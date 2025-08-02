<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FactoriesTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('factories')->insert([
            [
                'id' => 1,
                'name' => 'factory 1',
                'address' => null,
                'created_at' => '2025-07-11 09:53:29',
                'updated_at' => '2025-07-11 09:53:29',
            ],
            [
                'id' => 2,
                'name' => 'factory 2 ',
                'address' => null,
                'created_at' => '2025-07-11 09:53:39',
                'updated_at' => '2025-07-11 09:53:39',
            ],
        ]);
    }
}