<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulkUserDetail extends Model
{
    use HasFactory;
    protected $table = 'bulk_user_details';
    public $primaryKey = 'id';
    public $timeStamps = true;
}
