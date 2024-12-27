<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    // protected $fillable = ['user_id', 'product_id', 'quantity', 'status'];
    protected $fillable = [
        'user_id',
        'address_id',
        'region_id',
        'driver_id',
        'total_price',
        'delivery_date',
        'delivery_time',
        'delivery_charge',
        'payment_methode',
        'payment_status',
        'order_status',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function productss()
    {
        return $this->belongsToMany(
            Product::class,
            'order_items',
            'order_id',
            'product_id'
        );
    }
    public function items()
    {
        return $this->hasMany(OrderItems::class, 'order_id');
    }

    public function products()
    {
        return $this->hasManyThrough(Product::class, OrderItems::class, 'order_id', 'id', 'id', 'product_id');
    }


    public function address()
    {
        return $this->belongsTo(Address::class);
    }
}
