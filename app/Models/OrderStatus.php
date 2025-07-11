<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    protected $fillable = [
        'name',
        'store_id',
        'identifier',
        'is_active'
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
