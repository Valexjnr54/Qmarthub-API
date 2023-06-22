<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryProduct extends Model
{
    use HasFactory;
    protected $fillable = ['id'];
    protected $table = 'category_product';
    public $primaryKey = 'id';
    public $timeStamps = true;
}
