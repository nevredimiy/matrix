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
        Schema::table('factory_order_deliveries', function (Blueprint $table) {
            $table->foreignId('user_id')->after('id')->constrained('users')->cascadeOnDelete();
            $table->text('notes')->nullable()->after('quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('factory_order_deliveries', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'notes']);
        });
    }
};
