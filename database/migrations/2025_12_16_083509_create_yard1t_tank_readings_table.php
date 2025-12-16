<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yard1t_tank_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('yard1t_tank_id')->constrained()->onDelete('cascade');
            $table->date('reading_date');
            // Nullable karena tangki bisa jadi kosong
            $table->string('oil_code')->nullable();
            $table->string('description')->nullable();
            $table->unsignedInteger('current_value_kg')->default(0);
            $table->timestamps();

            $table->unique(['yard1t_tank_id', 'reading_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yard1t_tank_readings');
    }
};