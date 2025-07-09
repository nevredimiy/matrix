<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArhivedOrder extends Model
{
    protected $fillable = [
        'order_number',
        'product_sku',
        'quantity',
        'store_id',
        'status'
    ];
}
