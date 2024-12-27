<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
    protected $fillable = ['name_ar',
        'name_en', 'price', 'description_ar', 'description_en',
    'image', 'category_id','status','size','quantity','last_quantity'];
   protected $hidden = ['created_at','updated_at'];
//    public function getImageUrlAttribute()
//     {
//         return url(Storage::url($this->image));
//     }
}
