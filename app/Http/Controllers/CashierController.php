<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Ticket;
use App\Models\Event;
use App\Models\Folio;
use App\Models\User;
use App\Models\Sale;
use App\Models\Box;
use App\Models\Cut;
use App\Models\Order;
use App\Models\OrderAttendee;
use App\Models\Position;
use App\Services\ConferenceService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Auth;
use DB;

class CashierController extends Controller {
    public function sale($event_id) {
        $event = Event::with('setting')->where('id', $event_id)->whereHas('users', function($query) {
            $query->where('event_user.user_id', Auth::user()->id);
        })->first();
        if (!empty($event)) {
            $folios = Folio::with(['ticket'])->addSelect(['ticket_name' => Ticket::select('name')
            ->whereColumn('ticket_id', 'tickets.id')
            ])->where('user_id', Auth::user()->id)->where('status', 1)->orderBy('ticket_name')->get();
            $session = Auth::user();
            $user = User::with(['box', 'cash'])->where('id', $session->id)->first();

            $perPerson = $event->isPerPersonMode();
            $positions = $perPerson
                ? Position::where('event_type', $event->setting->event_type)->orderBy('id')->get(['id', 'name'])
                : collect();

            return view('cashiers.sale')->with([
                'event' => $event, 'user' => $user, 'folios' => $folios,
                'perPerson' => $perPerson, 'positions' => $positions,
            ]);
        } else {
            return redirect('home');
        }
    }

    public function registerSale(Request $request, ConferenceService $conferences) {
        $event = Event::with('setting')->where('id', $request->event_id)->whereHas('users', function($query) {
            $query->where('event_user.user_id', Auth::user()->id);
        })->first();
        if (empty($event)) {
            return response()->json([
                'error' => true,
                'msj' => 'No tiene acceso a este evento'
            ], 202);
        }

        if (!in_array($request->payment_method, ['cash', 'card'], true)) {
            return response()->json([
                'error' => true,
                'msj' => 'Elija un método de pago'
            ], 202);
        }

        $perPerson = $event->isPerPersonMode();
        if ($perPerson) {
            $error = $this->validateAttendees($request, $event);
            if ($error) {
                return response()->json(['error' => true, 'msj' => $error], 202);
            }
        }

        DB::beginTransaction();
        try {
            // Modo 1: un solo cliente por compra (comportamiento original)
            $customer_id = null;
            if (!$perPerson) {
                $customer = Customer::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                ]);
                $customer_id = $customer->id;
            }

            $ids_folios = [];
            $totalSale = 0;
            foreach ($request->folios as $key => $f) {
                $ids_folios[] = $f['id'];
                if ($f['quantity'] > 0) {
                    $folio = Folio::with(['ticket'])->where('id', $f['id'])->first();
                    $totalSale = $totalSale + ($f['quantity'] * $folio->ticket->price);
                    if (($folio->sold + intval($f['quantity'])) > $folio->quantity) {
                        DB::rollback();
                        return response()->json([
                            'error' => true,
                            'msj' => 'Excedió la cantidad disponible de folios de <br><b>'.$folio->ticket->name.' Folios: '.$folio->initial_folio.'-'.$folio->final_folio.'</b>'
                        ], 202);
                    }
                    $folio->sold = $folio->sold + intval($f['quantity']);
                    if ($folio->sold == $folio->quantity) {
                        $folio->status = 0;
                    }
                    $folio->save();
                    $sale = Sale::where('ticket_id', $folio->ticket->id)->where('date', date('Y-m-d'))->where('payment_method', $request->payment_method)->first();
                    if (empty($sale)) {
                        Sale::create([
                            'ticket_id' => $folio->ticket->id,
                            'sold' => $f['quantity'],
                            'payment_method' => $request->payment_method,
                            'date' => date('Y-m-d')
                        ]);
                    } else {
                        $sale->sold = $sale->sold + intval($f['quantity']);
                        $sale->save();
                    }
                    if (!$perPerson) {
                        $customer = Customer::with(['tickets' => function($query) use($folio) {
                            $query->where('ticket_id', $folio->ticket->id);
                        }])->where('id', $customer_id)->first();
                        if (sizeof($customer->tickets) == 0) {
                            $customer->tickets()->attach($folio->ticket->id, ['quantity' => $f['quantity']]);
                        } else {
                            $quantity = $customer->tickets[0]->pivot->quantity;
                            $customer->tickets()->detach($folio->ticket->id);
                            $customer->tickets()->attach($folio->ticket->id, ['quantity' => $quantity + intval($f['quantity'])]);
                        }
                    }
                }
            }

            // Efectivo: "Pago con" es obligatorio y debe cubrir el total calculado en el servidor.
            // Tarjeta: "Pago con" es opcional y no se valida.
            $pay = trim((string) $request->pay);
            if ($request->payment_method === 'cash' && (!ctype_digit($pay) || intval($pay) < $totalSale)) {
                DB::rollback();
                return response()->json([
                    'error' => true,
                    'msj' => $pay === ''
                        ? 'Ingrese con cuánto paga el cliente en <b>Pago con</b>'
                        : 'El pago no puede ser menor al total de <b>$'.number_format($totalSale).'.00</b>'
                ], 202);
            }

