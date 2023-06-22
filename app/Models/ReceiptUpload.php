<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceiptUpload extends Model
{
    use HasFactory;
    protected $table = 'receipt_uploads';
    public $primaryKey = 'id';
    public $timeStamps = true;
}
