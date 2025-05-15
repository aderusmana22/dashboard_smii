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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('tr_trnbr');
            $table->string('tr_addr')->nullable();
            $table->string('tr_effdate')->nullable();
            $table->decimal('tr_ton', 8, 3)->nullable();
            $table->string('cm_region')->nullable();
            $table->string('cm_rmks')->nullable();
            $table->string('code_cmmt')->nullable();
            $table->string('margin')->nullable();
            $table->string('value')->nullable();
            $table->string('pt_desc1')->nullable();
            $table->string('pt_prod_line')->nullable();
            $table->string('pl_desc')->nullable();
            $table->string('ad_name')->nullable();
            $table->string('tr_slspsn')->nullable();
            $table->string('sales_name')->nullable();
            $table->string('pt_part')->nullable();
            $table->string('pt_draw')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
