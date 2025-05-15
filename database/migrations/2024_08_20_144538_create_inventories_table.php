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
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->string('ld_part')->unique();
            $table->string('pt_desc1');
            $table->string('ld_status');
            $table->string('ld_qty_oh');
            $table->string('pt_um');
            $table->string('ld_date');
            $table->string('ld_loc');
            $table->string('ld_lot');
            $table->string('aging_days');
            $table->string('ld_expire');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
