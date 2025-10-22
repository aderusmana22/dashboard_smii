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
        Schema::create('forecast_imports', function (Blueprint $table) {
            $table->id();
            $table->string('item_number');
            $table->string('description')->nullable();
            $table->integer('month');
            $table->integer('year');
            $table->decimal('unit', 15, 2);
            $table->decimal('tonage', 15, 4);
            $table->timestamps();

            // Menambahkan unique constraint untuk kombinasi item_number, month, dan year
            $table->unique(['item_number', 'month', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forecast_imports');
    }
};