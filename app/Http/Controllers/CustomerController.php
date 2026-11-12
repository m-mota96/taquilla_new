<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Traits\DateFormatEs;
use App\Models\Ticket;
use App\Models\Event;
use App\Models\Folio;
use App\Models\User;
use App\Models\Sale;
use App\Models\Box;
use App\Models\Cut;
use Auth;
use DB;

class CustomerController extends Controller {
    public function createEvent(Request $request) {
        $nameEvent = trim($request->nameEvent);
        $nameEvent = str_replace('"', '', $nameEvent);
        $nameEvent = str_replace("'", '', $nameEvent);
        $event = Event::create([
            'user_id' => Auth::user()->id,
            'name' => trim($nameEvent),
            'initial_date' => $request->initial_date,
            'final_date' => $request->final_date
        ]);

        $users = User::where('user_id', Auth::user()->id)->get();
        if (sizeof($users) > 0) {
            foreach ($users as $key => $u) {
                $event->users()->attach($u->id);
            }
        }

        return response()->json(['event' => $event], 202);
    }

    public function adminTickets($event_id) {
        $event = Event::with(['tickets:id,event_id,name,price'])->where('id', $event_id)->where('user_id', Auth::user()->id)->first();
        if (!empty($event)) {
            $session = Auth::user();
            $user = User::where('id', $session->id)->first();
            return view('customers.tickets')->with(['event' => $event, 'user' => $user]);
        } else {
            return redirect('home');
        }
    }

    public function createEditTicket(Request $request) {
        if (empty($request->ticket_id)) {
            $ticket = Ticket::create([
                'event_id' => $request->eventId,
                'name' => trim($request->name_ticket),
                'price' => trim($request->price),
            ]);
        } else {
            $ticket = Ticket::where('id', $request->ticket_id)->first();
            $ticket->name = trim($request->name_ticket);
            $ticket->price = trim($request->price);
            $ticket->save();
        }
        return response()->json([
            'error' => true,
            'msj' => '',
            'data' => $ticket
        ], 202);
    }

    public function adminCashiers() {
        $events = Event::where('user_id', Auth::user()->id)->where('status', 1)->get();
        $array_events = Array();
        foreach ($events as $key => $e) {
            $array_events[] = $e->id;
        }
        $session = Auth::user();
        $user = User::where('id', $session->id)->first();
        return view('customers.cashiers')->with(['events' => $events, 'user' => $user, 'array_events' => $array_events]);
    }

    public function users() {
        return datatables()->eloquent(User::with(['access_events:name,id'])->where('user_id', Auth::user()->id))->toJson();
    }

    public function createCashier(Request $request) {
        $user = User::where('user', trim($request->user))->first();
        if (!empty($user)) {
            return response()->json([
                'error' => true,
                'msj' => 'El usuario <b>'.$request->user.'</b> no esta disponible.<br>Intente con otro'
            ], 202);
        }

        $events = Event::where('user_id', Auth::user()->id)->get();

        $user = User::create([
            'user_id' => Auth::user()->id,
            'name' => trim($request->name),
            'user' => trim($request->user),
            'password' => Hash::make(trim($request->password))
        ]);
        $user->assignRole('cashier');

        if (sizeof($events) > 0) {
            foreach ($events as $key => $e) {
                $user->access_events()->attach($e->id);
            }
        }

        return response()->json([
            'error' => false,
            'msj' => ''
        ], 202);
    }

    public function enableDisableEventForSale(Request $request) {
        $user = User::with(['access_events'])->where('id', $request->user_id)->first();
        if ($request->ban == 0) {
            $user->access_events()->detach($request->event_id);
        } else if ($request->ban == 1) {
            $user->access_events()->attach($request->event_id);
        }
        return response()->json([
            'error' => false,
            'msj' => ''
        ], 202);
    }

