<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodDrink extends Model
{
    use HasFactory;
    protected $fillable = ['id'];
    protected $table = 'food_drink';
    public $primaryKey = 'id';
    public $timeStamps = true;
}
