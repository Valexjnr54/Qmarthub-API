<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerReferral extends Model
{
    use HasFactory;
    protected $table = 'customer_referrals';
    public $primaryKey = 'id';
    public $timeStamps = true;
    protected $fillable = [
        'customer_id', 'customer_referral_id','name','email','phone',
    ];
}
