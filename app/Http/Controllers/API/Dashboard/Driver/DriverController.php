<?php

namespace App\Http\Controllers\API\Dashboard\Driver;

use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DriverController extends Controller
{
    public function getDrivers()
    {
        $drivers = Driver::all();

        if ($drivers->isEmpty()) {
            return response()->json([
                'message' => "Drivers not found",
                'data' => [],
            ]);
        }

        return response()->json([
            'message' => "Drivers retrieved successfully",
            'data' => $drivers,
        ]);
    }
    public function getDriverById($id)
    {
        $driver = Driver::find($id);

        if (!$driver) {
            return response()->json([
                'message' => "Driver not found",
                'data' => [],
            ]);
        }

        return response()->json([
            'message' => "Driver retrieved successfully",
            'data' => $driver,
        ]);
    }

    public function addDriver(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'numeric', 'unique:drivers,phone_number'],
            'license_number' => ['required', 'numeric', 'unique:drivers,license_number'],
            'vehicle_license' => ['required', 'numeric', 'unique:drivers,vehicle_license'],
            'vehicle_number' => ['required', 'numeric', 'unique:drivers,vehicle_number'],
            'address' => ['required', 'string'],
            'image' => ['nullable', 'image'],
        ]);

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $data['image'] = $request->file('image')
            ->store('uploads/drivers',['disk' => 'public']);

        }
        $data['password'] = Hash::make($request->phone_number);
        $driver = Driver::create($data);
        // edit image url
        // if ($driver->image) {
        //     $driver->image = url(Storage::url($driver->image));
        // }
        return response()->json([
            'message' => "Driver added successfully",
            'data' => $driver,
        ]);
    }



    public function deleteDriver($id)
    {
        $driver = Driver::find($id);

        if (!$driver) {
            return response()->json([
                'message' => "Driver not found",
                'data' => [],
            ]);
        }
        $driver->delete();
        if ($driver->image && Storage::disk('public')->exists($driver->image)) {
            Storage::disk('public')->delete($driver->image);
        }
        return response()->json([
            'message' => "Driver deleted successfully",
            'data' => [],
        ]);
    }
}
