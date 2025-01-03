<?php

namespace App\Http\Controllers\API\Dashboard\Category;

use App\Models\Order;
use App\Models\Driver;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{

    public function index()
    {
        // 1- get all avilable drivers
        $allDrivers = Driver::count();


        $driversWithOrdersToday = Driver::whereHas('orders', function ($query) {
            $query->whereDate('created_at', Carbon::today());
        })->count();

        // $activeDrivers = Driver::where('status', 'active')->count();
        // 2- get all todays orders and delevided orders
        $todaysOrders = Order::whereDate('created_at', Carbon::today())->count();

        $deliveredOrders = Order::whereDate('created_at', Carbon::today())
            ->where('order_status', 'delivered')
            ->count();
        $shippedOrders = Order::whereDate('created_at', Carbon::today())
            ->where('order_status', 'shipped')
            ->count();
        $undeliveredOrders = Order::whereDate('created_at', Carbon::today())
            ->where('order_status',  'pending')
            ->count();
        // 3- get all products
        $locale = request()->header('Accept-Language', 'ar');
        $nameField = $locale === 'ar' ? 'name_ar' : 'name_en';
        $descriptionField = $locale === 'ar' ? 'description_ar' : 'description_en';

        // $products = Product::where('status', 'active')
        //     ->select(
        //         'id',
        //         "$nameField as name",
        //         "$descriptionField as description",
        //         'price',
        //         'quantity',
        //         'last_quantity',
        //         'status',
        //         'image',
        //     )   ->get();
        $categories = Category::with(['products' => function ($query) use ($nameField, $descriptionField) {
            $query->where('status', 'active')
                ->select(
                    'id',
                    "$nameField as name",
                    "$descriptionField as description",
                    'price',
                    'quantity',
                    'last_quantity',
                    'status',
                    'image',
                    'category_id'
                );
        }])
            ->select('id', "$nameField as name")
            ->get();

        // return response()->json([
        //     'allDrivers' => $allDrivers,
        //     'activeDrivers' => $activeDrivers,

        //     'todaysOrders' => 64,

        //     'deliveredOrders' => 7,
        //     'shippedOrders' => 2,

        //     'undeliveredOrders' => 55,

        //     'products' => $categories,
        // ],);
        return response()->json([
            'allDrivers' => $allDrivers,
            'activeDrivers' => $driversWithOrdersToday,

            'todaysOrders' => $todaysOrders,

            'deliveredOrders' => $deliveredOrders,
            'shippedOrders' => $shippedOrders,

            'undeliveredOrders' => $undeliveredOrders,

            'products' => $categories,
        ]);
    }
}
