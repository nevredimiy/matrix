<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Order extends Model
{
    protected $fillable = [
        'order_number',
        'product_sku',
        'quantity',
        'store_id',
        'status',
        'name',
        'image',
        'order_date',
        'stock_quantity'

    ];

    public function store():BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function products()
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function orderStatus()
    {
        return $this->belongsTo(OrderStatus::class, 'order_status_identifier', 'identifier' );
    }

}
