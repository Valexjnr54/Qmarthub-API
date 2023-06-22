<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulkOrder extends Model
{
    use HasFactory;
    protected $table = 'bulk_orders';
    public $primaryKey = 'id';
    public $timeStamps = true;
    protected $fillable = [
        'product_name', 'qty','price','status','reference',
    ];
}