    public function adminFolios($event_id) {
        $event = Event::with(['tickets:id,event_id,name,price', 'tickets.folios.user'])->where('id', $event_id)->where('user_id', Auth::user()->id)->first();
        if (!empty($event)) {
            $users = User::where('user_id', Auth::user()->id)->whereHas('access_events', function($query) use($event) {
                $query->where('event_id', $event->id);
            })->get();
            $session = Auth::user();
            $user = User::where('id', $session->id)->first();
            return view('customers.folios')->with(['event' => $event, 'user' => $user, 'users' => $users]);
        } else {
            return redirect('home');
        }
    }

    public function addEditFolios(Request $request) {
        foreach ($request->folios as $key => $f) {
            if (is_numeric($f['initial_folio']) && is_int(intval($f['initial_folio'])) && is_numeric($f['final_folio']) && is_int(intval($f['final_folio']))) {
                if ($f['initial_folio'] <= $f['final_folio']) {
                    if (empty($f['folio_id'])) {
                        Folio::create([
                            'ticket_id' => $f['ticket_id'],
                            'user_id' => $f['cashier'],
                            'initial_folio' => trim($f['initial_folio']),
                            'final_folio' => trim($f['final_folio']),
                            'quantity' => (intval($f['final_folio']) - intval($f['initial_folio'])) + 1
                        ]);
                    } else {
                        $folio = Folio::where('id', $f['folio_id'])->where('sold', 0)->first();
                        if (!empty($folio)) {
                            if (empty($folio->user_id)) {
                                $folio->user_id = $f['cashier'];
                            }
                            $folio->initial_folio = trim($f['initial_folio']);
                            $folio->final_folio = trim($f['final_folio']);
                            $folio->quantity = (intval($f['final_folio']) - intval($f['initial_folio'])) + 1;
                            $folio->save();
                        }
                    }
                } else {
                    return response()->json([
                        'error' => true,
                        'msj' => 'Los folios finales deben ser mayor o igual que los iniciales'
                    ], 202);
                }
            } else {
                return response()->json([
                    'error' => true,
                    'msj' => 'Solo se aceptan números en los folios inicial y final'
                ], 202); 
            }
        }
        $folios = Folio::with(['user'])->where('ticket_id', $request->ticket_id)->get();
        return response()->json([
            'error' => false,
            'msj' => '',
            'data' => $folios
        ], 202);
    }

    public function deleteFolios(Request $request) {
        $folio = Folio::where('id', $request->folio_id)->first();
        if ($folio->sold == 0) {
            $folio->delete();
            return response()->json([
                'error' => false,
                'msj' => ''
            ], 202);
        } else {
            return response()->json([
                'error' => true,
                'msj' => 'No es posible eliminar el registro<br>Ya hay ventas realizadas con estos folios'
            ], 202);
        }
    }

    public function stats($event_id) {
        $event = Event::with(['tickets:id,event_id,name,price', 'tickets.folios.user'])->where('id', $event_id)->where('user_id', Auth::user()->id)->first();
        if (!empty($event)) {
            $begin = new \DateTime($event->initial_date);
            $end = new \DateTime($event->initial_date);
            $end->modify('+1 day');

            for($i = $begin; $i <= $end; $i->modify('+1 day')) {
                $dates[] = [
                    'date' => $i->format("Y-m-d"),
                    'date_parse' => substr($i->format("Y-m-d"), 8, 2).' de '.DateFormatEs::nameMonth($i->format("Y-m-d")).' de '.substr($i->format("Y-m-d"), 0, 4),
                ];
            }
            $session = Auth::user();
            $user = User::where('id', $session->id)->first();
            return view('customers.stats')->with(['event' => $event, 'user' => $user, 'dates' => $dates]);
        } else {
            return redirect('home');
        }
    }

