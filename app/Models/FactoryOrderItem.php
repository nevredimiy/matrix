<?php

namespace App\Models;

use App\Models\FactoryOrder;
use Illuminate\Database\Eloquent\Model;

class FactoryOrderItem extends Model
{
    protected $fillable = [
        'factory_order_id',
        'product_id',
        'quantity_ordered',
        'quantity_delivered'
    ];

    public function factoryOrder()
    {
        return $this->belongsTo(FactoryOrder::class, 'factory_order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function factoryOrderDelivery()
    {
        return $this->hasMany(FactoryOrderDelivery::class, 'factory_order_item_id', 'id');
    }

}
