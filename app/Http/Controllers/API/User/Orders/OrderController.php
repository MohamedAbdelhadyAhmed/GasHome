<?php

namespace App\Http\Controllers\API\User\Orders;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItems;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Interfaces\PaymentGatewayInterface;

class OrderController extends Controller
{
    protected PaymentGatewayInterface $paymentGateway;

    public function __construct(PaymentGatewayInterface $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

    public function createOrder()
    {
        $user = auth()->guard('sanctum')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized', 'data' => []]);
        }

        $cartItems = DB::table('carts')
            ->join('products', 'carts.product_id', '=', 'products.id')
            ->where('carts.user_id', $user->id)
            ->select('products.id', 'products.price', 'products.name', 'products.quantity as available_quantity', 'carts.quantity as requested_quantity')
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
            'address_id' => 'required',
            'payment_method' => ['required', 'in:cod,online'],
            'delivery_date' => ['required', 'date', 'date_format:Y-m-d'],
        ]);

        $user = auth()->guard('sanctum')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized', 'data' => []]);
        }

        $totalCost = DB::table('carts')
            ->join('products', 'carts.product_id', '=', 'products.id')
            ->where('carts.user_id', $data['address_id'])
            ->select(DB::raw('SUM(products.price * carts.quantity) as total_cost'))
            ->value('total_cost');
        if (!$totalCost) {
            return response()->json([
                'message' => 'No items in the cart',
                'data' => []
            ]);
        }

        $address = Address::where('user_id', $data['address_id'])->first();

        if (!$address) {
            return response()->json([
                'message' => 'No address found',
                'data' => []
            ]);
        }

        $address_id = $data['address_id'];


        if ($data['payment_method'] == 'cod') {
            // Process cash on delivery
            $order = Order::create([
                'user_id' => $user->id,
                'address_id' => $address_id,
                'total_price' =>  $totalCost,
                'delivery_date' => $data['delivery_date'],
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
        } elseif ($data['payment_method'] == 'online') {
            // Process online payment using MyFatoorah
            $order = Order::create([
                'user_id' => $user->id,
                'address_id' => $address_id,
                'total_price' => $totalCost,
                'payment_status' => 'pending',
                'order_status' => 'pending',
                'delivery_date' => $data['delivery_date'],
            ]);


            $paymentData = [
                // 'Amount' => $totalCost,
                'InvoiceValue' => $totalCost,
                'DisplayCurrencyIso' => 'SAR',
                'InvoiceValue' => $totalCost,
                'CustomerName' => $user->first_name . ' ' . $user->last_name,
                'CustomerMobile' => $user->phone_number,

                // 'CallBackUrl' => route('payment.callback'), // رابط الرجوع
                // 'NotificationOption' => 'LNK',
                // 'Language' => 'en',
            ];

            // إرسال الدفع باستخدام البيانات التي تم تحديدها
            $paymentResponse = $this->paymentGateway->sendPayment($paymentData);

            if ($paymentResponse['success']) {
                return response()->json([
                    'message' => 'Payment initiated successfully',
                    'payment_url' => $paymentResponse['url']
                ]);
            } else {
                return response()->json([
                    'message' => 'Payment initiation failed',
                    'error' => 'Payment failed, please try again.'
                ]);
            }
        }
    }

    // =====================================get Orders ===================================
    public function getAllUserOrders()
    {
        $user = auth()->guard('sanctum')->user();
        if (!$user) {
            return response()->json(['message' => 'User not found', 'data' => []]);
        }
        $orders = Order::where('user_id', $user->id)->get();
        if (!$orders) {
            return response()->json(['message' => 'No orders found', 'data' => []]);
        }
        return response()->json([
            'message' => 'All orders',
            'data' => $orders,
        ]);
    }
    public function getOrderById($id)
    {
        $user = auth()->guard('sanctum')->user();
        if (!$user) {
            return response()->json(['message' => 'User not found', 'data' => []]);
        }
        $order = Order::where('user_id', $user->id)
            ->where('id', $id)
            ->with('products')
            ->first();

        // $order = Order::with(['items', 'products'])
        // ->where('user_id', $user->id)
        // ->where('id', $id)
        // ->first();

        // $order = Order::where('user_id', $user->id)->where('id', $id)->first();
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
