<?php

namespace App\Http\Controllers\API\User\Address;

use App\Models\Address;
use Illuminate\Http\Request;
use App\Models\AvailableAddress;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    //
    public function storeAddress(Request $request)
    {
        $data = $request->validate([
            'city' => 'required|string',
            'state' => 'required|string',
            'street_name' => 'required|string',
            'building_number' => 'nullable|string',
            'status' => 'required|in:home,other',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json([
                'message' => 'User not found',
                'data' => [],
            ]);
        }

        $data['user_id'] = $user->id;

        $status = $this->checkLocation($data['latitude'], $data['longitude']);
        if (!$status) {
            return response()->json([
                'message' => 'Location not allowed',
                'data' => [],
            ]);
        }
        $existingAddress = Address::where('user_id', $user->id)
            ->where('latitude', $data['latitude'])
            ->where('longitude', $data['longitude'])
            ->first();

        if ($existingAddress) {
            return response()->json([
                'message' => 'This address already exists',
                'data' => [],
            ]);
        }
        $firstAddress = Address::where('user_id', $user->id)->get();
        if ($firstAddress->count() > 0) {
            $data['status'] = 'other';
        }

        $address = Address::create($data);

        return response()->json([
            'message' => 'Address added successfully',
            'address' => $address,
        ]);
    }

    public function getAddresses()
    {
        $user = Auth::guard('sanctum')->user();
        if ($user) {
            $addresses = Address::where('user_id', $user->id)->get();
            return response()->json([
                'message' => 'All Addresses',
                'data' => $addresses,
            ]);
        }
        return response()->json([
            'message' => 'User not found',
            'data' => [],
        ]);
    }

    public function checkLocation($lat, $lng)
    {
        $userLocation = [
            'lat' => $lat,
            'lng' => $lng,
        ];

        $areas = AvailableAddress::all();
        if ($areas->isEmpty()) {
            return false;
        }

        foreach ($areas as $area) {
            $polygon = json_decode($area->coordinates, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return false;
            }
            if ($this->isPointInPolygon($userLocation, $polygon)) {
                return true;
            }
        }

        return false;
    }

    private function isPointInPolygon($point, $polygon)
    {
        $x = $point['lat'];
        $y = $point['lng'];
        $inside = false;
        $n = count($polygon);

        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            $xi = $polygon[$i]['lat'];
            $yi = $polygon[$i]['lng'];
            $xj = $polygon[$j]['lat'];
            $yj = $polygon[$j]['lng'];

            $intersect = (($yi > $y) != ($yj > $y)) &&
                ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi);

            if ($intersect) {
                $inside = !$inside;
            }
        }

        return $inside;
    }
}
