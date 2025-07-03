<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OcOrderProduct extends Model
{
    protected $connection = 'opencart';
    protected $table = 'order_product';
    protected $primaryKey = 'order_product_id';
    public $timestamps = false;

    public function order()
    {
        return $this->belongsTo(OcOrder::class, 'order_id', 'order_id');
    }


}
