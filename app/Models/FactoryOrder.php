<?php

namespace App\Models;

use App\Events\FactoryOrderCreated;
use App\Events\FactoryOrderStatusChanged;
use Illuminate\Database\Eloquent\Model;
use App\Models\FactoryOrderItem;

class FactoryOrder extends Model
{
    protected $fillable = [
        'factory_id',
        'order_id',
        'order_number',
        'status'
    ];

    protected $dispatchesEvents = [
        'created' => FactoryOrderCreated::class,
    ];

    protected static function booted()
    {
        static::updating(function ($factoryOrder) {
            if ($factoryOrder->isDirty('status')) {
                $oldStatus = $factoryOrder->getOriginal('status');
                event(new FactoryOrderStatusChanged($factoryOrder, $oldStatus));
            }
        });
    }

    public function factory()
    {
        return $this->belongsTo(Factory::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function items()
    {
        return $this->hasMany(FactoryOrderItem::class);
    }
}
