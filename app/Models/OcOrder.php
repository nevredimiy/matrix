<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OcOrder extends Model
{
    protected $connection = 'opencart';
    protected $table = 'order';
    protected $primaryKey = 'order_id';
    public $timestamps = false;

    // Связь с товарами заказа
    public function products()
    {
        return $this->hasMany(OcOrderProduct::class, 'order_id', 'order_id');
    }
}
