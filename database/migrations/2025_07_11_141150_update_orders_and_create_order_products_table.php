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
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['product_sku', 'quantity', 'image', 'name', 'stock_quantity']);
        });

        Schema::create('order_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->integer('quantity')->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('product_sku');
            $table->unsignedInteger('quantity');
            $table->string('image')->nullable();
            $table->string('name')->nullable();
            $table->integer('stock_quantity')->nullable();
        });

        // Удаляем таблицу order_products
        Schema::dropIfExists('order_products');
    }
};
