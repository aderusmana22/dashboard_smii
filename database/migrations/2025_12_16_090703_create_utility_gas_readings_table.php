<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utility_gas_readings', function (Blueprint $table) {
            $table->id();
            $table->date('reading_date');
            // Tipe gas: HYDROGEN, NITROGEN, AMMONIA
            $table->string('gas_type');
            // Identifier untuk sub-unit, misal: 'Torpedo #04', 'Liquid Tank', 'Full Cylinders'
            $table->string('unit_name');
            // Nilai pembacaan (bisa Bar, Inch Water, atau jumlah tabung)
            $table->decimal('value', 8, 2);
            // Unit pengukuran untuk kejelasan
            $table->string('unit_measure');
            $table->timestamps();

            $table->unique(['reading_date', 'gas_type', 'unit_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('utility_gas_readings');
    }
};