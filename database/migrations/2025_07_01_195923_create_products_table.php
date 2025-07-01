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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Название продукта
            $table->string('sku')->unique(); // Артикул
            $table->integer('stock_quantity')->default(0); // Количество на складе
            $table->integer('desired_stock_quantity')->default(0); // Желаемое количество на складе
            $table->integer('ordered_for_production')->default(0); // Заказано для изготовления
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
