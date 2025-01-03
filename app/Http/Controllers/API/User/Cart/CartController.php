<?php

namespace App\Http\Controllers\API\User\Cart;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{

    public function index()
    {
        $user = auth()->guard('sanctum')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized', 'data' => []], 401);
        }
        $locale = request()->header('Accept-Language', 'ar');
        $nameField = $locale === 'ar' ? 'name_ar' : 'name_en';
        $descriptionField = $locale === 'ar' ? 'description_ar' : 'description_en';

        $cartItems = DB::table('carts')
            ->join(
                'products',
                'carts.product_id',
                '=',
                'products.id'
            )
            ->where('carts.user_id', $user->id)
            ->select(
                'products.id',
                "products.$nameField as name",
                "products.$descriptionField as description",
                'products.price',
                'products.quantity',
                'products.last_quantity',
                'products.status',
                'products.image',
                'carts.quantity as quantity_in_cart',
                DB::raw('products.price * carts.quantity as total_price')
            )
            ->get();


        if ($cartItems->isEmpty()) {
            // dd('');
            return response()->json([
                'message' => 'Cart is empty',
                'data' => [],
            ]);
        }

        $totalCost = $cartItems->sum('total_price');
        $cartItemsArray = $cartItems->toArray();
        $cartItemsArray[] = ['total_cost' => $totalCost];
        $cartItems = collect($cartItemsArray);

        return response()->json([
            'message' => 'Cart Items',
            'data' => $cartItems,
        ]);
    }


    //
    public function addToCart(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer'],
        ]);

        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized', 'data' => []]);
        }
        $product = Product::find($data['product_id']);
        if ($product->quantity > 0 && $data['quantity'] <= $product->quantity) {

            $cartItem = Cart::where('user_id', $user->id)
                ->where('product_id', $data['product_id'])->first();

            if ($cartItem) {
                return response()->json([
                    'message' => 'Product Already in Cart',
                    'data' => [],
                ]);
            } else {

                Cart::create([
                    'user_id' => $user->id,
                    'product_id' => $data['product_id'],
                    'quantity' => $data['quantity']
                ]);
            }

            return response()->json([
                'message' => 'Added to Cart Successfully',
                'data' => [],
            ]);
        } // end if product quanity is not zero and quantity is less than or equal to product quanity
        return response()->json([
            'message' => 'Product Quantity is not available Available : ' . $product->quantity,
            'data' => [],
        ]);
    }

    /**
     *     "message": "SQLSTATE[42S22]: Column not found:
     * 1054 Unknown column 'id' in 'where clause'
     *  (Connection: mysql, SQL: update `carts` set `quantity` = 6,
     *  `carts`.`updated_at` = 2024-11-02 11:02:39 where `id` is null)",

     */
    //========================================================================
    public function removeItemFromCart(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id']
        ]);

        $user = auth()->guard('sanctum')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized', 'data' => []]);
        }
        $cartItem = Cart::where('user_id', $user->id)
            ->where('product_id', $data['product_id'])->first();

        if ($cartItem) {
            DB::table('carts')
                ->where('user_id', $user->id)
                ->where('product_id', $data['product_id'])
                ->delete();
            // dd($cartItem);
            return response()->json([
                'message' => 'Product Deleted Successfully',
                'data' => [],
            ]);
        } else {
            return response()->json([
                'message' => 'Product Not Found',
                'data' => [],
            ]);
        }
    }
    //========================================================================
    public function increeceQuntaty(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer'],
        ]);

        $user = auth()->guard('sanctum')->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized', 'data' => []]);
        }

        $cartItem = Cart::where('user_id', $user->id)
            ->where('product_id', $data['product_id'])->first();

        if ($cartItem) {
            $cartItem->quantity += $data['quantity'];
            $cartItem->save();
            return response()->json([
                'message' => 'Quantity Increased',
                'data' => [],
            ]);
        }
        // else
        return response()->json([
            'message' => 'Product Not Found',
            'data' => [],
        ]);
    }
    //========================================================================
    public function decreaseQuntaty(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer'],
        ]);

        $user = auth()->guard('sanctum')->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized', 'data' => []]);
        }

        $cartItem = Cart::where('user_id', $user->id)
            ->where('product_id', $data['product_id'])->first();

        if ($cartItem) {
            $cartItem->quantity = max(0, $cartItem->quantity - $data['quantity']);
            $cartItem->save();

            //  delete the item if quantity reaches zero
            if ($cartItem->quantity == 0) {
                $cartItem->delete();
            }

            return response()->json([
                'message' => 'Quantity Decreased',
                'data' => [],
            ]);
        }

        return response()->json([
            'message' => 'Product Not Found in Cart',
            'data' => [],
        ]);
    }
}
