<?php

namespace App\Http\Controllers\API\Dashboard\Auth;

use App\Models\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'phone_number' => ['required', 'exists:users,phone_number'],
            'password' => ['required'],
        ]);
        //inactive
        $user = Admin::where('phone_number', $request->phone_number)
        ->where('status','active')->first();
        // user not verified
        if ($user == null) {
            return response()->json([
                'status' => false,
                'message' => "User not verified please verify your phone number",
                'data' => [],
            ]);
        }
        if (! $user || ! Hash::check($request->password, $user->password)) {
            // dd($user->password );
            return response()->json([
                'status' => true,
                'message' => 'The provided credentials are incorrect',
                'data' => [],

            ]);
        }
        // $token  = "Bearer " .  $user->createToken('user_token')->plainTextToken;
        $token = "Bearer " . $user->createToken('user-token', ['role:user'])->plainTextToken;

        $user->token =  $token;
        return response()->json([
            'message' => 'User Logged In Successfully',
            'data' => $user,
        ]);
    }

    public function Logout()
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated.',
                'data' => [],
            ]);
        }
        $user->tokens()->delete();
        //->delete();

        return response()->json([
            'message' => 'User Logged Out Successful',
            'data' => [],
        ]);
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
}
