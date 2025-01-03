<?php

namespace App\Http\Controllers\API\Driver\Order;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\AvailableAddress;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function allDriverOrders()
    {
        $driver = Auth::guard('sanctum')->user();
        if (!$driver) {
            return response()->json([
                'message' => 'Driver not found',
                'data' => [],
            ]);
        }

        $orders = Order::where('driver_id', $driver->id)
            ->where('order_status', 'shipped')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'message' => 'No orders found',
                'data' => []
            ]);
        }
        // $driverData = [
        //     'id' => $driver->id,
        //     'name' => $driver->name,
        //     'email' => $driver->email,
        //     'phone' => $driver->phone,
        // ];

        return response()->json([
            'message' => 'All orders for driver',
            // 'driver' => $driverData,
            'data' => $orders,
        ]);
    }


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

    public function cancelOrder(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $updated = Order::where('id', $data['order_id'])->update(['driver_id' => null]);

        if ($updated) {
            return response()->json([
                'message' => 'Order cancelled',
                'data' => [],
            ]);
        }
        return response()->json([
            'message' => 'Something went wrong',
            'data' => [],
        ]);
    }


    public function updatePayment(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $updated = Order::where('id', $data['order_id'])
            ->update([
                'payment_status' => 'paid',
                'payment_method' => 'cod',
                'status' => 'delivered'
            ]);

        if ($updated) {
            return response()->json([
                'message' => 'Order delivered',
                'data' => [],
            ]);
        }
        return response()->json([
            'message' => 'Something went wrong',
            'data' => [],
        ]);
    }
}
