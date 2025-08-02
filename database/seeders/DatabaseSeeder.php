<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $this->call(UsersTableSeeder::class);
        $this->call(StoresTableSeeder::class);
        $this->call(FactoriesTableSeeder::class);
        $this->call(OrderStatusesTableSeeder::class);
        $this->call(ProductsTableSeeder::class);
        $this->call(SettingsTableSeeder::class);
        $this->call(OrdersTableSeeder::class);
        $this->call(OrderProductsTableSeeder::class);
    }
}
