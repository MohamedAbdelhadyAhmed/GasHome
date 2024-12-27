<?php

namespace App\Http\Controllers\API\User\Home;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    public function index($id)
    {
        $products = Product::where('quantity', '>', 0)
            ->where('status', 'active')
            ->where('category_id', $id)
            ->get()->map(function ($product) {
                $product->image = url(Storage::url('public/'.$product->image));
                return $product;
            });

        if ($products->isEmpty()) {
            return response()->json([
                'message' => "Products Not Found",
                'data' => [],
            ]);
        }
        return response()->json([
            'message' => "All Products.",
            'data' => $products,
        ]);
    }

}
