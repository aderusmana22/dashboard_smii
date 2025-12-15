<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oil_stock_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tank_id')->constrained('tanks')->onDelete('cascade');
            $table->date('reading_date');
            $table->decimal('current_value_kg', 15, 2);
            $table->decimal('temperature_celsius', 5, 2)->nullable();
            $table->decimal('gauge_board_meter', 5, 2)->nullable();
            $table->timestamps();
            
            $table->unique(['tank_id', 'reading_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oil_stock_readings');
    }
};