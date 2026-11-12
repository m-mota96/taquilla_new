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
use Auth;
use DB;

class CashierController extends Controller {
    public function sale($event_id) {
        $event = Event::where('id', $event_id)->whereHas('users', function($query) {
            $query->where('event_user.user_id', Auth::user()->id);
        })->first();
        if (!empty($event)) {
            $folios = Folio::with(['ticket'])->addSelect(['ticket_name' => Ticket::select('name')
            ->whereColumn('ticket_id', 'tickets.id')
            ])->where('user_id', Auth::user()->id)->where('status', 1)->orderBy('ticket_name')->get();
            $session = Auth::user();
            $user = User::with(['box', 'cash'])->where('id', $session->id)->first();
            return view('cashiers.sale')->with(['event' => $event, 'user' => $user, 'folios' => $folios]);
        } else {
            return redirect('home');
        }
    }

    public function registerSale(Request $request) {
        // dd($request->all());

        DB::beginTransaction();
        try {
            $customer = Customer::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
            ]);
            $customer_id = $customer->id;

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
        return response()->json([
            'error' => false,
            'msj' => '',
            'data' => $folios,
            'cashBox' => $box
        ]);
    }

    public function viewSales($event_id) {
        $event = Event::where('id', $event_id)->whereHas('users', function($query) {
            $query->where('event_user.user_id', Auth::user()->id);
        })->first();
        if (!empty($event)) {
            $session = Auth::user();
            $user = User::where('id', $session->id)->first();
            return view('cashiers.viewSales')->with(['event' => $event, 'user' => $user]);
        } else {
            return redirect('home');
        }
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
