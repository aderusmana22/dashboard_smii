<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_is_active_to_laporan_kecelakaans_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_kecelakaans', function (Blueprint $table) {
            // Menandai versi laporan mana yang aktif/terbaru.
            // Laporan baru default-nya aktif.
            $table->boolean('is_active')->default(true)->after('gm_id');
        });
    }

    public function down(): void
    {
        Schema::table('laporan_kecelakaans', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};