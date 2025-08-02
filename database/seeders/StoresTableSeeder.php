<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StoresTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('stores')->insert([
            [
                'id' => 1,
                'name' => 'OcStore',
                'address' => null,
                'created_at' => '2025-07-09 04:36:15',
                'updated_at' => '2025-07-09 04:36:15',
            ],
            [
                'id' => 2,
                'name' => 'Horoshop',
                'address' => null,
                'created_at' => '2025-07-09 04:36:29',
                'updated_at' => '2025-07-09 04:36:29',
            ],
        ]);
    }
}