<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    use HasFactory;
    protected $table = 'foods';
    public $primaryKey = 'id';
    public $timeStamps = true;

    public function vendor()
    {
        return $this->belongsTo('App\Models\FoodVendor',);
    }

    public function drink()
    {
        return $this->belongsToMany('App\Models\Drink', 'food_drink' ,'food_id', 'drink_id');
    }

    public function extra()
    {
        return $this->belongsToMany('App\Models\Extra', 'food_extra' ,'food_id', 'extra_id');
    }
}
