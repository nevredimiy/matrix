<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'sku',
        'image',
        'stock_quantity', // количество 
        'desired_stock_quantity', // желаемое количество
        'order_for_production', // сколько отгружено
        'is_active',
        'product_id_oc',
        'product_id_hor',
    ];

    public function factoryModelCount()
    {
        return $this->hasOne(FactoryModelCount::class, 'product_id', 'id');
    }
    
    public function factoryOrderItem()
    {
        return $this->hasOne(FactoryOrderItem::class, 'product_id', 'id');
    }
}
