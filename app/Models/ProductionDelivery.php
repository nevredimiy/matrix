<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionDelivery extends Model
{
    protected $fillable = [
        'production_order_id',
        'production_facility_id',
        'delivered_quantity',
        'delivered_at'
    ];

    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function productionFacility()
    {
        return $this->belongsTo(ProductionFacility::class);
    }
}
