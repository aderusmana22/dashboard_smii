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
        Schema::connection('mysql_oil')->create('master_oil_tank', function (Blueprint $table) {
            $table->id();
            $table->string('tank_name')->unique();
            $table->string('tank_description')->nullable();
            $table->integer('capacity')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_oil')->dropIfExists('master_oil_tank');
    }
};
