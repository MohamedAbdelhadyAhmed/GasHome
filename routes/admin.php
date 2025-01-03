<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Dashboard\Orders\OrderController;
use App\Http\Controllers\API\Dashboard\Category\HomeController;
use App\Http\Controllers\API\Dashboard\Driver\DriverController;
use App\Http\Controllers\API\Dashboard\Region\RegionController;
use App\Http\Controllers\API\Dashboard\Product\ProductsController;
use App\Http\Controllers\API\Dashboard\Category\CategoryController;
use App\Http\Controllers\API\Dashboard\Customers\CustomerController;
use App\Http\Controllers\API\Dashboard\Customers\DashboardController;

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

Route::get('/home', [HomeController::class, 'index']);
//============================= categories =============================
Route::get('/categories', [CategoryController::class, 'index']);
Route::post('/category/create', [CategoryController::class, 'store']);
Route::post('/category/edit/{id}', [CategoryController::class, 'edit']);
Route::get('/category/delete/{id}', action: [CategoryController::class, 'delete']);

//============================= products =============================
Route::get('/products/{id}', [ProductsController::class, 'index']);
Route::get('/all-products', [ProductsController::class, 'allProducts']);
Route::get('/product/{id}', [ProductsController::class, 'show']);
Route::post('/product/create', [ProductsController::class, 'store']);
Route::post('/product/update/{id}', [ProductsController::class, 'update']);
Route::post('/product/update-quantity/{id}', [ProductsController::class, 'updateQuantity']);
Route::get('/product/delete/{id}', [ProductsController::class, 'destroy']);

//============================= Driver =============================
Route::get('/all-drivers', [DriverController::class, 'getDrivers']);
Route::get('/driver/{driver}', [DriverController::class, 'getDriverById']);
Route::post('/add-driver', [DriverController::class, 'addDriver']);
Route::get('/driver/delete/{id}', [DriverController::class, 'deleteDriver']);

//============================= Customer =============================
Route::get('/all-customers', [CustomerController::class, 'allCustomers']);
Route::get('/customer/{id}', [CustomerController::class, 'singleCustomer']);
Route::post('/add/region', [RegionController::class, 'addRegion']);
Route::get('/regions', [RegionController::class, 'allRegions']);

//============================= Dashboard Settings =============================
Route::get('/add-user', [DashboardController::class, 'addUser']);
Route::get('/edit-profile', [DashboardController::class, 'editProfile']);
Route::post('/dashboard-settings/update', [DashboardController::class, 'update']);
//============================= Orders  =============================
Route::patch('/orders/{order_id}/assign-driver', [OrderController::class, 'assignDriver']);
Route::get('/orders', [OrderController::class, 'index']);
Route::get('/orders/{id}', [OrderController::class, 'show']);
Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);
Route::delete('/orders/{id}', [OrderController::class, 'destroy']);
