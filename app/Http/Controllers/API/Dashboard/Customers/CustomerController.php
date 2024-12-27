<?php

namespace App\Http\Controllers\API\Dashboard\Customers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    //
    public function allCustomers()
    {
        $customers =  User::all();
        if($customers->count() > 0) {
            return response()->json([
              'message' =>'All customers',
                'data' => $customers
            ]);
        }
        return response()->json([

           'message' => 'No customer found',
           'data' => []
        ]);
    }
    public function singleCustomer($id)
    {

        $customer = User::with('orders.items.product')->find($id);

        if($customer) {
            return response()->json([
              'message' =>'customer data',
                'data' => $customer
            ]);
        }
        return response()->json([

           'message' => 'customer not found',
           'data' => []
        ]);
    }
}
