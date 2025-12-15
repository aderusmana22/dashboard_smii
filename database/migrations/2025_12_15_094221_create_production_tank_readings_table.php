<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_tank_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_tank_id')->constrained()->onDelete('cascade');
            $table->date('reading_date');
            $table->unsignedInteger('current_value_kg');
            $table->string('status'); // e.g., Holding, Process, Cooling
            $table->timestamps();

            $table->unique(['production_tank_id', 'reading_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_tank_readings');
    }
};