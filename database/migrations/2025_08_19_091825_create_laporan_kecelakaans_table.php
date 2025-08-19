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
        Schema::create('laporan_kecelakaans', function (Blueprint $table) {
            $table->id();

            // Informasi Umum
            $table->string('nomor_form')->unique()->nullable();
            $table->date('date');

            // Detail Insiden & Dampak
            $table->string('kategori_kecelakaan');
            $table->string('kategori_dampak');
            $table->dateTime('waktu_kecelakaan');
            $table->string('lokasi_kecelakaan');
            $table->string('tipe_kecelakaan')->nullable();
            $table->string('bagian_terluka')->nullable();
            $table->text('uraian_kejadian');

            // Data Korban
            $table->string('nama_korban');
            $table->string('nik')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('usia')->nullable();
            $table->date('tanggal_masuk')->nullable();
            $table->string('masa_kerja')->nullable();
            $table->string('jabatan')->nullable();
            $table->string('departemen')->nullable();

            // Tindakan Pertolongan & Akibat
            $table->string('pertolongan');
            $table->string('p3k_oleh')->nullable();
            $table->time('jam_p3k')->nullable();
            $table->string('akibat_kecelakaan');
            $table->integer('waktu_hilang')->nullable();

            // APD & Analisa (Disimpan sebagai JSON agar fleksibel)
            $table->json('apd_data')->nullable();
            $table->string('sebab_utama_kategori')->nullable(); // A atau B
            $table->text('sebab_utama_deskripsi')->nullable();
            $table->text('analisa_masalah')->nullable();

            // Tindak Lanjut
            $table->text('tindakan_pencegahan')->nullable();
            $table->text('rekomendasi')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_kecelakaans');
    }
};