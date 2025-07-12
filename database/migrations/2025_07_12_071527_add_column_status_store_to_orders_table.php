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

         Schema::table('order_statuses', function (Blueprint $table) {
           $table->unique('identifier');
        });


        Schema::table('orders', function (Blueprint $table) {
            $table->integer('order_status_identifier')->nullable()->after('status');

            $table->foreign('order_status_identifier')
                ->references('identifier')
                ->on('order_statuses')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
       // 1. Удаляем внешний ключ и колонку в orders
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['order_status_identifier']);
            $table->dropColumn('order_status_identifier');
        });

        // 2. Только теперь удаляем уникальный индекс
        Schema::table('order_statuses', function (Blueprint $table) {
            $table->dropUnique(['identifier']);
        });
    }
};
