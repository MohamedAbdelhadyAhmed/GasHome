<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Driver\Home\HomeController;
use App\Http\Controllers\API\Driver\Auth\LoginController;
use App\Http\Controllers\API\Driver\Auth\PasswordController;
use App\Http\Controllers\API\Dashboard\Region\RegionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::prefix('driver')->group(function () {

    // Route::post('driver/login', [LoginController::class, 'login']);
    Route::get('/logout', [LoginController::class, 'logout']);
    Route::post('/send-code', [PasswordController::class, 'SendCode']);
    Route::post('/verify-code', [PasswordController::class, 'verify']);
    Route::post('/reset-password', [PasswordController::class, 'ResetPasswordChangePassword']);
    //======================= Driver  Auth  ======================
    Route::get('/regions', [RegionController::class, 'allRegions']);




    // in home page get all orders depending on order regoin
    Route::get('/orders/{region_id}', [HomeController::class, 'getOrdersByRegion']);
    Route::get('/driver-orders', [HomeController::class, 'getOrdersByDriver']);
});
