<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChangeCenter\Customer;
use App\Models\ChangeCenter\Ticket;
use App\Models\ChangeCenter\Sale;

class ChangeCenterController extends Controller {
    public function change() {
        return view('changeCenter.change');
    }

    public function registerTickets(Request $request) {
        $customer = Customer::create([
            'event_id' => 1,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone
        ]);

        $total = 0;

        for ($i = 0; $i < sizeof($request->provider); $i++) { 
            $data[] = ['customer_id' => $customer->id, 'provider_id' => $request->provider[$i], 'amount' => $request->amount[$i]];
            $total = $total + $request->amount[$i];
        }

        $total = $total / 500;
        $total = intval($total);

        Sale::insert($data);
        $data = [];

        for ($i = 0; $i < $total; $i++) { 
            $data[] = ['customer_id' => $customer->id];
        }

        Ticket::insert($data);

        return response()->json([
            'error' => false,
            'msj' => ''
        ], 200);
    }

    public function clients($event_id) {
        $customers = Customer::where('event_id', $event_id)
        ->addSelect(['buy' => Sale::selectRaw('SUM(amount)')->whereColumn('customer_id', 'customers.id')])
        ->get();
        $winner = Ticket::where('winner', 1)->first();
        // dd($customers);
        return view('changeCenter.clients')->with(['customers' => $customers, 'winner' => $winner]);
    }

    public function selectWinner(Request $request) {
        $tickets = Ticket::whereHas('customer', function($query) {
            $query->where('event_id', 1);
        })->get();
        foreach ($tickets as $key => $t) {
            $data[] = $t->id;
            $t->winner = 0;
            $t->save();
        }
        
        $x = array_rand(array_flip($data), 1);
        $winner = Ticket::with(['customer'])->where('id', $x)->first();
        $winner->winner = 1;
        $winner->save();

        return response()->json(['winner' => $winner], 200);
    }
}
