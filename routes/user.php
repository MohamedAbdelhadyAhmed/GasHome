<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MyFatoorahController;
use App\Http\Controllers\API\User\Cart\CartController;
use App\Http\Controllers\API\User\Auth\LoginController;
use App\Http\Controllers\API\User\Orders\OrderController;
use App\Http\Controllers\API\User\Auth\PasswordController;
use App\Http\Controllers\API\User\Auth\RegisterController;
use App\Http\Controllers\API\User\Orders\PaymentController;
use App\Http\Controllers\API\User\Address\AddressController;
use App\Http\Controllers\API\Dashboard\Product\ProductsController;

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

Route::prefix('user')->group(function () {
    Route::post('/register', [RegisterController::class, 'store']);
    Route::post('/verify-code', [RegisterController::class, 'verify']);
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/logout', [LoginController::class, 'logout']);
    Route::post('/send-code', [PasswordController::class, 'SendCode']);
    Route::post('/reset-password', [PasswordController::class, 'ResetPasswordChangePassword']);
    Route::post('/store-address', [AddressController::class, 'StoreAddress']);
    Route::get('/address', [AddressController::class, 'getAddresses']);
    //=================== get all products or categories with its products  =======================
    // Route::middleware(['auth:sanctum', 'type.user'])->group(function () {
    //  get all avilable products or categories with its products in home page  //  ProductsController same in user and admin
    Route::get('/categories', [ProductsController::class, 'allCategories']);
    Route::get('/products/{category_id}', [ProductsController::class, 'index']);
    Route::get('/products', [ProductsController::class, 'allProducts']);
    // Route::get('/products/{id}', [ProductsController::class,'show']);
    //======================================== Cart  ====================================
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/add-to-cart', [CartController::class, 'addToCart']);
    Route::post('/remove-from-cart', [CartController::class, 'removeItemFromCart']);
    Route::post('/increece-quntaty-for-product', [CartController::class, 'increeceQuntaty']);
    Route::post('/decrease-quntaty-for-product', [CartController::class, 'decreaseQuntaty']);

    //============================================ create order & Orders  ============================================

    Route::get('/create-order', [OrderController::class, 'createOrder']);
    Route::post('/complete-order', [OrderController::class, 'completeOrder']);
    Route::get('/all-orders', [OrderController::class, 'getAllUserOrders']);
    Route::get('/order/{id}', [OrderController::class, 'getOrderById']);
    Route::get('/pay-online/{}', [MyFatoorahController::class, 'getPayLoadData']);
    //============================================ payment============================================
    Route::post('/payment/process', [PaymentController::class, 'paymentProcess']);
    //============================================ end payment============================================
});

// });
