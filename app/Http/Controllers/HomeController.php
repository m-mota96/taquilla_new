<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Traits\DateFormatEs;
use App\Models\Event;
use App\Models\User;
use App\Models\ChangeCenter\Sale;
use Auth;

class HomeController extends Controller {
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index() {
        $session = Auth::user();
        $user = User::where('id', $session->id)->first();
        $role = $user->getRoleNames()[0];
        switch ($role) {
            case 'customer':
                    $events = Event::where('user_id', $session->id)->get();
                    foreach ($events as $key => $e) {
                        $e->initial_date_es = substr($e->initial_date, 8, 2).' de '.DateFormatEs::nameMonth($e->initial_date, true).'. de '.substr($e->initial_date, 0, 4);
                        $e->final_date_es = substr($e->final_date, 8, 2).' de '.DateFormatEs::nameMonth($e->final_date, true).'. de '.substr($e->final_date, 0, 4);
                    }
                    return view('customers.events')->with(['events' => $events, 'user' => $user]);
                break;
            case 'cashier':
                    $events = Event::whereHas('users', function($query) use($session) {
                        $query->where('event_user.user_id', $session->id);
                    })->get();
                    foreach ($events as $key => $e) {
                        $e->initial_date_es = substr($e->initial_date, 8, 2).' de '.DateFormatEs::nameMonth($e->initial_date, true).'. de '.substr($e->initial_date, 0, 4);
                        $e->final_date_es = substr($e->final_date, 8, 2).' de '.DateFormatEs::nameMonth($e->final_date, true).'. de '.substr($e->final_date, 0, 4);
                    }
                    return view('cashiers.events')->with(['events' => $events, 'user' => $user]);
                break;
            case 'admin_change_center':
                    $response = Http::get('https://crm.ticketland.mx/api/get-expositores-crm/1');
                    $data = $response->json()['data'];
                    foreach ($data as $key => $d) {
                        $sales = Sale::where('provider_id', $d['id'])->selectRaw('SUM(amount) as sales')->first();
                        $data[$key]['sales'] = !isset($sales->sales) ? 0 : $sales->sales;
                    }
                    // dd($data);
                    return view('changeCenter.providers')->with(['user' => $user, 'providers' => $data]);
                break;
        }
    }
}
