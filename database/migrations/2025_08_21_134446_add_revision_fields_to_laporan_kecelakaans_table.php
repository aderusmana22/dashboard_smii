<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_kecelakaans', function (Blueprint $table) {
            // Menyimpan ID laporan asli yang direvisi. Nullable.
            $table->foreignId('revised_from_id')->nullable()->constrained('laporan_kecelakaans')->onDelete('set null');
            
            // Menyimpan nomor revisi (1, 2, 3, dst.)
            $table->unsignedInteger('revision_number')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('laporan_kecelakaans', function (Blueprint $table) {
            $table->dropForeign(['revised_from_id']);
            $table->dropColumn(['revised_from_id', 'revision_number']);
        });
    }
};