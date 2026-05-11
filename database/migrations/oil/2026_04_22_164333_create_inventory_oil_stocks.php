<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::connection('mysql_oil')->create('inventory_oil_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('ld_part')->nullable();      // Kode Barang
            $table->string('pt_desc1')->nullable();     // Deskripsi
            $table->decimal('ld_qty_oh', 15, 4)->default(0); // Quantity On Hand
            $table->string('pt_um', 10)->nullable();    // Satuan (KG)
            $table->string('pt_prod_line')->nullable(); // Production Line
             $table->string('ld_loc')->nullable()->comment('Lokasi/Nama Tangki');  // lokasi
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::connection('mysql_oil')->dropIfExists('inventory_oil_stocks');
    }
};
