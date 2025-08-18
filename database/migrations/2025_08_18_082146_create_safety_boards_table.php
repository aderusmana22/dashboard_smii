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
        Schema::create('safety_boards', function (Blueprint $table) {
            $table->id();
            $table->date('last_accident_date')->nullable()->comment('Tanggal kecelakaan kerja terakhir');
            $table->unsignedInteger('record_days_without_accident')->default(0)->comment('Rekor hari terbaik tanpa kecelakaan');
            $table->text('marquee_text')->nullable()->comment('Teks berjalan di bagian bawah dashboard, pisahkan dengan ***');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('safety_boards');
    }
};