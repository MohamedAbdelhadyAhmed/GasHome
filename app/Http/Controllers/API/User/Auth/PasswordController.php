<?php

namespace App\Http\Controllers\API\User\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
            $token = "Bearer " . $user->createToken('user-token', ['role:user'])->plainTextToken;
            $user->token =  $token;
            return response()->json([
                'status' => true,
                'message' => 'code sent to phone number',
                'data' => $user,
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
}
