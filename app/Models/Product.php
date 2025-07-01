<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillabel = [
        'name',
        'stock_quantity',
        'desired_stock_quantity',
        'order_for_quantity'
    ];
}
