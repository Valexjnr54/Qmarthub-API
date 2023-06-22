<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDrink extends Model
{
    use HasFactory;
    protected $table = 'order_drinks';
    public $primaryKey = 'id';
    public $timeStamps = true;
}
