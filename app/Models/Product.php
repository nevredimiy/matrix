<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'sku',
        'stock_quantity', // количество 
        'desired_stock_quantity', // желаемое количество
        'order_for_production' // сколько отгружено
    ];
}
