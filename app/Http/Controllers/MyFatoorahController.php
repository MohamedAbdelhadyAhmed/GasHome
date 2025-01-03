<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Cart;
use App\Models\User;
use App\Models\Order;
use App\Models\Address;
use App\Models\OrderItems;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MyFatoorah\Library\MyFatoorah;
use MyFatoorah\Library\API\Payment\MyFatoorahPayment;
use MyFatoorah\Library\API\Payment\MyFatoorahPaymentStatus;
use MyFatoorah\Library\API\Payment\MyFatoorahPaymentEmbedded;

class MyFatoorahController extends Controller
{

    /**
     * @var array
     */
    public $mfConfig = [];

    // Initiate MyFatoorah Configuration
    public function __construct()
    {
        $this->mfConfig = [
            'apiKey'      => config('myfatoorah.api_key'),
            'isTest'      => config('myfatoorah.test_mode'),
            'countryCode' => config('myfatoorah.country_iso'),
        ];
    }

    // Redirect to MyFatoorah Invoice URL
    public function index()
    {
        try {
            $paymentId = request('pmid') ?: 0;
            $sessionId = request('sid') ?: null;
            $orderId   = request('oid') ?: 147;
            $curlData  = $this->getPayLoadData($orderId);
            $mfObj     = new MyFatoorahPayment($this->mfConfig);
            $payment   = $mfObj->getInvoiceURL($curlData, $paymentId, $orderId, $sessionId);

            return response()->json([
                'message' => 'Payment Linke.',
                'data'    => $payment['invoiceURL'],
            ]);
        } catch (Exception $ex) {
            $exMessage = __('myfatoorah.' . $ex->getMessage());
            return response()->json(['IsSuccess' => 'false', 'Message' => $exMessage]);
        }
    }

    // Map order data to MyFatoorah
    private function getPayLoadData($orderId = null)
    {
        $callbackURL = route('myfatoorah.callback');
        $order       = $this->getTestOrderData($orderId);

        if (!$order) {
            throw new Exception('Order not found');
        }

        $user = User::find($order->user_id);
        if (!$user) {
            throw new Exception('User not found');
        }

        return [
            'CustomerName'       => $user->first_name . " " . $user->last_name,
            'InvoiceValue'       => $order->total_price,
            'DisplayCurrencyIso' => "SAR",
            'CustomerEmail'      => $user->email ? $user->email : "test@test.com",
            'CallBackUrl'        => $callbackURL,
            'ErrorUrl'           => $callbackURL,
            'MobileCountryCode'  => '+965',
            'CustomerMobile'     => "1158071449",
            'Language'           => 'en',
            'CustomerReference'  => $orderId,
            'SourceInfo'         => 'Laravel ' . app()::VERSION . ' - MyFatoorah Package ' . MYFATOORAH_LARAVEL_PACKAGE_VERSION
        ];
    }

    // Handle MyFatoorah Payment Callback
    // public function callback() {
    //     try {
    //         $paymentId = request('paymentId');
    //         $mfObj = new MyFatoorahPaymentStatus($this->mfConfig);
    //         $data = $mfObj->getPaymentStatus($paymentId, 'PaymentId');

    //         if ($data->InvoiceStatus == 'Paid') {
    //             $this->PaymentDone($data->CustomerReference);
    //         }

    //         $message = $this->getTestMessage($data->InvoiceStatus, $data->InvoiceError);
    //         $response = ['IsSuccess' => true, 'Message' => $message, 'Data' => $data];

    //     } catch (Exception $ex) {
    //         $exMessage = __('myfatoorah.' . $ex->getMessage());
    //         $response = ['IsSuccess' => 'false', 'Message' => $exMessage];
    //     }

    // }
    public function callback()
    {
        try {
            $paymentId = request('paymentId');
            $mfObj = new MyFatoorahPaymentStatus($this->mfConfig);
            $data = $mfObj->getPaymentStatus($paymentId, 'PaymentId');
            $message = $this->getTestMessage($data->InvoiceStatus, $data->InvoiceError);
            if ($data->InvoiceStatus === 'Paid') {
                $this->PaymentDone($data->CustomerReference);
                return redirect()->route('payment.success', ['message' => $message, 'data' => $data]);
            } else {
                // delete the order
                $order = Order::find($data->CustomerReference);

                if ($order) {
                    $order->delete();
                } else {
                    return response()->json(['IsSuccess' => false, 'Message' => 'Order not found']);
                }


                return redirect()->route('payment.failed', ['message' => 'Payment failed due to ' . $data->InvoiceError]);
            }
        } catch (Exception $ex) {
            $exMessage = __('myfatoorah.' . $ex->getMessage());
            return redirect()->route('payment.failed', ['message' => $exMessage]);
        }
    }


