<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OcProduct extends Model
{
    protected $connection = 'opencart';
    protected $table = 'oc_product';
    protected $primaryKey = 'product_id';
    public $timestamps = false;

    public function orderProducts()
    {
        return $this->hasMany(OcOrderProduct::class, 'product_id', 'product_id');
    }

    public function description()
    {
        return $this->hasOne(OcProductDescription::class, 'product_id', 'product_id')->where('language_id', 1); // или нужный язык
    }
}
