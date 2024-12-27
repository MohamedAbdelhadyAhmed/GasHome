<?php

namespace App\Http\Controllers\API\Dashboard\Customers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    //
    public function editProfile(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|integer',
            'address' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        /** @var \App\Models\User $user */
        $user = auth()->user();

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            if ($user->image && Storage::disk('public')->exists($user->image)) {
                Storage::disk('public')->delete($user->image);
            }
            $data['image'] = $request->file('image')->store('uploads/admin', ['disk' => 'public']);
        }

        $user->update($data);

        return response()->json([
            'message' => 'Profile Updated Successfully',
            'data' => $user->fresh()
        ]);
    }

    public function changePassword(Request $request)
    {
        $data = $request->validate([
            'new_password' => 'required',
            'confirm_password=>' => 'required|same:new_password',
        ]);
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $user->update([
            'password' => Hash::make($data['new_password']),
        ]);

        return response()->json([
            'message' => 'Password Updated Successfully',
            'data' => $user->fresh()
        ]);
    }
}
