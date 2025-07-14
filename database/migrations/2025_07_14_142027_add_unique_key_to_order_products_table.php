<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::table('order_products', function (Blueprint $table) {
            $table->unique(['order_id', 'product_id'], 'order_product_unique');
        });

    }

   
    public function down(): void
    {
        Schema::table('order_products', function (Blueprint $table) {
            $table->dropUnique('order_product_unique');
        });
    }
};
