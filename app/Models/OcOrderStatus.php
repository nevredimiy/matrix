<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OcOrderStatus extends Model
{
    protected $connection = 'opencart';
    protected $table = 'order_status';
    protected $primaryKey = 'order_status_id';
    public $timestamps = false;
}
