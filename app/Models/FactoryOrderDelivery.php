<?php

namespace App\Models;

use App\Events\DeliveryCreated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FactoryOrderDelivery extends Model
{
    protected $fillable = [
        'factory_order_item_id',
        'user_id',
        'delivered_at',
        'quantity',
        'notes'
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
    ];

    protected $dispatchesEvents = [
        'created' => DeliveryCreated::class,
    ];

    public function factoryOrderItem(): BelongsTo
    {
        return $this->belongsTo(FactoryOrderItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->hasOneThrough(
            Product::class,
            FactoryOrderItem::class,
            'id', // Foreign key on factory_order_items table
            'id', // Foreign key on products table
            'factory_order_item_id', // Local key on factory_order_deliveries table
            'product_id' // Local key on factory_order_items table
        );
    }

    public function warehouseReceipts()
    {
        return $this->hasMany(WarehouseReceipt::class);
    }

    public function latestWarehouseReceipt()
    {
        return $this->hasOne(WarehouseReceipt::class)->latestOfMany();
    }
}
