<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('inventory_oil_in_outs')) {
            Schema::connection('mysql_oil')->create('inventory_oil_in_outs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('tr_trnbr')->nullable()->unique(); // Dari <qdoc:trTrnbr>
                $table->string('tr_part')->nullable();            // Dari <qdoc:trPart>
                $table->string('tr_part_name')->nullable();       // Hasil lookup ke tabel Item
                $table->string('tr_addr')->nullable();            // Dari <qdoc:trAddr>
                $table->string('tr_addr_name')->nullable();       // Hasil lookup ke tabel Supplier
                $table->date('tr_date')->nullable();              // Dari <qdoc:trEffdate>
                $table->string('tr_qty_chg')->nullable();         // Dari <qdoc:trQtyLoc>
                $table->string('tr_um')->nullable();               // Dari <qdoc:trUm>
                $table->string('type')->nullable();               // Penentuan IN/OUT
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('inventory_oil_in_outs')) {
            Schema::connection('mysql_oil')->dropIfExists('inventory_oil_in_outs');
        }
    }
};
