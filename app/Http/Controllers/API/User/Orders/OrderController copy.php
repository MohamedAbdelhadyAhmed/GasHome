<?php

namespace App\Http\Controllers\API\User\Orders;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItems;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Address;

class OrderController extends Controller
{
    //
    // public function completeOrder()
    // {
    //     // نتاكد الاول ان الكميات متاحة
    //     // بعد تاكيد اتمام الشراء من العربة
    //     $user = auth()->guard('sanctum')->user();
    //     if (!$user) {
    //         return response()->json(['message' => 'Unauthorized', 'data' => []]);
    //     }

    //     $totalCost = DB::table('carts')
    //         ->join('products', 'carts.product_id', '=', 'products.id')
    //         ->where('carts.user_id', $user->id)
    //         ->selectRaw('SUM(products.price * carts.quantity) as total_cost')
    //         ->value('total_cost');


    //     return response()->json([
    //         'message' => 'Total Cost',
    //         'order' => $totalCost,
    //     ]);
    // }
    public function createOrder()
    {
        $user = auth()->guard('sanctum')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized', 'data' => []]);
        }

        $cartItems = DB::table('carts')
            ->join(
                'products',
                'carts.product_id',
                '=',
                'products.id'
            )
            ->where('carts.user_id', $user->id)
            ->select(
                'products.id',
                'products.price',
                'products.name',
                'products.quantity as available_quantity',
                'carts.quantity as requested_quantity'
            )
            ->get();
        // dd($cartItems);

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
            // 'address_id' => ['required', 'exists:addresses,id'],
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
            return response()->json([
                'message' => 'No items in the cart',
                'data' => []
            ]);
        }
        $address_id = Address::where('user_id', $user->id)->first()->id;

        if ($data['payment_method'] == 'cod') {
            //    dd($address_id);
            $order = Order::create([
                'user_id' => $user->id,
                'address_id' => $address_id,
                'total_price' =>  $totalCost,
            ]);
            $cartItems = Cart::where('user_id', $user->id)->get();

            foreach ($cartItems as $item) {
                $product = $item->product;
                if ($product->quantity < $item->quantity) {
                    return response()->json([
                        'message' => 'Insufficient stock for product ID ' . $item->product_id,
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
                'message' => 'Order created successfully',
                'order' => $order,
            ]);
        } else {
            // online payment
            $order = Order::create([
                'user_id' => $user->id,
                'address_id' => $address_id,
                'total_price' => $totalCost,
                'payment_status' => 'unpaid',
                'order_status' => 'pending',
                'delivery_date' => $data['delivery_date'],
            ]);
            
        }
    }
}

//-------------------
