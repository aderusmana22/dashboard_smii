<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tanks', function (Blueprint $table) {
            $table->id();
            $table->string('tank_code')->unique();
            $table->string('oil_code');
            $table->string('description');
            $table->unsignedBigInteger('capacity_kg');
            $table->string('color_hex', 7)->comment('Hex color for charts');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tanks');
    }
};