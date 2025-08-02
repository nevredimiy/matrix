<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            [
                'id' => 1,
                'name' => 'admin',
                'email' => 'artemlitivnov@gmail.com',
                'email_verified_at' => '2025-07-28 14:03:46',
                'password' => '$2y$12$T48ojU8c0IO0ZaAM4IaHCupCS7wwP2KZbv5qanIrOJTc5V4w8GUle', // лучше хэшировать так
                'role' => 'user',
                'remember_token' => null,
                'created_at' => null,
                'updated_at' => '2025-07-22 04:01:24',
                'is_admin' => 0,
            ],
            [
                'id' => 2,
                'name' => 'admin',
                'email' => 'a@a.a',
                'email_verified_at' => '2025-07-28 14:03:42',
                'password' => '$2y$12$4yNXQAn7IPW.TNWzTqbiCOiM5afIDBjdqlVA0EE/FbEQ7xxh6R1j2',
                'role' => 'admin',
                'remember_token' => 'sQxSxxw5ZGB1OLm8ZxVCAEhcksG0JUPz2tHmuGrAjbg7QEblfAF0kjh71hek',
                'created_at' => null,
                'updated_at' => null,
                'is_admin' => 0,
            ],
            [
                'id' => 3,
                'name' => 'superAdmin',
                'email' => 'admin@admin.com',
                'email_verified_at' => '2025-07-28 14:03:33',
                'password' => '$2y$12$V23RvXrGTSurLro9SpCFw.d3S1CHa8yvgylM2ISHmGxqaGMZTGmGC',
                'role' => 'admin',
                'remember_token' => null,
                'created_at' => '2025-07-22 05:51:45',
                'updated_at' => '2025-07-22 05:51:45',
                'is_admin' => 0,
            ],
            [
                'id' => 4,
                'name' => 'factory1',
                'email' => 'factory1@dinara.com',
                'email_verified_at' => '2025-07-28 11:05:05',
                'password' => '$2y$12$M3jVslclXW9.X.8AgkZK6O5TQh.ac4vTlTUolJnRqV32k/fPyKuiS',
                'role' => 'user',
                'remember_token' => null,
                'created_at' => '2025-07-28 11:05:43',
                'updated_at' => '2025-07-28 11:05:43',
                'is_admin' => 0,
            ],
            [
                'id' => 5,
                'name' => 'factory2',
                'email' => 'factory2@dinara.com',
                'email_verified_at' => '2025-07-28 11:05:43',
                'password' => '$2y$12$0krHOuj8KnQhpo4aBJtk4e.EsqOu2jQ4zzPTWD728BwIjMJK0OGq2',
                'role' => 'user',
                'remember_token' => null,
                'created_at' => '2025-07-28 11:05:57',
                'updated_at' => '2025-07-28 11:05:57',
                'is_admin' => 0,
            ],
            [
                'id' => 6,
                'name' => 'warehouse',
                'email' => 'warehouse@dinara.com',
                'email_verified_at' => '2025-07-28 11:12:48',
                'password' => '$2y$12$RLpVtDTtRMA63sz1bvwluuSZyGug1B5GPYo.eyKzz9CoVabkhTcXa',
                'role' => 'user',
                'remember_token' => null,
                'created_at' => '2025-07-28 11:13:10',
                'updated_at' => '2025-07-28 11:13:10',
                'is_admin' => 0,
            ],
        ]);
    }
}