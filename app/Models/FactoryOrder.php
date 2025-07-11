<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\FactoryOrderItem;

class FactoryOrder extends Model
{
    protected $fillable = [
        'factory_id',
        'status'
    ];

    public function factory()
    {
        return $this->belongsTo(Factory::class);
    }

    public function items()
    {
        return $this->hasMany(FactoryOrderItem::class);
    }
}
