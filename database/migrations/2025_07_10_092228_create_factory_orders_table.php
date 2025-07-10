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

        Schema::create('factory_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factory_id')->constrained('factories')->cascadeOnDelete();
            $table->string('status')->default('в процессе'); // или ENUM, если хочешь строгие статусы
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('factory_orders');
    }
};
