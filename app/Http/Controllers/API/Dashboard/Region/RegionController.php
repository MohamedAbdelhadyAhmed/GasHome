<?php

namespace App\Http\Controllers\API\Dashboard\Region;

use App\Http\Controllers\Controller;
use App\Models\AvailableAddress;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    //
    public function addRegion(Request $request)
    {
        // dd($request->all());
        $data = $request->validate(
            [
                'name_ar' => 'required|string',
                'name_en' => 'required|string',
                'coordinates' => 'required|array',
                'coordinates.*.lat' => 'required|numeric',
                'coordinates.*.lng' => 'required|numeric',
            ]
        );
        // dd('add region');
        $region =  AvailableAddress::create(
            [
                'name_ar' => $data['name_ar'],
                'name_en' => $data['name_en'],
                'coordinates' =>  json_encode($data['coordinates']),

            ]
        );
        return response()->json([
            'message' => 'Region added successfully',
            'data' => $region
        ]);
    }
    public function allRegions()
    {
        // dd('all regions');
        $regions = AvailableAddress::all();
        if (empty($regions)) {
            return response()->json([
                'message' => 'No regions found',
                'data' => []
            ]);
        }
        return response()->json([
            'message' => 'Regions fetched successfully',
            'data' => $regions
        ]);
    }
}
