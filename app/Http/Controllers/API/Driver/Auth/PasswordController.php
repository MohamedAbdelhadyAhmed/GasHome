<?php

namespace App\Http\Controllers\API\Driver\Auth;

use Carbon\Carbon;
use Vonage\Client;
use App\Models\Driver;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Vonage\Client\Credentials\Basic;

class PasswordController extends Controller
{


    public function SendCode(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|exists:drivers,phone_number',
        ]);
        $driver = Driver::where('phone_number', $request->phone_number)->first();

        if ($driver->phone_number) {
            // cerate code for driver and send to phone number
            $code = rand(1000, 9999);
            $driver->code = $code;
            $driver->save();

            $phoneNumber = $driver->phone_number;
            $messageBody = "Your verification code is: $code";
            // try {
            //     $smsResponse = $this->sendSms($phoneNumber, $messageBody);
            //     if (isset($smsResponse['status']) && $smsResponse['status'] === 'Success') {
            //         $token = "Bearer " . $driver->createToken('driver-token', ['role:driver'])->plainTextToken;
            //         $driver->token =  $token;
            //         return response()->json([
            //             'status' => true,
            //             'message' => 'code sent to phone number',
            //             'data' => $driver,
            //         ]);
            //     }

            //     return response()->json([
            //         'message' => "Driver Created, but SMS Sending Failed",
            //         'data'    => $driver,
            //         'smsError' => $smsResponse['message'] ?? 'Unknown error',
            //     ]);
            // } catch (\Exception $e) {
            //     return response()->json([
            //         'message' => "driver Created, but SMS Sending Failed",
            //         'data'    => $driver,
            //         'exception' => $e->getMessage(),
            //     ]);
            // }
            $token = "Bearer " . $driver->createToken('driver-token', ['role:driver'])->plainTextToken;
            $driver->token =  $token;
            return response()->json([
                'status' => true,
                'message' => 'code sent to phone number',
                'data' => $driver,
            ]);
            // send code to phone number
        } else {
            // return $this->data([], 'User not Found');
            return response()->json([
                'message' => 'Driver not Found',
                'data' => [],

            ]);
        }

    }
    public function ResetPasswordChangePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);
        $driver = Auth::guard('sanctum')->user();
        if ($driver) {
            $driver->password = Hash::make($request->password);
            /** @var \App\Models\Driver $driver */
            $driver->save();
            $driver->token =  $request->header('Authorization');
            return response()->json([

                'message' => 'Password changed successfully',
                'data' => $driver,
            ]);
        } else {
            return response()->json([
                'message' => 'driver not found',
                'data' => [],

            ]);
        }
    }
    public function verify(Request $request)
    {
        // token required to get user
        $data = $request->validate([
            'code' => ['required', 'numeric'],
        ]);
        $auth_user = Auth::guard('sanctum')->user();
        if (! $auth_user) {
            return response()->json([
                'message' => 'Driver not found',
                'data' => [],
            ]);
        }

        if ($auth_user->code == $data['code']) {
            // user is verified
            $auth_user->status = 'active';
            /** @var \App\Models\Driver $auth_user */

            $auth_user->save();

            $auth_user->token =  $request->header('Authorization');
            return response()->json([
                'message' => "Driver Verified Successfully",
                'data' => $auth_user,
            ]);
        } else {
            return response()->json([
                'message' => "Code is not valid",
                'data' => [],
            ]);
        }
    }
    public function sendSms($number, $message)
    {
        $url = "https://app.mobile.net.sa/api/v1/send";
        $token = "gmillB6pQYOkxGqWKwJDU9VrEkbC3dTLSid70AAn";

        $response = Http::withToken($token)->post($url, [
            'number' => $number,
            'senderName' => 'gazhome',
            'messageBody' => $message,
            'sendAtOption' => 'Now',
            'allow_duplicate' => true,
        ]);
        return $response->json();
    }
}
