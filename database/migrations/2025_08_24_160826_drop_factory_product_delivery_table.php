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
        Schema::table('factory_product_deliveries', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });
        
        Schema::dropIfExists('factory_product_deliveries');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('factory_product_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->unsignedInteger('quantity'); // общее количество отгруженного товара
            $table->timestamp('delivered_at')->nullable(); // дата отгрузки
            $table->string('delivered_by')->nullable();            
            $table->timestamps();
        });
    }
};
