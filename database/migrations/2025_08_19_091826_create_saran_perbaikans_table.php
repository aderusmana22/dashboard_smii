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
        Schema::create('saran_perbaikans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_kecelakaan_id')->constrained('laporan_kecelakaans')->onDelete('cascade');
            $table->text('tindakan');
            $table->string('pic');
            $table->date('due_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saran_perbaikans');
    }
};