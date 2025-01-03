<?php

namespace App\Http\Controllers\API\User\Auth;

use App\Models\User;
use App\Models\Address;
use Illuminate\Http\Request;
use App\Services\MadarSmsService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class RegisterController extends Controller
{

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'phone_number' => ['required', 'numeric', 'unique:users,phone_number'],
            'password' => ['required', 'min:8', 'max:255', 'confirmed'],
        ]);

        $code = rand(1000, 9999);
        $data['code'] = $code;
        $data['status'] = 'inactive';
        $data['password'] = Hash::make($request->password);

        $user = User::create($data);

        if ($user->id) {
            $phoneNumber = $user->phone_number;
            $messageBody = "Your verification code is: $code";

            // try {
            //     $smsResponse = $this->sendSms($phoneNumber, $messageBody);
            //     if (isset($smsResponse['status']) && $smsResponse['status'] === 'Success') {
            //         $token = "Bearer " . $user->createToken('user-token', ['role:user'])->plainTextToken;
            //         $user->token = $token;

            //         return response()->json([
            //             'message' => "User Created and SMS Sent Successfully",
            //             'data'    => $user,
            //         ]);
            //     }

            //     return response()->json([
            //         'message' => "User Created, but SMS Sending Failed",
            //         'data'    => $user,
            //         'smsError' => $smsResponse['message'] ?? 'Unknown error',
            //     ]);
            // } catch (\Exception $e) {
            //     return response()->json([
            //         'message' => "User Created, but SMS Sending Failed",
            //         'data'    => $user,
            //         'exception' => $e->getMessage(),
            //     ]);
            // }
            $token = "Bearer " . $user->createToken('user-token', ['role:user'])->plainTextToken;
            $user->token = $token;

            return response()->json([
                'message' => "User Created and SMS Sent Successfully",
                'data'    => $user,
            ]);
        }

        return response()->json(data: [
            'message' => "Something Went Wrong",
            'data' => [],
        ]);
    }




    public function verify(Request $request)
    {
        // token required to get user

        $data = $request->validate([
            'code' => ['required', 'numeric'],
        ]);
        $auth_user = Auth::guard('sanctum')->user();
        if ($auth_user->code == $data['code']) {
            // user is verified
            $auth_user->status = 'active';
            /** @var \App\Models\User $auth_user */
            $auth_user->save();

            $auth_user->token =  $request->header('Authorization');
            return response()->json([
                'message' => "User Verified Successfully",
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
