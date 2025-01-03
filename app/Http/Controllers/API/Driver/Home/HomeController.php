<?php

namespace App\Http\Controllers\API\Driver\Home;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\AvailableAddress;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    //
    // get all orders of city
    // public function getOrders(Request $request)
    // {
    //     $orders = Order::where('city_id', $request->city_id)->get();
    //     return response()->json(['orders' => $orders], 200);
    // }
    //================================================= getOrdersByRegion ======================================
    public function allRegions()
    {
        // dd('all regions');
        $locale = request()->header('Accept-Language', 'ar');
        $nameField = $locale === 'ar' ? 'name_ar' : 'name_en';
        $regions = AvailableAddress::where('status', 'active')
            ->select(
                'id',
                "$nameField as name",
                'latitude',
                'radius',
                'longitude',
                'coordinates'
            )
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
    public function getOrdersByRegion($region_id)
    {
        $region = AvailableAddress::find($region_id);
        if (!$region) {
            return response()->json([
                'message' => 'Region not found',
                'data' => [],
            ]);
        }
        $polygon = json_decode($region->coordinates, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'message' => 'Invalid region coordinates',
                'data' => [],
            ]);
        }
        $addresses = Address::all()->filter(function ($address) use ($polygon) {
            $point = [
                'lat' => $address->latitude,
                'lng' => $address->longitude,
            ];
            return $this->isPointInPolygon($point, $polygon);
        });
        $addressIds = $addresses->pluck('id')->toArray();
        $orders = Order::where('order_status', 'pending')->whereIn('address_id', $addressIds)->get();
        return response()->json([
            'message' => "Orders in   $region->name_en",
            'data' => $orders,
        ]);
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

    //================================================= Add order to Driver  ======================================
    public function addOrder(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $driver = Auth::guard('sanctum')->user();
        if (!$driver) {
            return response()->json([
                'message' => 'Driver not authenticated',
                'data' => [],
            ]);
        }

        $order = Order::find($data['order_id']);
        if (!$order) {
            return response()->json([
                'message' => 'Order not found',
                'data' => [],
            ]);
        }

        $order->driver_id = $driver->id;
        $order->order_status = 'shipped';
        $order->save();
        if ($order->refresh()->driver_id === $driver->id) {
            return response()->json([
                'message' => 'Order assigned to driver successfully',
                'data' => $order,
            ]);
        } else {
            return response()->json([
                'message' => 'Failed to assign order to driver',
                'data' => [],
            ]);
        }
    }

    //================================================= getOrdersByDriver ======================================
    public function getOrdersByDriver()
    {

        $driver_id = Auth::guard('sanctum')->user()->id;
        if (!$driver_id) {
            return response()->json([
                'message' => 'Driver not found',
                'data' => [],
            ]);
        }
        $orders = Order::where('driver_id', $driver_id)->where('order_status', 'shipped')->get();
        if ($orders->isEmpty()) {
            return response()->json([
                'message' => 'No orders found',
                'data' => []
            ]);
        }
        return response()->json(
            [
                'message' => 'All orders',
                'data' => $orders
            ]
        );
    }
}
