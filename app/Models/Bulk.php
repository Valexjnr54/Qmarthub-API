<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bulk extends Model
{
    use HasFactory;
    protected $fillable = ['id'];
    protected $table = 'bulks';
    public $primaryKey = 'id';
    public $timeStamps = true;
}
