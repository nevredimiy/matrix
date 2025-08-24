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
        Schema::create('warehouse_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factory_order_delivery_id')->constrained('factory_order_deliveries')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('received_at')->useCurrent();
            $table->unsignedInteger('quantity_received');
            $table->enum('status', ['pending', 'received', 'damaged', 'partial'])->default('pending');
            $table->text('notes')->nullable();
            $table->string('warehouse_location')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_receipts');
    }
};
