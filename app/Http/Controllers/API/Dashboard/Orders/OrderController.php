<?php

namespace App\Http\Controllers\API\Dashboard\Orders;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    // 1. Get All Orders

    public function index(Request $request)
    {

        $locale = request()->header('Accept-Language', 'ar');
        $nameField = $locale === 'ar' ? 'name_ar' : 'name_en';
        $descriptionField = $locale === 'ar' ? 'description_ar' : 'description_en';

        $orders = Order::with(['items.product' => function ($query) use ($nameField, $descriptionField) {
            $query->select(
                'id',
                "$nameField as name",
                "$descriptionField as description",
                'price',
                'quantity',
                'last_quantity',
                'status',
                'image'
            );
        }])->orderBy('created_at', 'desc')->paginate(10);

        if ($orders->isEmpty()) {
            return response()->json(['message' => 'No orders found', 'data' => []]);
        }

        return response()->json([
            'message' => 'All orders',
            'data' => $orders,
        ]);
    }

    // 2. Get Order Details
    public function show($id)
    {
        $locale = request()->header('Accept-Language', 'ar');
        $nameField = $locale === 'ar' ? 'name_ar' : 'name_en';
        $descriptionField = $locale === 'ar' ? 'description_ar' : 'description_en';

        $order = Order::with(['user', 'items.product' => function ($query) use ($nameField, $descriptionField) {
            $query->select(
                'id',
                "$nameField as name",
                "$descriptionField as description",
                'price',
                'quantity',
                'last_quantity',
                'status',
                'image'
            );
        }])->find($id);

        if (!$order) {
            return response()->json([
                'message' => 'Order not found',
                'data' => []
            ]);
        }
        return response()->json([
            'message' => 'Order details fetched successfully',
            'data' => $order
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'message' => 'Order not found',
                'data' => []
            ]);
        }

        $data = $request->validate([
            'order_status' => 'required|in:pending,delivered,canceled,shipped',
        ]);

        $order->order_status = $data['order_status'];
        $order->save();

        return response()->json(['message' => 'Order status updated successfully', 'data' => $order]);
    }

    public function destroy($id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found']);
        }

        $order->delete();

        return response()->json(['message' => 'Order deleted successfully']);
    }
    public function assignDriver($id)
    {
        $order = Order::find($id);
        $order->driver_id = auth()->user()->id;
        $order->save();
        return response()->json(['message' => 'Driver assigned successfully', 'data' => $order]);
    }
}
