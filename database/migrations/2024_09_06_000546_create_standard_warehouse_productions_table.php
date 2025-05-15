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
        Schema::create('standard_warehouse_productions', function (Blueprint $table) {
            $table->id();
            $table->string('location')->nullable();
            $table->string('rack')->nullable();
            $table->string('temperature')->nullable();
            $table->string('pallet_rack')->nullable();
            $table->string('estimated_tonnage')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('standard_warehouse_productions');
    }
};
