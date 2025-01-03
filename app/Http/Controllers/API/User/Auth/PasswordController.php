<?php

namespace App\Http\Controllers\API\User\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class PasswordController extends Controller
{


    public function SendCode(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|exists:users,phone_number',
        ]);
        $user = User::where('phone_number', $request->phone_number)->first();
        if ($user->first_name) {
            // cerate code for user and send to phone number
            $code = rand(1000, 9999);
            $user->code = $code;
            $user->save();
            // send code to phone number
            $phoneNumber = $user->phone_number;
            $messageBody = "Your verification code is: $code";
            try {
                $smsResponse = $this->sendSms($phoneNumber, $messageBody);
                if (isset($smsResponse['status']) && $smsResponse['status'] === 'Success') {
                    $token = "Bearer " . $user->createToken('user-token', ['role:user'])->plainTextToken;
                    $user->token =  $token;
                    return response()->json([
                        'status' => true,
                        'message' => 'code sent to phone number',
                        'data' => $user,
                    ]);
                }

                return response()->json([
                    'message' => "User Created, but SMS Sending Failed",
                    'data'    => $user,
                    'smsError' => $smsResponse['message'] ?? 'Unknown error',
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => "User Created, but SMS Sending Failed",
                    'data'    => $user,
                    'exception' => $e->getMessage(),
                ]);
            }
        } else {
            // return $this->data([], 'User not Found');
            return response()->json([
                'message' => 'User not Found',
                'data' => [],

            ]);
        }
    }
    public function ResetPasswordChangePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);
        $user = Auth::guard('sanctum')->user();
        if ($user) {
            $user->password = Hash::make($request->password);
            /** @var \App\Models\User $user */

            $user->save();
            $user->token =  $request->header('Authorization');
            return response()->json([

                'message' => 'Password changed successfully',
                'data' => $user,
            ]);
        } else {
            return response()->json([
                'message' => 'user not found',
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
