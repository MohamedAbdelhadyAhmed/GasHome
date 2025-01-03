<?php

namespace App\Http\Controllers\API\User\Auth;

use App\Models\User;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        // $data = $request->validate([
        //     'phone_number' => ['required', 'exists:users,phone_number'],
        //     'password' => ['required'],
        // ]);
        $data = $request->validate([
            'phone_number' => [
                'required',
                function ($attribute, $value, $fail) {
                    $existsInUsers = User::where('phone_number', $value)->exists();
                    $existsInDrivers = Driver::where('phone_number', $value)->exists();
                    if (!$existsInUsers && !$existsInDrivers) {
                        $fail('The phone number does not exist in our records.');
                    }
                },
            ],
            'password' => ['required'],
        ]);
        // dd(vars: $request->all());


        $user = User::where('phone_number', $request->phone_number)
            ->where('status', 'active')
            ->first();
        if (!$user) {
            $user = Driver::where('phone_number', $request->phone_number)
                // ->where('status', 'active')
                ->first();
        }
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => "User not verified. Please verify your phone number.",
                'data' => [],
            ]);
        }
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
                'data' => [],
            ]);
        }

        $tokenType = $user instanceof User ? 'user-token' : 'driver-token';
        $role = $user instanceof User ? 'role:user' : 'role:driver';
        $token = "Bearer " . $user->createToken($tokenType, [$role])->plainTextToken;
        $user->token = $token;
        return response()->json([
            'message' =>  $user instanceof User ? 'User Logged In Successfully' : 'Driver Logged In Successfully',
            'data' => $user,
        ]);
    }

    public function Logout()
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated.',
                'data' => [],
            ]);
        }
        /** @var \App\Models\User $user */

        $user->tokens()->delete();
        //->delete();

        return response()->json([
            'message' => 'User Logged Out Successful',
            'data' => [],
        ]);
    }
}
