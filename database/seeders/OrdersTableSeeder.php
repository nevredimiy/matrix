<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrdersTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('orders')->insert([
            [
                'id' => 7613,
                'order_number' => '18174',
                'store_id' => 1,
                'status' => 'in_progress',
                'order_status_identifier' => 2,
                'order_date' => '2023-05-24 03:15:46',
                'created_at' => '2025-07-14 12:03:05',
                'updated_at' => '2025-07-30 14:00:01',
            ],
            [
                'id' => 7704,
                'order_number' => '17791',
                'store_id' => 1,
                'status' => 'in_progress',
                'order_status_identifier' => 2,
                'order_date' => '2023-04-09 05:06:43',
                'created_at' => '2025-07-16 07:47:04',
                'updated_at' => '2025-07-30 14:00:01',
            ],
            [
                'id' => 7923,
                'order_number' => '19032',
                'store_id' => 1,
                'status' => 'відкритий',
                'order_status_identifier' => 7,
                'order_date' => '2023-09-01 11:06:55',
                'created_at' => '2025-07-16 07:47:04',
                'updated_at' => '2025-07-30 14:00:01',
            ],
            [
                'id' => 8908,
                'order_number' => '22705',
                'store_id' => 1,
                'status' => 'відкритий',
                'order_status_identifier' => 7,
                'order_date' => '2024-05-09 12:22:06',
                'created_at' => '2025-07-18 07:14:04',
                'updated_at' => '2025-07-30 14:00:01',
            ],
            [
                'id' => 8909,
                'order_number' => '26673',
                'store_id' => 1,
                'status' => 'in_progress',
                'order_status_identifier' => 7,
                'order_date' => '2025-01-15 11:05:24',
                'created_at' => '2025-07-18 07:14:04',
                'updated_at' => '2025-07-30 14:00:01',
            ],
            [
                'id' => 8910,
                'order_number' => '22603',
                'store_id' => 1,
                'status' => 'відкритий',
                'order_status_identifier' => 11,
                'order_date' => '2024-05-01 10:22:55',
                'created_at' => '2025-07-18 07:14:04',
                'updated_at' => '2025-07-30 14:00:01',
            ],
            [
                'id' => 9598,
                'order_number' => '29098',
                'store_id' => 1,
                'status' => 'in_progress',
                'order_status_identifier' => 5,
                'order_date' => '2025-06-19 11:47:17',
                'created_at' => '2025-07-19 04:52:10',
                'updated_at' => '2025-07-30 14:00:01',
            ],
        ]);
    }
}