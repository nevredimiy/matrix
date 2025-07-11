<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OcOrderStatus extends Model
{
    protected $connection = 'opencart';
    protected $table = 'order';
    protected $primaryKey = 'order_id';
    public $timestamps = false;
}
