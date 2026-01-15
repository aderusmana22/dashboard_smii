<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Ubah enum status (jika menggunakan MySQL 8+ bisa langsung modify, jika tidak pakai DB::statement)
        DB::statement("ALTER TABLE job_marsho MODIFY COLUMN status ENUM('to_be_scheduled', 'scheduled', 'preparation', 'on_going', 'completed', 'closed', 'cancelled') DEFAULT 'to_be_scheduled'");

        Schema::table('job_marsho', function (Blueprint $table) {
            $table->text('cancellation_reason')->nullable()->after('closed_at');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            //
        });
    }
};
