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
        Schema::create('approval_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_kecelakaan_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->comment('Approver ID')->constrained()->onDelete('cascade');
            $table->string('token', 64)->unique();
            $table->string('action'); // 'approve' atau 'reject'
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_tokens');
    }
};