<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionFacility extends Model
{
    protected $fillable = [
        'name',
        'location'
    ];

    public function productionOrders()
    {
        $this->hasMany(ProductionOrder::class, 'production_facility_id', 'id');
    }

     public function productionDeliveries()
    {
        $this->hasMany(ProductionDelivery::class, 'production_delivery_id', 'id');
    }
}
