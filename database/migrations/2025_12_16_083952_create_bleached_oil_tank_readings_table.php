<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bleached_oil_tank_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bleached_oil_tank_id')->constrained()->onDelete('cascade');
            $table->date('reading_date');
            $table->string('oil_code')->nullable();
            $table->string('description')->nullable();
            $table->unsignedInteger('current_value_kg')->default(0);
            $table->timestamps();

            $table->unique(
                ['bleached_oil_tank_id', 'reading_date'],
                'botr_tank_date_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bleached_oil_tank_readings');
    }
};