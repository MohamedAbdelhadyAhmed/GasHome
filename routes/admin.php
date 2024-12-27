<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Dashboard\Driver\DriverController;
use App\Http\Controllers\API\Dashboard\Product\ProductsController;
use App\Http\Controllers\API\Dashboard\Category\CategoryController;
use App\Http\Controllers\API\Dashboard\Customers\CustomerController;
use App\Http\Controllers\API\Dashboard\Customers\DashboardController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Route::apiResource('/product', ProductsController::class);


// Route::get(uri: '/logout', [LoginController::class, 'logout']);

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
Route::get('/edit-profile', [DashboardController::class, 'editProfile']);
Route::post('/dashboard-settings/update', [DashboardController::class, 'update']);
// use Illuminate\Support\Facades\Route;
use App\Models\AvailableAddress;

Route::get('/test-coordinates', function () {
    $areas = AvailableAddress::all();
    foreach ($areas as $area) {
        echo "Area ID: " . $area->id . "<br>";
        $polygon = json_decode($area->coordinates, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "Invalid JSON in area ID: " . $area->id . "<br>";
        } else {
            echo "Coordinates:<br>";
            echo "<pre>" . print_r($polygon, true) . "</pre>";
        }
    }
});
