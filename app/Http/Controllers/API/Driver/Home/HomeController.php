<?php

namespace App\Http\Controllers\API\Driver\Home;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    //
    // get all orders of city
    // public function getOrders(Request $request)
    // {
    //     $orders = Order::where('city_id', $request->city_id)->get();
    //     return response()->json(['orders' => $orders], 200);
    // }
    public function getOrdersByRegion($region_id)
    {
        $orders = Order::where('region_id', $region_id)->get();
        if ($orders->isEmpty()) {
            return response()->json([
                'message' => 'No orders found',
                'data' => []
            ]);
        }
        return response()->json(
            [
                'message' => 'All orders',
                'data' => $orders
            ]
        );
    }
    //================================================= getOrdersByDriver ======================================
    public function getOrdersByDriver()
    {

        $driver_id = Auth::guard('sanctum')->user()->id;
        if (!$driver_id) {
            return response()->json([
                'message' => 'Driver not found',
                'data' => [],
            ]);
        }
        $orders = Order::where('driver_id', $driver_id)->get();
        if ($orders->isEmpty()) {
            return response()->json([
                'message' => 'No orders found',
                'data' => []
            ]);
        }
        return response()->json(
            [
               'message' => 'All orders',
                'data' => $orders
            ]
        );
    }
    //================================================  =====================================
}
