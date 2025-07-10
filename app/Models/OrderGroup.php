<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// class OrderGroup extends Model
// {
//     protected $table = 'orders';
//     public $timestamps = false;

//     protected $primaryKey = 'order_number';
//     public $incrementing = false;

//     protected $fillable = [
//         'order_number',
//         'store_id',
//     ];

//     // Связь с товарами (все записи с этим номером заказа)
//     public function products()
//     {
//         return $this->hasMany(Order::class, 'order_number', 'order_number');
//     }

//     // Фильтрация для уникальных заказов
//     protected static function booted()
//     {
//         static::addGlobalScope('unique_orders', function ($query) {
//             $query->groupBy('order_number', 'store_id');
//         });
//     }
// }

class OrderGroup extends Model
{
    protected $table = 'order_groups_view'; // Вьюха
    public $timestamps = false;

    public function products()
    {
        return $this->hasMany(Order::class, 'order_number', 'order_number');
    }
}
