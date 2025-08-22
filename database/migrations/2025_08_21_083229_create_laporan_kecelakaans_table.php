<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_kecelakaans', function (Blueprint $table) {
            $table->id();
            // Header
            $table->string('nomor_form')->unique()->nullable();
            $table->date('date');
            // Detail Insiden
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
            // Tindakan Pertolongan
            $table->string('pertolongan');
            $table->string('p3k_oleh')->nullable();
            $table->time('jam_p3k')->nullable();
            $table->string('akibat_kecelakaan');
            $table->integer('waktu_hilang')->nullable();
            // APD & Analisa
            $table->json('apd_data')->nullable();
            $table->string('sebab_utama_kategori')->nullable(); // 'A' atau 'B'
            $table->text('sebab_utama_deskripsi')->nullable();
            $table->text('analisa_masalah')->nullable();
            $table->text('tindakan_pencegahan')->nullable();
            $table->text('rekomendasi')->nullable();
            // Kolom Persetujuan (Foreign Keys ke tabel users)
            $table->foreignId('pembuat_laporan_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('manager_hse_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('manager_terkait_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('dept_head_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('gm_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_kecelakaans');
    }
};