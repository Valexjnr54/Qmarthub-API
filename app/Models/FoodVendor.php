<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodVendor extends Model
{
    use HasFactory;
    protected $table = 'food_vendors';
    public $primaryKey = 'id';
    public $timeStamps = true;

    public function food()
    {
        return $this->hasMany('App\Models\Food');
    }
}
