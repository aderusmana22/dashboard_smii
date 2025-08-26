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
    Schema::table('laporan_kecelakaans', function (Blueprint $table) {
        if (!Schema::hasColumn('laporan_kecelakaans', 'revised_from_id')) {
            $table->unsignedBigInteger('revised_from_id')->nullable()->after('id');
            $table->foreign('revised_from_id')->references('id')->on('laporan_kecelakaans')->onDelete('cascade');
        }
        if (!Schema::hasColumn('laporan_kecelakaans', 'revision_number')) {
            $table->integer('revision_number')->default(0)->after('revised_from_id');
        }
    });
}

public function down(): void
{
    Schema::table('laporan_kecelakaans', function (Blueprint $table) {
        if (Schema::hasColumn('laporan_kecelakaans', 'revised_from_id')) {
            $table->dropForeign(['revised_from_id']);
            $table->dropColumn('revised_from_id');
        }
        if (Schema::hasColumn('laporan_kecelakaans', 'revision_number')) {
            $table->dropColumn('revision_number');
        }
    });
}
};
