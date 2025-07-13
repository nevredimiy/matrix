<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Order extends Model
{
    protected $fillable = [
        'order_number',
        'store_id',
        'status',
        'order_status_identifier',
        'order_date',
    ];

    public function store():BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function products()
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function pivotProducts()
    {
        return $this->belongsToMany(Product::class, 'order_products')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class);
    }

    // public function orderStatus()
    // {
    //     return $this->belongsTo(OrderStatus::class, 'order_status_identifier', 'identifier' );
    // }



    public function orderStatus()
    {
        return $this->hasOne(OrderStatus::class, 'identifier', 'order_status_identifier')
            ->whereColumn('order_statuses.store_id', 'orders.store_id');
    }



}
