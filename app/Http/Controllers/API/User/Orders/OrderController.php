<?php

namespace App\Http\Controllers\API\User\Orders;

use Log;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Address;
use App\Models\OrderItems;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use MyFatoorah\Library\API\Payment\MyFatoorahPayment;

class OrderController extends Controller
{

    public function createOrder()
    {
        $user = auth()->guard('sanctum')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized', 'data' => []]);
        }

        $cartItems = DB::table('carts')
            ->join('products', 'carts.product_id', '=', 'products.id')
            ->where('carts.user_id', $user->id)
            ->select('products.id', 'products.price', 'products.name_ar', 'products.name_en', 'products.quantity as available_quantity', 'carts.quantity as requested_quantity')
            ->get();

        foreach ($cartItems as $item) {
            if ($item->requested_quantity > $item->available_quantity) {
                return response()->json([
                    'message' => 'Insufficient stock for product : ' . $item->name,
                    'data' => []
                ]);
            }
        }

        $totalCost = $cartItems->sum(function ($item) {
            return $item->requested_quantity * $item->price;
        });

        return response()->json([
            'message' => 'All items are available.',
            'totalCost' => $totalCost,
        ]);
    }
    public function completeOrder(Request $request)
    {
        $data = $request->validate([
            'address_id' => ['required', 'exists:addresses,id'],
            'payment_method' => ['required', 'in:cod,online'],
            'delivery_date' => ['required', 'date', 'date_format:Y-m-d'],
        ]);

        $user = auth()->guard('sanctum')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized', 'data' => []]);
        }

        $totalCost = DB::table('carts')
            ->join('products', 'carts.product_id', '=', 'products.id')
            ->where('carts.user_id', $user->id)
            ->select(DB::raw('SUM(products.price * carts.quantity) as total_cost'))
            ->value('total_cost');

        if (!$totalCost) {
            return response()->json(['message' => 'No items in the cart', 'data' => []]);
        }

        $address = Address::where('id', $data['address_id'])->where('user_id', $user->id)->first();
        if (!$address) {
            return response()->json(['message' => 'No address found', 'data' => []]);
        }
        if ($data['payment_method'] == 'cod') {
            $order = Order::create([
                'user_id' => $user->id,
                'address_id' => $data['address_id'],
                'total_price' => $totalCost,
                'order_status' => 'pending',
                'payment_methode' => 'cod',
                'delivery_date' => $data['delivery_date'],
            ]);

            $cartItems = Cart::where('user_id', $user->id)->get();

            foreach ($cartItems as $item) {
                $product = $item->product;

                if ($product->quantity < $item->quantity) {
                    return response()->json([
                        'message' => 'Insufficient stock for product ID ' . $item->product_id,
                        'data' => []
                    ]);
                }

                OrderItems::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $product->price,
                ]);
                $product->decrement('quantity', $item->quantity);
            }
            Cart::where('user_id', $user->id)->delete();
            return response()->json([
                'message' => 'Order created successfully with Cash on Delivery.',
                'data' => $order,
            ]);
        } elseif ($data['payment_method'] == 'online') {
            $order = Order::create([
                'user_id' => $user->id,
                'address_id' => $data['address_id'],
                'total_price' => $totalCost,
                'delivery_date' => $data['delivery_date'],
            ]);
            //يتم توجيه الطلب إلى كلاس MyFatoorahController واستدعاء الدالة index الخاصة به.
            $mfController = new \App\Http\Controllers\MyFatoorahController();
            // return $mfController->index()->with('oid', $order->id);
            return redirect()->route('myfatoorah.index', ['oid' => $order->id]);
        }
    }
    // =====================================get Orders ===================================
    // public function getAllUserOrders()
    // {
    //     $user = auth()->guard('sanctum')->user();
    //     if (!$user) {
    //         return response()->json(['message' => 'User not found', 'data' => []]);
    //     }
    //     $orders = Order::where('user_id', $user->id)->with(['orderItems.product'])
    //     ->get();
    //     if (!$orders) {
    //         return response()->json(['message' => 'No orders found', 'data' => []]);
    //     }
    //     return response()->json([
    //         'message' => 'All orders',
    //         'data' => $orders,
    //     ]);
    // }
    public function getAllUserOrders()
    {
        
    }

    public function getOrderById($id)
    {
        $user = auth()->guard('sanctum')->user();
        if (!$user) {
            return response()->json(['message' => 'User not found', 'data' => []]);
        }
        $locale = request()->header('Accept-Language', 'ar');
        $nameField = $locale === 'ar' ? 'name_ar' : 'name_en';
        $descriptionField = $locale === 'ar' ? 'description_ar' : 'description_en';

        $order = Order::where('user_id', $user->id)
            ->where('id', $id)
            ->with(['items.product' => function ($query) use ($nameField, $descriptionField) {
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
            }])
            ->first();

        if (!$order) {
            return response()->json([
                'message' => 'Order not found',
                'data' => []
            ]);
        }

        return response()->json([
            'message' => 'Order details',
            'data' => $order,
        ]);
    }
}
