<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderProductsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('order_products')->insert([
            [
                'id' => 4330,
                'order_id' => 7613,
                'product_id' => 1319,
                'quantity' => 1,
                'created_at' => '2025-07-14 12:03:05',
                'updated_at' => '2025-07-30 14:00:01',
            ],
            [
                'id' => 4708,
                'order_id' => 7704,
                'product_id' => 1319,
                'quantity' => 1,
                'created_at' => '2025-07-16 07:47:04',
                'updated_at' => '2025-07-30 14:00:01',
            ],
            [
                'id' => 5278,
                'order_id' => 7923,
                'product_id' => 1319,
                'quantity' => 1,
                'created_at' => '2025-07-16 07:47:04',
                'updated_at' => '2025-07-30 14:00:01',
            ],
            [
                'id' => 7899,
                'order_id' => 8908,
                'product_id' => 1379,
                'quantity' => 1,
                'created_at' => '2025-07-18 07:14:04',
                'updated_at' => '2025-07-30 14:00:01',
            ],
            [
                'id' => 7900,
                'order_id' => 8909,
                'product_id' => 1379,
                'quantity' => 1,
                'created_at' => '2025-07-18 07:14:04',
                'updated_at' => '2025-07-30 14:00:01',
            ],
            [
                'id' => 7901,
                'order_id' => 8910,
                'product_id' => 1379,
                'quantity' => 1,
                'created_at' => '2025-07-18 07:14:04',
                'updated_at' => '2025-07-30 14:00:01',
            ],
            [
                'id' => 8589,
                'order_id' => 9598,
                'product_id' => 1835,
                'quantity' => 1,
                'created_at' => '2025-07-19 04:52:10',
                'updated_at' => '2025-07-30 14:00:01',
            ],
        ]);
    }
}