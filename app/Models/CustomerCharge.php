<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerCharge extends Model
{
    use HasFactory;
    protected $table = 'customer_charges';
    public $primaryKey = 'id';
    public $timeStamps = true;
    protected $fillable = [
        'customer_id', 'reference','delivery_fee','service_charge','payingfor',
    ];
}
