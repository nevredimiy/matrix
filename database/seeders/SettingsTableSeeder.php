<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('settings')->insert([
            [
                'id' => 1,
                'key' => 'max_days_per_form',
                'value' => '7',
                'created_at' => '2025-07-31 07:53:17',
                'updated_at' => '2025-07-31 07:53:17',
            ],
        ]);
    }
}