    // Process payment and add cart items to order
    public function PaymentDone($orderId)
    {
        $order = Order::find($orderId);
        if (!$order) {
            return response()->json(['IsSuccess' => false, 'Message' => 'Order not found']);
        }

        $user = User::find($order->user_id);
        if (!$user) {
            return response()->json(['IsSuccess' => false, 'Message' => 'User not found']);
        }

        $totalCost = DB::table('carts')
            ->join('products', 'carts.product_id', '=', 'products.id')
            ->where('carts.user_id', $user->id)
            ->select(DB::raw('SUM(products.price * carts.quantity) as total_cost'))
            ->value('total_cost');

        $address = Address::where('id', $order->address_id)->where('user_id', $user->id)->first();
        if (!$address) {
            return response()->json(['IsSuccess' => false, 'Message' => 'Address not found']);
        }

        $cartItems = Cart::where('user_id', $user->id)->get();

        foreach ($cartItems as $item) {
            $product = $item->product;
            if ($product) {
                OrderItems::create([
                    'order_id'    => $order->id,
                    'product_id'  => $item->product_id,
                    'quantity'    => $item->quantity,
                    'price'       => $product->price,
                ]);
                $product->decrement('quantity', $item->quantity);
            }
        }

        Cart::where('user_id', $user->id)->delete();
    }

    // Display enabled gateways at MyFatoorah account
    public function checkout()
    {
        try {
            $orderId = request('oid') ?: 147;
            $order   = $this->getTestOrderData($orderId);

            if (!$order) {
                throw new Exception('Order not found');
            }

            $customerId = request('customerId');
            $userDefinedField = config('myfatoorah.save_card') && $customerId ? "CK-$customerId" : '';

            $mfObj = new MyFatoorahPaymentEmbedded($this->mfConfig);
            $paymentMethods = $mfObj->getCheckoutGateways($order['total'], $order['currency'], config('myfatoorah.register_apple_pay'));

            if (empty($paymentMethods['all'])) {
                throw new Exception('noPaymentGateways');
            }

            $mfSession = $mfObj->getEmbeddedSession($userDefinedField);
            $isTest = $this->mfConfig['isTest'];
            $vcCode = $this->mfConfig['countryCode'];
            $countries = MyFatoorah::getMFCountries();
            $jsDomain = ($isTest) ? $countries[$vcCode]['testPortal'] : $countries[$vcCode]['portal'];

            return view('myfatoorah.checkout', compact('mfSession', 'paymentMethods', 'jsDomain', 'userDefinedField'));
        } catch (Exception $ex) {
            $exMessage = __('myfatoorah.' . $ex->getMessage());
            return view('myfatoorah.error', compact('exMessage'));
        }
    }

    // Webhook to update transaction status
    public function webhook(Request $request)
    {
        try {
            $secretKey = config('myfatoorah.webhook_secret_key');
            if (empty($secretKey)) {
                return response(null, 404);
            }

            $mfSignature = $request->header('MyFatoorah-Signature');
            if (empty($mfSignature)) {
                return response(null, 404);
            }

            $body  = $request->getContent();
            $input = json_decode($body, true);
            if (empty($input['Data']) || empty($input['EventType']) || $input['EventType'] != 1) {
                return response(null, 404);
            }

            if (!MyFatoorah::isSignatureValid($input['Data'], $secretKey, $mfSignature, $input['EventType'])) {
                return response(null, 404);
            }

            $result = $this->changeTransactionStatus($input['Data']);
            return response()->json($result);
        } catch (Exception $ex) {
            $exMessage = __('myfatoorah.' . $ex->getMessage());
            return response()->json(['IsSuccess' => false, 'Message' => $exMessage]);
        }
    }

    // Change transaction status after receiving webhook
    private function changeTransactionStatus($inputData)
    {
        $orderId = $inputData['CustomerReference'];
        $invoiceId = $inputData['InvoiceId'];

        if ($inputData['TransactionStatus'] == 'SUCCESS') {
            $status = 'Paid';
            $error  = '';
        } else {
            $mfObj = new MyFatoorahPaymentStatus($this->mfConfig);
            $data  = $mfObj->getPaymentStatus($invoiceId, 'InvoiceId');
            $status = $data->InvoiceStatus;
            $error  = $data->InvoiceError;
        }

        $message = $this->getTestMessage($status, $error);
        return ['IsSuccess' => true, 'Message' => $message, 'Data' => $inputData];
    }

    // Get order data for MyFatoorah payment
    private function getTestOrderData($orderId)
    {
        return Order::find($orderId);
    }

    // Get test message based on payment status
    private function getTestMessage($status, $error)
    {
        if ($status == 'Paid') {
            return 'Invoice is paid.';
        } elseif ($status == 'Failed') {
            return 'Invoice is not paid due to ' . $error;
        } elseif ($status == 'Expired') {
            return $error;
        }
    }
}
