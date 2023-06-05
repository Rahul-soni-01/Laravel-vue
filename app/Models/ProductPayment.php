<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPayment extends Model
{
    use HasFactory;

    protected $table = 'product_payment';

    protected $fillable = [
        'user_id',
        'product_id',
        'status',
        'payment_date',
        'payment_price',
    ];
}
