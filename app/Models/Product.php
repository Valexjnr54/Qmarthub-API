<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $table = 'products';
    public $primaryKey = 'id';
    public $timeStamps = true;

    public function category()
    {
        return $this->belongsToMany('App\Models\Category', 'category_product' ,'product_id', 'category_id');
    }

    public function brand()
    {
        return $this->belongsTo('App\Models\Brand');
    }
}
