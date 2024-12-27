<?php

namespace App\Http\Controllers\API\Driver\Auth;

use Carbon\Carbon;
use Vonage\Client;
use App\Models\Driver;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Vonage\Client\Credentials\Basic;

class PasswordController extends Controller
{


    public function SendCode(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|exists:drivers,phone_number',
        ]);
        $driver = Driver::where('phone_number', $request->phone_number)->first();

        if ($driver->name) {
            // cerate code for driver and send to phone number
            $code = rand(1000, 9999);
            $driver->code = $code;
            $driver->save();
            // send code to phone number
            $token = "Bearer " . $driver->createToken('driver-token', ['role:driver'])->plainTextToken;
            $driver->token =  $token;
            return response()->json([
                'message' => 'code sent to phone number',
                'data' => $driver,
            ]);
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
    public function verify(Request $request)
    {
        // token required to get user
        $data = $request->validate([
            'code' => ['required', 'numeric'],
        ]);
        $auth_user = Auth::guard('sanctum')->user();
        if(! $auth_user )
        {
            return response()->json([
                'message' => 'User not found',
                'data' => [],
            ]);
        }

        if ($auth_user->code == $data['code']) {
            // user is verified
            $auth_user->status = 'active';
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
}
