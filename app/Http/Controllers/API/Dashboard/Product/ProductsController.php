<?php

namespace App\Http\Controllers\API\Dashboard\Product;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\QuantityAdded;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($category_id)
    {
        $locale = request()->header('Accept-Language', 'ar');

        $nameField = $locale === 'ar' ? 'name_ar' : 'name_en';
        $descriptionField = $locale === 'ar' ? 'description_ar' : 'description_en';

        $products = Product::where('category_id', $category_id)
        ->select('id', "$nameField as name", "$descriptionField as description", 'price', 'image')
        ->get()
        ->map(function ($product) {
            $product->image = $product->image ? asset('storage/' . $product->image) : null;
            return $product;
        });
        if ($products->isEmpty()) {
            return response()->json([
                'message' => "Products Not Found",
                'data' => [],
            ]);
        }

        return response()->json([
            'message' => "All Products",
            'data' => $products,
        ]);
    }
    // all products where quantity >1

    public function allProducts()
    {
        $locale = request()->header('Accept-Language', 'ar');

        $products = Product::where('quantity', '>', 1)
            ->where('status', 'active')
            ->get()
            ->map(function ($product) use ($locale) {
                $product->image = $product->image ? url(Storage::url($product->image)) : null;
                $product->description = $locale === 'ar' ?
                    $product->description_ar : $product->description_en;
                $product->name = $locale === 'en' ?
                    $product->name_ar : $product->name_en;
                unset($product->description_ar, $product->description_en, $product->name_ar, $product->name_en);

                return $product;
            });

        if ($products->isEmpty()) {
            // $message = $locale === 'ar' ? "لا توجد منتجات" : "Products Not Found";
            return response()->json([
                'message' => "No Products Found",
                'data' => [],
            ]);
        }

        // $message = $locale === 'ar' ? "كل المنتجات" : "All Products";
        return response()->json([
            'message' => "All Products",
            'data' => $products,
        ]);
    }
    ////====================================================================
    public function allCategories()
    {
        $locale = request()->header('Accept-Language', 'ar');
        $categories = Category::where('status', 'active')
            ->select('id', 'name_ar', 'name_en')
            ->get()
            ->map(function ($category) use ($locale) {
                $category->name = $locale === 'ar' ? $category->name_ar : $category->name_en;
                unset($category->name_ar, $category->name_en);
                return $category;
            });

        if ($categories->isEmpty()) {
            return response()->json([
                'message' => 'No Categories Found',
                'data' => [],
            ]);
        }

        return response()->json([
            'message' => "All Categories",
            'data' => $categories,
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name_ar' => ['required', 'string', 'min:5'],
            'name_en' => ['required', 'string', 'min:5'],
            'description_ar' => ['nullable', 'string', 'min:5'],
            'description_en' => ['nullable', 'string', 'min:5'],
            'price' => ['required', 'numeric'],
            // 'size' => ['nullable', 'string'],.
            'quantity' => ['required', 'integer'],
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:4048'],
            'category_id' => ['required', 'exists:categories,id'],
        ]);

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $data['image'] = $request->file('image')
                ->store('uploads/products', ['disk' => 'public']);
        }
        $data['last_quantity'] = $data['quantity'];
        $product = Product::create($data);

        if ($product) {
            // insert into last_quantity table
            $last_quantity = QuantityAdded::create(
                [
                    'product_id' => $product->id,
                    'quantity' => $data['quantity']
                ]
            );

            $product->image = $product->image ? url(Storage::url($product->image)) : null;
            return response()->json([
                'message' => "Product Created Successfully",
                'data' => $product,
            ]);
        }

        return response()->json([
            'message' => "Something Went Wrong",
            'data' => [],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json([
                'message' => "Product Not Found",
                'data' => [],
            ]);
        }

        $product->image = $product->image ? url(Storage::url($product->image)) : null;

        return response()->json([
            'message' => "Product Details",
            'data' => $product,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'message' => "Product Not Found",
                'data' => [],
            ]);
        }

        $data = $request->validate([
            'name' => ['nullable', 'string', 'min:5'],
            'description' => ['nullable', 'string', 'min:5'],
            'price' => ['nullable', 'numeric'],
            'size' => ['nullable', 'string'],
            'quantity' => ['nullable', 'numeric'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:4048'],
            'category_id' => ['nullable', 'exists:categories,id'],
        ]);

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            $data['image'] = $request->file('image')
                ->store('uploads/products', ['disk' => 'public']);
        }

        $product->update($data);

        $product->image = $product->image ? url(Storage::url($product->image)) : null;

        return response()->json([
            'message' => "Product Updated Successfully",
            'data' => $product,
        ]);
    }
    public function updateQuantity(Request $request, string $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json([
                'message' => "Product Not Found",
                'data' => [],
            ]);
        }

        $data = $request->validate([
            'quantity' => ['required', 'numeric', 'min:0'],
        ]);

        $product->last_quantity =  $product->quantity + $data['quantity'];
        $product->quantity += $data['quantity'];
        $product->save();
        // insert into last_quantity table
        $last_quantity = QuantityAdded::create(
            [
                'product_id' => $product->id,
                'quantity' => $data['quantity']
            ]
        );



        return response()->json([
            'message' => "Product Quantity Updated Successfully",
            'data' => $product
        ]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'message' => "Product Not Found",
                'data' => [],
            ]);
        }

        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return response()->json([
            'message' => "Product Deleted Successfully",
            'data' => [],
        ]);
    }
}
