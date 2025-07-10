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
        Schema::create('factory_order_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factory_order_item_id')->constrained('factory_order_items')->cascadeOnDelete();
            $table->timestamp('delivered_at')->useCurrent();
            $table->unsignedInteger('quantity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('factory_order_deliveries');
    }
};
