<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FactoryModelCount extends Model
{
    protected $fillable = [
        'product_id',
        'factory1_model_count',
        'factory2_model_count',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
