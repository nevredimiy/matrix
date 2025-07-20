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
         Schema::create('factory_product_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->unsignedInteger('quantity'); // общее количество отгруженного товара
            $table->timestamp('delivered_at')->nullable(); // дата отгрузки
            // $table->foreignId('delivered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('delivered_by')->nullable();
            $table->foreign('delivered_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('factory_product_deliveries');
    }
};
