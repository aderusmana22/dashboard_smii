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
        Schema::create('biaya_perawatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_kecelakaan_id')->constrained('laporan_kecelakaans')->onDelete('cascade');
            $table->decimal('harga', 15, 2);
            $table->string('kategori');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biaya_perawatans');
    }
};