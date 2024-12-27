<?php

namespace App\Http\Controllers\API\Driver\Auth;

use App\Models\Driver;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{

    public function login(Request $request)
    {

        $data = $request->validate([
            'phone_number' => ['required', 'exists:drivers,phone_number'],
            'password' => ['required'],
        ]);


        $driver = Driver::where('phone_number', $request->phone_number)->first();

        if (!$driver) {
            return response()->json([
                'message' => "Driver not verified. Please verify your phone number.",
                'data' => [],
            ]);
        }


        if (!Hash::check($request->password, $driver->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
                'data' => [],
            ]);
        }
        $driver->token = "Bearer " . $driver->createToken('driver-token', ['role:driver'])->plainTextToken;
        return response()->json([
            'message' => 'Driver Logged In Successfully.',
            'data' => $driver,
        ]);
    }
    public function logout()
    {
        $driver = Auth::guard('sanctum')->user();

        if (!$driver) {
            return response()->json([
                'status' => false,
                'message' => 'Driver not authenticated.',
                'data' => [],
            ]);
        }
        $driver->tokens()->delete();

        return response()->json([
            'message' => 'Driver Logged Out Successfully.',
            'data' => [],
        ]);
    }
}
