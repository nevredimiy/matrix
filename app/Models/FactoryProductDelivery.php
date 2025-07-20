<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FactoryProductDelivery extends Model
{
    protected $fillable = [
        'product_id',
        'quantity',
        'delivered_at',
        'delivered_by',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }
}
