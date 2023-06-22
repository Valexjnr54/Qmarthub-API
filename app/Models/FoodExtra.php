<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodExtra extends Model
{
    use HasFactory;
    protected $fillable = ['id'];
    protected $table = 'food_extra';
    public $primaryKey = 'id';
    public $timeStamps = true;
}
