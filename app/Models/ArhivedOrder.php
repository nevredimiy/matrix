<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArhivedOrder extends Model
{
    protected $fillable = [
        'order_number',
        'store_id',
        'status_order',
        'product_skues',
    ];
}
