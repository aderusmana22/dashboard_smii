<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_ppic_reports', function (Blueprint $table) {
            $table->id();
            $table->string('item_number');
            $table->string('description')->nullable();
            $table->integer('month');
            $table->integer('year');
            $table->decimal('inventory_qty', 15, 2)->default(0);
            $table->decimal('dispatch_qty', 15, 2)->default(0);
            $table->decimal('allocated_qty', 15, 2)->default(0);
            $table->decimal('so_outstanding', 15, 2)->default(0);
            $table->decimal('mps_qty', 15, 2)->default(0);
            // Kolom forecast bisa null jika tidak ada data dari user
            $table->decimal('forecast_unit', 15, 2)->nullable();
            $table->decimal('forecast_tonage', 15, 4)->nullable();
            $table->timestamps();

            $table->unique(['item_number', 'month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_ppic_reports');
    }
};