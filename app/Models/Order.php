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
        'status'
    ];

    public function store():BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
