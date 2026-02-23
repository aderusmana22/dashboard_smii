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
        Schema::table('oil_stock_utility_gas_readings', function (Blueprint $table) {
            // Menambahkan kolom shift setelah kolom reading_date. 
            // Default 1 agar data lama yang sudah ada tidak error.
            $table->integer('shift')->default(1)->after('reading_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('oil_stock_utility_gas_readings', function (Blueprint $table) {
            $table->dropColumn('shift');
        });
    }
};