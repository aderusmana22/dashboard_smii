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
        Schema::create('productions', function (Blueprint $table) {
            $table->id();
            $table->string('tr_trnbr');
            $table->string('tr_nbr');
            $table->string('tr_effdate')->nullable();
            $table->string('tr_type')->nullable();
            $table->string('tr_prod_line')->nullable();
            $table->string('tr_part')->nullable();
            $table->string('pt_desc1')->nullable();
            $table->integer('tr_qty_loc')->nullable();
            $table->decimal('Weight_in_KG', 10, 2)->nullable();
            $table->string('Line')->nullable();
            $table->string('pt_draw')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productions');
    }
};
