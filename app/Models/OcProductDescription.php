<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OcProductDescription extends Model
{
    protected $connection = 'opencart';
    protected $table = 'product_description';
    public $timestamps = false;

    public function product()
    {
        return $this->belongsTo(OcProduct::class, 'product_id', 'product_id');
    }
}
