<?php

namespace App\Http\Controllers\API\User\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MadarSmsService;

class RegisterController extends Controller
{

    public function store(Request $request, MadarSmsService $smsService)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'phone_number' => ['required', 'numeric', 'unique:users,phone_number'],
            'password' => ['required', 'min:8', 'max:255', 'confirmed'],
        ]);

        // Generate random 4-digit code
        $code = rand(1000, 9999);
        $data['code'] = $code;
        $data['status'] = 'inactive';
        $data['password'] = Hash::make($request->password);

        // Store user in database
        $user = User::create($data);

        if ($user->id) {

            $token = "Bearer " . $user->createToken('user-token', ['role:user'])->plainTextToken;
            $user->token = $token;


            return response()->json([
                'message' => "User Created, but SMS Sending Failed",
                'data'    => $user,
            ]);
        }

        return response()->json([
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
}
