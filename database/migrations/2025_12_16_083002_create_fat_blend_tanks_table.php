<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fat_blend_tanks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('capacity_kg');
            // Kolom untuk 'Source Info': MANUAL, PLC, WAITING
            $table->string('source_type')->default('WAITING');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fat_blend_tanks');
    }
};