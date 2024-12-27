<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItems;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Interfaces\PaymentGatewayInterface;

class MyFatoorahPaymentService extends BasePaymentService implements PaymentGatewayInterface
{
    /**
     * Create a new class instance.
     */
    protected $api_key;
    public function __construct()
    {
        $this->base_url = env("MYFATOORAH_BASE_URL");
        $this->api_key = env("MYFATOORAH_API_KEY");
        $this->header = [
            'accept' => 'application/json',
            "Content-Type" => "application/json",
            "Authorization" => "Bearer " . $this->api_key,
        ];
    }

    // public function sendPayment(Request $request): array
    // {
    //     $data = $request->all();
    //     $data['NotificationOption']="LNK";
    //     $data['Language']="en";
    //     $data['CallBackUrl']=$request->getSchemeAndHttpHost().'/api/payment/callback';
    //     $response = $this->buildRequest('POST', '/v2/SendPayment', $data);
    //     //handel payment response data and return it
    //      if($response->getData(true)['success']){
    //          return ['success' => true,'url' => $response->getData(true)['data']['Data']['InvoiceURL']];
    //     }
    //      return ['success' => false,'url'=>route('payment.failed')];
    // }
    public function sendPayment(array $data): array
    {
        $data['NotificationOption'] = "LNK";
        $data['Language'] = "en";
        $data['CallBackUrl'] = route('payment.callback');

        $response = $this->buildRequest('POST', '/v2/SendPayment', $data);

        if ($response->getData(true)['success']) {
            return ['success' => true, 'url' => $response->getData(true)['data']['Data']['InvoiceURL']];
        }

        return ['success' => false, 'url' => route('payment.failed')];
    }


    public function callBack(Request $request): bool
{
    $data = [
        'KeyType' => 'paymentId',
        'Key' => $request->input('paymentId'),
    ];

    $response = $this->buildRequest('POST', '/v2/getPaymentStatus', $data);
    $response_data = $response->getData(true);

    Storage::put('myfatoorah_response.json', json_encode([
        'myfatoorah_callback_response' => $request->all(),
        'myfatoorah_response_status' => $response_data
    ]));

    if ($response_data['data']['Data']['InvoiceStatus'] === 'Paid') {
        $user = auth()->guard('sanctum')->user();
        if (!$user) {
            return false;
        }

        // احصل على جميع العناصر في عربة التسوق للمستخدم
        $cartItems = Cart::where('user_id', $user->id)->get();
        foreach ($cartItems as $item) {
            $product = $item->product;

            if ($product->quantity < $item->quantity) {
                return response()->json([
                    'message' => 'Insufficient stock for product ID ' . $item->product_id,
                ]);
            }
            $order = Order::create([
                'user_id' => $user->id,
                'address_id' => $item->address_id, // تأكد من أن العنوان متاح
                'total_price' => $item->quantity * $product->price,  // تحديد السعر الإجمالي للمنتجات في الطلب
            ]);

            OrderItems::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $product->price,
            ]);
            $product->decrement('quantity', $item->quantity);
        }
        Cart::where('user_id', $user->id)->delete();

        return true;
    }

    return false;
}

}
