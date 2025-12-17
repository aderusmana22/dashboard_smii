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
        // Tabel untuk data 3 lingkaran (Packaging, Coolroom 1, Coolroom 2)
        Schema::create('storage_areas', function (Blueprint $table) {
            $table->id();
            $table->string('name');                 // e.g., 'Packaging Ambient'
            $table->string('temp_range')->nullable(); // e.g., '20 - 25 C'
            $table->integer('total_pp');            // e.g., 2824
            $table->unsignedTinyInteger('occupancy_percent'); // e.g., 25
            $table->integer('actual_temp');         // e.g., 29
            $table->string('color');                // e.g., '#0041a3' atau 'blue'
            $table->timestamps();
        });

        // Tabel untuk "Ingredient Expiry Status"
        Schema::create('ingredient_expiries', function (Blueprint $table) {
            $table->id();
            $table->string('item_code');
            $table->string('description');
            $table->integer('qty');
            $table->date('expiry_date');
            $table->timestamps();
        });

        // Tabel untuk "Daily Incoming Status"
        Schema::create('incoming_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_code');
            $table->integer('jumlah');
            $table->string('satuan');
            $table->string('no_rc');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('storage_areas');
        Schema::dropIfExists('ingredient_expiries');
        Schema::dropIfExists('incoming_items');
    }
};