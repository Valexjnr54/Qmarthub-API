<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;
    protected $table = 'brands';
    public $primaryKey = 'id';
    public $timeStamps = true;

    public function products()
    {
        return $this->belongsToMany('App\Models\Product');
    }
}
