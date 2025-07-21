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
            $table->unsignedBigInteger('factory_id')->nullable()->after('product_id');
        });


        Schema::table('factory_product_deliveries', function (Blueprint $table) {
            $table->foreign('factory_id')->references('id')->on('factories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('factory_product_deliveries', function (Blueprint $table) {
            $table->dropForeign(['factory_id']);
            $table->dropColumn('factory_id');
        });
    }

};
