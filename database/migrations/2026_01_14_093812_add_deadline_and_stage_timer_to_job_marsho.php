<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_marsho', function (Blueprint $table) {
            $table->date('deadline')->nullable()->after('tanggal_job_selesai'); // Deadline total pekerjaan
            $table->timestamp('last_stage_update')->nullable()->after('status'); // Untuk reset timer 3 hari
        });
    }

    public function down(): void
    {
        Schema::table('job_marsho', function (Blueprint $table) {
            $table->dropColumn(['deadline', 'last_stage_update']);
        });
    }
};