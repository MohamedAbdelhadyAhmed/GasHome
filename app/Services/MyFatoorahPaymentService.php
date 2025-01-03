<?php
namespace App\Services;

use Exception;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItems;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\Storage;
use App\Interfaces\PaymentGatewayInterface;

class MyFatoorahPaymentService  implements PaymentGatewayInterface
{
    protected $api_key;
    protected $base_url;
    protected $header;

    public function __construct()
    {
        $this->base_url ='https://api-sa.myfatoorah.com/';
        $this->api_key = 'V6eLUuafgYq494aQHHcvMNgfjjZVu0a9XJSiokIeyOX213fLWEWxh0RUDKFgAnD_yNFK_o1J2qwGaPg9kR5s3AcYqqbbRBfcDsjcESri22vUWCGdzrXyOaoUnkiIJG-p0U48wTUoC7WmOT_cxc6SGTz-N3lNkHSoiK_71uS543U0-so-LCp0eBxwpFLePe02BQpoHTpmgfwRg0jHm3n6UZ7wmE3V2Fcu5HAeFCUu7SOMEI3nxXe2m4o9fPPEL3EL4ryuOrFVeAW2x8gq91EBWa66QFoHniMLVWHTlEm_aJ68VWbLsNWWsna5nMG0A_dAUzG-oHg3O8eFXw8CRnoxrYo0HYlwb4Z6V5aXd5Nb8s_26gvemlqEYwOURRW8quwxBd42EqDzjMTECsdgt0LyLhDP2GzwkzZUXhKoEF-01SnPuz6Sx0bQ8b5j8Y651eZd7NRQ1rEGXedFi-prsVmEM7EDdVED3eCZe_clG_kgXDPOAextN9Exm0Fsqi9YPviUT6uh3jK8Pj8aiWgOhS8T1ndSPo_LZbC7FN8r2LObHZaVXdaweKNz-eqXHEsq0YAmgHKehtYJ5601mIfzvwceJJ-ffF-FHbDPukr5DJBOoLeQyp0vJqxcaf367vgRIeMXwFtEm-gyZOVfj9rmVXDNVbbSvXugkq7tn-4LjI8u0nNBXAcHmHHFdwEcg1lNEAWq9-BadcbL_hZLw1aet9BQ0glP7Z5B2vUQeo4sGyBIl0ipKD-R';
         $this->header = [
            'accept' => 'application/json',
            "Content-Type" => "application/json",
            "Authorization" => "Bearer " . $this->api_key,
        ];
    }

    public function sendPayment(array $data): array
    {
        try {
            $data['NotificationOption'] = "LNK";
            $data['Language'] = "en";
            $data['CallBackUrl'] = route('payment.callback');
            $data['ErrorUrl'] = route('payment.error');

            $response = $this->buildRequest('POST', '/v2/SendPayment', $data);

            if ($response['success'] && isset($response['data']['InvoiceURL'], $response['data']['InvoiceId'])) {
                return [
                    'success' => true,
                    'url' => $response['data']['InvoiceURL'],
                    'payment_id' => $response['data']['InvoiceId'],
                ];
            }

            return ['success' => false, 'message' => $response['message'] ?? 'Invalid payment response.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'An error occurred during the payment process.'];
        }
    }

    public function callBack(Request $request): bool
    {
        try {
            $paymentId = $request->input('paymentId');
            $response = $this->validatePayment($paymentId);

            if ($response['success'] && $response['data']['InvoiceStatus'] === 'Paid') {
                $this->handlePaymentSuccess($paymentId);
                return true;
            }

            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    public function validatePayment(string $paymentId): array
    {
        try {
            $data = [
                'KeyType' => 'paymentId',
                'Key' => $paymentId,
            ];

            $response=$this->buildRequest('POST', '/v2/getPaymentStatus', $data);

            return $response['success'] ? $response : ['success' => false, 'message' => 'Invalid payment status response.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'An error occurred during payment validation.'];
        }
    }

    private function buildRequest($method, $url, $data = null, $type = 'json'): array
    {
        try {
            $response = Http::withHeaders($this->header)->send($method, $this->base_url . $url, [
                $type => $data
            ]);

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'data' => $response->json(),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'status' => 500,
                'message' => $e->getMessage(),
            ];
        }
    }

    private function handlePaymentSuccess(string $paymentId)
    {
        $order = Order::where('payment_id', $paymentId)->firstOrFail();
        Cart::where('user_id', $order->user_id)->delete();
        return response()->json([
            'message' => 'Payment successful and cart cleared.',
        ]);
    }

}
