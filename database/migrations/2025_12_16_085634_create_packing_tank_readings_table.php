<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packing_tank_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packing_tank_id')->constrained()->onDelete('cascade');
            $table->date('reading_date');
            $table->unsignedInteger('current_value_kg');
            // Status: READY, FILLING, STANDBY, MAINTENANCE
            $table->string('status');
            $table->timestamps();

            $table->unique(['packing_tank_id', 'reading_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packing_tank_readings');
    }
};