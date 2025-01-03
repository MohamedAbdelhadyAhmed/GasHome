<?php

use Illuminate\Support\Facades\Route;
use App\Services\MyFatoorahPaymentService;
use App\Http\Controllers\MyFatoorahController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/myfatoorah/index', [MyFatoorahController::class, 'index'])->name('myfatoorah.index');
Route::get('/myfatoorah/callback', [MyFatoorahController::class, 'callback'])->name('myfatoorah.callback');
Route::get('/myfatoorah/checkout', [MyFatoorahController::class, 'checkout'])->name('myfatoorah.checkout');
Route::post('/myfatoorah/webhook', [MyFatoorahController::class, 'webhook'])->name('myfatoorah.webhook');

//------------------------

Route::get('/payment-success', function () {
    return view('payment.success', [
        'message' => request('message'),
        'data' => request('data'),
    ]);
})->name('payment.success');

Route::get('/payment-failed', function () {
    return view('payment.failed', [
        'message' => request('message'),
    ]);
})->name('payment.failed');
