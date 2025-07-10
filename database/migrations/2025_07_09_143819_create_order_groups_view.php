<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
         DB::statement("
            CREATE OR REPLACE VIEW order_groups_view AS
            SELECT
                MIN(id) AS id,                  -- нужен, чтобы Filament знал первичный ключ
                order_number,
                store_id
            FROM orders
            GROUP BY order_number, store_id
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS order_groups_view");
    }
};
