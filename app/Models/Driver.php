<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Driver extends Model
{
    use  HasApiTokens,HasFactory;
    protected $fillable = [ 'name','password', 'phone_number','address' ,'license_number' ,'vehicle_number','status' , 'image','vehicle_license'];

     protected $hidden = ['created_at', 'updated_at'];
}
