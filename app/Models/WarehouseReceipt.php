<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseReceipt extends Model
{
    protected $fillable = [
        'factory_order_delivery_id',
        'user_id',
        'received_at',
        'quantity_received',
        'status',
        'notes',
        'warehouse_location'
    ];

    protected $casts = [
        'received_at' => 'datetime',
    ];

    public function factoryOrderDelivery(): BelongsTo
    {
        return $this->belongsTo(FactoryOrderDelivery::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->hasOneThrough(
            Product::class,
            FactoryOrderDelivery::class,
            'id', // Foreign key on factory_order_deliveries table
            'id', // Foreign key on products table
            'factory_order_delivery_id', // Local key on warehouse_receipts table
            'id' // Local key on factory_order_deliveries table
        )->via('factoryOrderDelivery');
    }

    public function scopeReceived($query)
    {
        return $query->where('status', 'received');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeDamaged($query)
    {
        return $query->where('status', 'damaged');
    }
}