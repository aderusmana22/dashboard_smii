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
        Schema::create('laporan_approval_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_kecelakaan_id')->constrained('laporan_kecelakaans')->onDelete('cascade');
            $table->string('status')->default('pending_manager_hse');
            $table->foreignId('current_approver_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_approval_statuses');
    }
};