            // Orden padre (ambos modos)
            $order = Order::create([
                'event_id' => $event->id,
                'user_id' => Auth::user()->id,
                'customer_id' => $customer_id,
                'payment_method' => $request->payment_method,
                'total' => $totalSale,
            ]);

            // Modo 2: un registro por persona, cada uno con su QR
            if ($perPerson) {
                foreach ($request->attendees as $a) {
                    $order->attendees()->create([
                        'ticket_id' => $a['ticket_id'],
                        'position_id' => $a['position_id'],
                        'name' => trim($a['name']),
                        'email' => trim($a['email']),
                        'country_code' => trim($a['country_code']),
                        'phone' => preg_replace('/\D/', '', $a['phone']),
                        'qr' => (string) Str::uuid(),
                    ]);
                }
            }
            if ($totalSale > 0) {
                $box = Box::where('event_id', $request->event_id)->where('user_id', Auth::user()->id)->where('type', $request->payment_method)->first();
                if (empty($box)) {
                    Box::create([
                        'event_id' => $request->event_id,
                        'user_id' => Auth::user()->id,
                        'cash' => $totalSale,
                        'type' => $request->payment_method
                    ]);
                } else {
                    $box->cash = $box->cash + $totalSale;
                    $box->save();
                }
            }
            $folios = Folio::whereIn('id', $ids_folios)->where('user_id', Auth::user()->id)->select('id', 'quantity', 'sold', 'status')->get();
            $box = [
                'cash' => Box::where('event_id', $request->event_id)->where('user_id', Auth::user()->id)->where('type', 'cash')->first(),
                'box' => Box::where('event_id', $request->event_id)->where('user_id', Auth::user()->id)->where('type', 'box')->first()
            ];
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'error' => true,
                'msj' => 'Error: '.$e->getMessage()
            ], 202);
        }

        // El envio a conferencias va despues del commit: si falla, la venta ya quedo
        // registrada y los pendientes se reenvian desde Configuraciones.
        $conferenceFailed = 0;
        $badges = [];
        $badgesMissing = [];
        if ($perPerson) {
            $attendees = $order->attendees()->with(['ticket', 'position'])->orderBy('id')->get();
            $result = $conferences->sendMany($attendees);
            $conferenceFailed = $result['failed'];

            // Gafetes a imprimir: solo los que ya tienen id en conferencias (va en el QR)
            foreach ($attendees as $a) {
                $badge = $a->badgeData();
                if ($badge) {
                    $badges[] = $badge;
                } else {
                    $badgesMissing[] = $a->name;
                }
            }
        }

        return response()->json([
            'error' => false,
            'msj' => '',
            'data' => $folios,
            'cashBox' => $box,
            'conferenceFailed' => $conferenceFailed,
            'badges' => $badges,
            'badgesMissing' => $badgesMissing
        ]);
    }

    /**
     * Valida los formularios del modo "un registro por persona".
     * Devuelve el mensaje del primer error o null si todo es correcto.
     */
    private function validateAttendees(Request $request, Event $event) {
        $eventType = $event->setting->event_type;
        if (empty($eventType)) {
            return 'El evento no tiene configurado el tipo de evento. Avise al organizador.';
        }

        $validator = Validator::make($request->all(), [
            'attendees' => ['required', 'array'],
            'attendees.*.ticket_id' => ['required', 'integer'],
            'attendees.*.name' => ['required', 'string', 'max:255'],
            'attendees.*.email' => ['required', 'email:rfc', 'regex:/^[^\s@]+@[^\s@]+\.[a-zA-Z]{2,}$/', 'max:255'],
            'attendees.*.country_code' => ['required', 'regex:/^\+\d{1,4}$/'],
            'attendees.*.phone' => ['required', 'regex:/^\d{3} ?\d{3} ?\d{4}$/'],
            'attendees.*.position_id' => ['required', Rule::exists('positions', 'id')->where('event_type', $eventType)],
        ], [
            'attendees.required' => 'Capture los datos de cada persona',
            'attendees.*.name.required' => 'El nombre es obligatorio en todos los registros',
            'attendees.*.email.required' => 'El correo es obligatorio en todos los registros',
            'attendees.*.email.email' => 'Hay un correo con formato inválido',
            'attendees.*.email.regex' => 'Hay un correo con formato inválido',
            'attendees.*.country_code.required' => 'El código de país es obligatorio en todos los registros',
            'attendees.*.country_code.regex' => 'Hay un código de país inválido (ej. +52)',
            'attendees.*.phone.required' => 'El teléfono es obligatorio en todos los registros',
            'attendees.*.phone.regex' => 'El teléfono debe tener exactamente 10 dígitos',
            'attendees.*.position_id.required' => 'El cargo es obligatorio en todos los registros',
            'attendees.*.position_id.exists' => 'Hay un cargo que no es válido para este evento',
            'attendees.*.*.max' => 'Hay un campo con demasiados caracteres',
        ]);
        if ($validator->fails()) {
            return $validator->errors()->first();
        }

        // Debe haber exactamente un registro por cada boleto vendido
        $expected = [];
        foreach ((array) $request->folios as $f) {
            if (intval($f['quantity'] ?? 0) > 0) {
                $folio = Folio::where('id', $f['id'])->first();
                if (empty($folio)) {
                    return 'Uno de los folios ya no existe. Recargue la página.';
                }
                $expected[$folio->ticket_id] = ($expected[$folio->ticket_id] ?? 0) + intval($f['quantity']);
            }
        }
        $received = [];
        foreach ($request->attendees as $a) {
            $received[$a['ticket_id']] = ($received[$a['ticket_id']] ?? 0) + 1;
        }
        ksort($expected);
        ksort($received);
        if ($expected != $received) {
            return 'El número de registros no coincide con los boletos seleccionados. Recargue la página e intente de nuevo.';
        }

        return null;
    }

    public function viewSales($event_id) {
        $event = Event::where('id', $event_id)->whereHas('users', function($query) {
            $query->where('event_user.user_id', Auth::user()->id);
        })->first();
        if (!empty($event)) {
            $session = Auth::user();
            $user = User::where('id', $session->id)->first();

            // Un evento pudo cambiar de modo: se muestra cada tabla si es el modo actual o si ya tiene ventas
            $perPerson = $event->isPerPersonMode();
            $showAttendees = $perPerson || OrderAttendee::whereHas('order', function($query) use($event) {
                $query->where('event_id', $event->id);
            })->exists();
            $showCustomers = !$perPerson || Customer::whereHas('tickets.event', function($query) use($event) {
                $query->where('id', $event->id);
            })->exists();

            return view('cashiers.viewSales')->with([
                'event' => $event, 'user' => $user,
                'showAttendees' => $showAttendees, 'showCustomers' => $showCustomers,
            ]);
        } else {
            return redirect('home');
        }
    }

    public function extractAttendees(Request $request) {
        $event = Event::where('id', $request->event_id)->whereHas('users', function($query) {
            $query->where('event_user.user_id', Auth::user()->id);
        })->first();
        if (empty($event)) {
            abort(403);
        }

        return datatables()->eloquent(
            OrderAttendee::with(['ticket:id,name', 'position:id,name'])
                ->select('order_attendees.*')
                ->whereHas('order', function($query) use($event) {
                    $query->where('event_id', $event->id);
                })
        )->editColumn('created_at', function (OrderAttendee $a) {
            return $a->created_at->format('Y-m-d H:i:s');
        })->editColumn('conference_sent_at', function (OrderAttendee $a) {
            return $a->conference_sent_at ? $a->conference_sent_at->format('Y-m-d H:i:s') : null;
        })->toJson();
    }

    public function extractSales(Request $request) {
        // $data = Customer::with(['tickets'])->whereHas('tickets.event', function($query) use($request) {
        //     $query->where('id', $request->event_id);
        // });
        // return datatables()->collection($data)->toJson();
        return datatables()->eloquent(
            Customer::with(['tickets'])->whereHas('tickets.event', function($query) use($request) {
                $query->where('id', $request->event_id);
            })
        )->editColumn('created_at', function (Customer $query) {
            return $query->created_at;
        })->toJson();
    }

    public function registerCut(Request $request) {
        $question  = intval($request->question);
        $actualCash = trim($request->actualCash);
        $box       = 0;
        $cash      = 0;

        $boxInfo = Box::where('event_id', $request->event_id)->where('user_id', Auth::user()->id)->where('type', 'box')->select('cash')->first();
        if(!empty($boxInfo)) {
            $box = $boxInfo->cash;
        }
        
        $cashInfo = Box::where('event_id', $request->event_id)->where('user_id', Auth::user()->id)->where('type', 'cash')->first();
        if(!empty($cashInfo)) {
            $cash = $cashInfo->cash;
            $cashInfo->cash = 0;
            $cashInfo->save();
        }

        if ($question == 0) {
            if ($actualCash <= $box) {
                return response()->json([
                    'error' => true,
                    'msj' => 'El total en efectivo debe ser mayor que el valor de caja chica'
                ], 202);
            }
        }
        $cut = ($question == 1) ? $cash : $actualCash - $box;

        if ($cut > 0) {
            Cut::create([
                'event_id' => $request->event_id,
                'user_id' => Auth::user()->id,
                'systemCut' => $cash,
                'realCut' => $cut,
                'comment' => trim($request->message),
                'date' => date('Y-m-d'),
                'hour' => date('H:i:s')
            ]);
        } else {
            return response()->json([
                'error' => true,
                'msj' => 'No se puedo realizar el corte de caja,<br>por favor verifique que la información sea correcta'
            ], 202);
        }
        $box = [
            'cash' => Box::where('event_id', $request->event_id)->where('user_id', Auth::user()->id)->where('type', 'cash')->first(),
            'box' => Box::where('event_id', $request->event_id)->where('user_id', Auth::user()->id)->where('type', 'box')->first()
        ];

        return response()->json([
            'error' => false,
            'msj' => 'El corte de caja se realizo correctamente',
            'cashBox' => $box
        ], 202);
    }
}
