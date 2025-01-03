<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvailableAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_ar',
        'name_en',
        'longitude',
        'latitude',
        'coordinates',
    ];
}
