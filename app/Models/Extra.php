<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Extra extends Model
{
    use HasFactory;
    protected $table = 'extras';
    public $primaryKey = 'id';
    public $timeStamps = true;

    public function food()
    {
        return $this->belongsToMany('App\Models\Food');
    }
}
