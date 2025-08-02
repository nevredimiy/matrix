<?php  

namespace Database\Seeders;  

use Illuminate\Database\Seeder;  
use Illuminate\Support\Facades\DB;  

class OrderStatusesTableSeeder extends Seeder  
{  
    public function run()  
    {  
        DB::table('order_statuses')->insert([  
            [  
                'id' => 155,  
                'name' => 'Нове',  
                'store_id' => 1,  
                'identifier' => 1,  
                'is_active' => 1,  
                'created_at' => '2025-07-13 07:03:35',  
                'updated_at' => '2025-07-13 07:08:46',  
            ],  
            [  
                'id' => 156,  
                'name' => 'В обробці',  
                'store_id' => 1,  
                'identifier' => 2,  
                'is_active' => 1,  
                'created_at' => '2025-07-13 07:03:35',  
                'updated_at' => '2025-07-13 07:08:46',  
            ],  
            [  
                'id' => 157,  
                'name' => 'Доставлене',  
                'store_id' => 1,  
                'identifier' => 3,  
                'is_active' => 0,  
                'created_at' => '2025-07-13 07:03:35',  
                'updated_at' => '2025-07-13 07:25:29',  
            ],  
            [  
                'id' => 158,  
                'name' => 'Під замовлення',  
                'store_id' => 1,  
                'identifier' => 7,  
                'is_active' => 1,  
                'created_at' => '2025-07-13 07:03:35',  
                'updated_at' => '2025-07-13 07:08:46',  
            ],  
            [  
                'id' => 159,  
                'name' => 'Замовлення оплачене',  
                'store_id' => 1,  
                'identifier' => 5,  
                'is_active' => 1,  
                'created_at' => '2025-07-13 07:03:35',  
                'updated_at' => '2025-07-13 07:08:46',  
            ],  
            [  
                'id' => 160,  
                'name' => 'Фіскалізовано',  
                'store_id' => 1,  
                'identifier' => 8,  
                'is_active' => 1,  
                'created_at' => '2025-07-13 07:03:35',  
                'updated_at' => '2025-07-13 07:08:46',  
            ],  
            [  
                'id' => 161,  
                'name' => 'Отмена и аннулирование',  
                'store_id' => 1,  
                'identifier' => 9,  
                'is_active' => 0,  
                'created_at' => '2025-07-13 07:03:35',  
                'updated_at' => '2025-07-13 07:25:51',  
            ],  
            [  
                'id' => 162,  
                'name' => 'Неудавшийся',  
                'store_id' => 1,  
                'identifier' => 10,  
                'is_active' => 0,  
                'created_at' => '2025-07-13 07:03:35',  
                'updated_at' => '2025-07-13 07:25:55',  
            ],  
            [  
                'id' => 163,  
                'name' => 'Возмещенный',  
                'store_id' => 1,  
                'identifier' => 11,  
                'is_active' => 1,  
                'created_at' => '2025-07-13 07:03:35',  
                'updated_at' => '2025-07-13 07:08:16',  
            ],
            
        ]);
    }
}