    public function extractStats(Request $request) {
        // dd($request->all());
        switch ($request->date) {
            case 'all':
                $sales = Sale::with(['ticket:id,name,price'])->whereHas('ticket', function($query) use($request) {
                    $query->where('event_id', $request->event_id);
                })
                ->select('ticket_id', DB::raw('SUM(sold) as sold'))->groupBy('ticket_id')->get();
                $tickets = Ticket::where('event_id', $request->event_id)
                ->select('id', 'name', 'price')
                ->addSelect(['sales_card' => Sale::selectRaw('IF(SUM(sold) IS NULL, 0, SUM(sold))')->where('payment_method', 'card')->whereColumn('ticket_id', 'tickets.id')])
                ->addSelect(['sales_cash' => Sale::selectRaw('IF(SUM(sold) IS NULL, 0, SUM(sold))')->where('payment_method', 'cash')->whereColumn('ticket_id', 'tickets.id')])
                ->get();
            break;
            default:
                $sales = Sale::with(['ticket:id,name,price'])->whereHas('ticket', function($query) use($request) {
                    $query->where('event_id', $request->event_id);
                })
                ->where('date', $request->date)
                ->select('ticket_id', DB::raw('SUM(sold) as sold'))->groupBy('ticket_id')->get();
                $tickets = Ticket::where('event_id', $request->event_id)
                ->select('id', 'name', 'price')
                ->addSelect([
                    'sales_card' => 
                    Sale::selectRaw('IF(SUM(sold) IS NULL, 0, SUM(sold))')->where('payment_method', 'card')->where('date', $request->date)->whereColumn('ticket_id', 'tickets.id')
                ])
                ->addSelect([
                    'sales_cash' => 
                    Sale::selectRaw('IF(SUM(sold) IS NULL, 0, SUM(sold))')->where('payment_method', 'cash')->where('date', $request->date)->whereColumn('ticket_id', 'tickets.id')
                ])
                ->get();
                break;
        }
        return response()->json([
            'sales' => $sales,
            'tickets' => $tickets
        ], 202);
    }

    public function boxes($event_id) {
        $event = Event::with(['users.box' => function($query) use($event_id) {
            $query->where('event_id', $event_id);
        }])->where('id', $event_id)->where('user_id', Auth::user()->id)->first();
        if (!empty($event)) {
            $session = Auth::user();
            $user = User::where('id', $session->id)->first();
            return view('customers.boxes')->with(['event' => $event, 'user' => $user]);
        } else {
            return redirect('home');
        }
    }

    public function saveBoxes(Request $request) {
        for ($i = 0; $i < sizeof($request->user_id); $i++) { 
            if (!empty($request->box[$i]) && $request->box[$i] != 0) {
                $box = Box::where('event_id', $request->event_id)->where('user_id', $request->user_id[$i])->first();
                if (empty($box)) {
                    Box::create([
                        'event_id' => $request->event_id,
                        'user_id' => $request->user_id[$i],
                        'cash' => $request->box[$i],
                        'type' => 'box'
                    ]);
                } else {
                    $box->cash = $request->box[$i];
                    $box->save();
                }
            }
        }
        return response()->json([
            'error' => false,
        ], 202);
    }

    public function cuts($event_id) {
        $event = Event::with(['users.box' => function($query) use($event_id) {
            $query->where('event_id', $event_id);
        }])->where('id', $event_id)->where('user_id', Auth::user()->id)->first();
        if (!empty($event)) {
            $begin = new \DateTime($event->initial_date);
            $end = new \DateTime($event->initial_date);
            $end->modify('+1 day');

            for($i = $begin; $i <= $end; $i->modify('+1 day')) {
                $dates[] = [
                    'date' => $i->format("Y-m-d"),
                    'date_parse' => substr($i->format("Y-m-d"), 8, 2).' de '.DateFormatEs::nameMonth($i->format("Y-m-d")).' de '.substr($i->format("Y-m-d"), 0, 4),
                ];
            }
            $session = Auth::user();
            $user = User::where('id', $session->id)->first();
            return view('customers.cuts')->with(['event' => $event, 'user' => $user, 'dates' => $dates]);
        } else {
            return redirect('home');
        }
    }

    public function extractCuts(Request $request) {
        return datatables()->eloquent(
            Cut::with(['user.box'])
            ->where('event_id', $request->event_id)
            ->select('user_id', 'systemCut', 'realCut', 'date', DB::raw('SUBSTRING(hour, 1, 5) as hour'), 'comment')
            // ->addSelect(['name_user' => User::select('name')
            // ->whereColumn('cuts.user_id', 'users.id')])
            ->orderBy('hour', 'ASC')
            ->orderBy('date', 'ASC')
        )->toJson();
    }
}
