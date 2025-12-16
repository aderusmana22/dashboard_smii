<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fat_blend_tank_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fat_blend_tank_id')->constrained()->onDelete('cascade');
            $table->date('reading_date');
            $table->unsignedInteger('current_value_kg');
            $table->timestamps();

            $table->unique(['fat_blend_tank_id', 'reading_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fat_blend_tank_readings');
    }
};