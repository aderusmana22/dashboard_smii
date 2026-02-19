<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('oil_shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Shift 1, Shift 2, Shift 3
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed Data Default
        DB::table('oil_shifts')->insert([
            ['name' => 'Shift 1', 'start_time' => '06:00:00', 'end_time' => '14:00:00'],
            ['name' => 'Shift 2', 'start_time' => '14:00:00', 'end_time' => '22:00:00'],
            ['name' => 'Shift 3', 'start_time' => '22:00:00', 'end_time' => '06:00:00'],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('oil_shifts');
    }
};