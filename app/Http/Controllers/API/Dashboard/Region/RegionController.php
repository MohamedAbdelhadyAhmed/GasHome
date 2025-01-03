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
                'latitude' => 'required',
                'longitude' => 'required',
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
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
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
        $locale = request()->header('Accept-Language', 'ar');
        $nameField = $locale === 'ar' ? 'name_ar' : 'name_en';
        $regions = AvailableAddress::where('status', 'active')
        ->select('id', "$nameField as name" , 'latitude','radius',
        'longitude', 'coordinates')
        ->get();

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
