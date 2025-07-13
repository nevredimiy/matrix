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
        // 1. Удаляем внешний ключ orders → order_statuses.identifier
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['order_status_identifier']); // это имя Laravel создаёт по умолчанию, если явно не указано
        });

        // 2. Удаляем уникальный индекс
        Schema::table('order_statuses', function (Blueprint $table) {
            $table->dropUnique('order_statuses_identifier_unique');
        });

        // 3. Добавляем составной уникальный индекс
        Schema::table('order_statuses', function (Blueprint $table) {
            $table->unique(['store_id', 'identifier'], 'store_identifier_unique');
        });

        // 4. Добавляем новый внешний ключ
        Schema::table('orders', function (Blueprint $table) {
            $table->foreign(['store_id', 'order_status_identifier'])
                ->references(['store_id', 'identifier'])
                ->on('order_statuses')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['store_id', 'order_status_identifier']);
        });

        Schema::table('order_statuses', function (Blueprint $table) {
            $table->dropUnique('store_identifier_unique');
            $table->unique('identifier', 'order_statuses_identifier_unique');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('order_status_identifier')
                ->references('identifier')
                ->on('order_statuses');
        });
    }
};
