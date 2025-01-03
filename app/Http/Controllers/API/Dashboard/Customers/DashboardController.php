<?php

namespace App\Http\Controllers\API\Dashboard\Customers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin;
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
            'phone_number' => 'nullable|integer',
            'address' => 'nullable|string',
            'image' => 'nullable|image',
        ]);

        /** @var \App\Models\User $user */
        $user = auth()->user();

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            if ($user->image && Storage::disk('public')->exists($user->image)) {
                Storage::disk('public')->delete($user->image);
            }
            $data['image'] = $request->file('image')->store('uploads/employee', ['disk' => 'public']);
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

    public function addUser(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'nullable|email|unique:admins',
            'phone_number' => 'required|integer',
            'address' => 'required|string',
            'image' => 'nullable|image',
        ]);
        $data['password'] = Hash::make('phone_number');
        $data['role'] = 'employee';
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $data['image'] = $request->file('image')
                ->store('uploads/employee', ['disk' => 'public']);
        }
        $employee = Admin::create($data);

        return response()->json([
            'message' => 'Employee Added Successfully',
            'data' => $employee->fresh()
        ]);
    }
}
