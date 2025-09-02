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
        Schema::table('laporan_kecelakaans', function (Blueprint $table) {
            // 1. Tambahkan kolom baru 'sebab_kecelakaan' (dari Nomor 10) setelah kolom 'apd_data'
            $table->string('sebab_kecelakaan')->nullable()->after('apd_data');

            // 2. Tambahkan kolom JSON baru untuk menyimpan sebab utama dari "Nomor 15"
            $table->json('sebab_utama')->nullable()->after('sebab_kecelakaan');

            // 3. Hapus kolom-kolom lama yang sekarang digantikan oleh kolom JSON
            // Pastikan Anda sudah mem-backup data dari kolom ini jika diperlukan
            if (Schema::hasColumn('laporan_kecelakaans', 'sebab_utama_kategori') && Schema::hasColumn('laporan_kecelakaans', 'sebab_utama_deskripsi')) {
                $table->dropColumn(['sebab_utama_kategori', 'sebab_utama_deskripsi']);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('laporan_kecelakaans', function (Blueprint $table) {
            // Mengembalikan kolom-kolom lama jika migration di-rollback
            $table->string('sebab_utama_kategori')->nullable()->after('apd_data');
            $table->text('sebab_utama_deskripsi')->nullable()->after('sebab_utama_kategori');

            // Hapus kolom-kolom baru
            $table->dropColumn('sebab_utama');
            $table->dropColumn('sebab_kecelakaan');
        });
    }
};