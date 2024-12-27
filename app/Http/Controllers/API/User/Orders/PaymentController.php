<?php

namespace App\Http\Controllers\API\User\Orders;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Interfaces\PaymentGatewayInterface;

class PaymentController extends Controller
{
    protected PaymentGatewayInterface $paymentGateway;

    public function __construct(PaymentGatewayInterface $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

    public function paymentProcess(Request $request)
    {
        $response = $this->paymentGateway->sendPayment($request);
        return response()->json($response, 200);
    }

    public function callBack(Request $request): \Illuminate\Http\RedirectResponse
    {
        $response = $this->paymentGateway->callBack($request);
        if ($response) {
            return redirect()->route('payment.success');
        }
        return redirect()->route('payment.failed');
    }

    public function success()
    {
        return view('payment-success');
    }

    public function failed()
    {
        return view('payment-failed');
    }
}
