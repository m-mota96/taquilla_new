<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    // return view('welcome');
    return view('auth.login');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/administrar_cajeros', [App\Http\Controllers\CustomerController::class, 'adminCashiers'])->name('administrar_cajeros');
    Route::get('/administrar_boletos/{event_id}', [App\Http\Controllers\CustomerController::class, 'adminTickets'])->name('administrar_boletos');
    Route::get('/administrar_folios/{event_id}', [App\Http\Controllers\CustomerController::class, 'adminFolios'])->name('administrar_folios');
    Route::get('/estadisticas/{event_id}', [App\Http\Controllers\CustomerController::class, 'stats'])->name('estadisticas');
    Route::get('/caja_chica/{event_id}', [App\Http\Controllers\CustomerController::class, 'boxes'])->name('caja_chica');
    Route::get('/cortes_de_caja/{event_id}', [App\Http\Controllers\CustomerController::class, 'cuts'])->name('cortes_de_caja');
});

Route::middleware(['auth', 'role:cashier'])->group(function () {
    Route::get('/venta/{event_id}', [App\Http\Controllers\CashierController::class, 'sale'])->name('venta');
    Route::get('/ver_ventas/{event_id}', [App\Http\Controllers\CashierController::class, 'viewSales'])->name('ver_ventas');
});

Route::middleware(['auth', 'role:admin_change_center'])->group(function () {
    Route::get('/clientes/{event_id}', [App\Http\Controllers\ChangeCenterController::class, 'clients'])->name('clientes');
});

Route::post('/createEvent', [App\Http\Controllers\CustomerController::class, 'createEvent']);
Route::get('/users', [App\Http\Controllers\CustomerController::class, 'users']);
Route::post('/createCashier', [App\Http\Controllers\CustomerController::class, 'createCashier']);
Route::post('/enableDisableEventForSale', [App\Http\Controllers\CustomerController::class, 'enableDisableEventForSale']);
Route::post('/createEditTicket', [App\Http\Controllers\CustomerController::class, 'createEditTicket']);
Route::post('/addEditFolios', [App\Http\Controllers\CustomerController::class, 'addEditFolios']);
Route::post('/deleteFolios', [App\Http\Controllers\CustomerController::class, 'deleteFolios']);
Route::post('/registerSale', [App\Http\Controllers\CashierController::class, 'registerSale']);
Route::post('/viewSales', [App\Http\Controllers\CashierController::class, 'extractSales']);
Route::post('/stats', [App\Http\Controllers\CustomerController::class, 'extractStats']);
Route::post('/saveBoxes', [App\Http\Controllers\CustomerController::class, 'saveBoxes']);
Route::post('/registerCut', [App\Http\Controllers\CashierController::class, 'registerCut']);
Route::post('/cuts', [App\Http\Controllers\CustomerController::class, 'extractCuts']);

// Change center routes
Route::get('/change', [App\Http\Controllers\ChangeCenterController::class, 'change'])->name('change');
Route::post('registerTickets', [App\Http\Controllers\ChangeCenterController::class, 'registerTickets']);
Route::post('selectWinner', [App\Http\Controllers\ChangeCenterController::class, 'selectWinner']);