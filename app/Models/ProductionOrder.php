<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionOrder extends Model
{
    protected $fillable = [
        'profuct_id',
        'quantity',
        'product_facility_id'
    ];

    public function product()
    {
        $this->belongsTo(Product::class);
    }

    public function productionFacility()
    {
        $this->belongsTo(ProductionFacility::class);
    }
}
