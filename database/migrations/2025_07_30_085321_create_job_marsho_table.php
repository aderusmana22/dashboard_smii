<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('job_marsho', function (Blueprint $table) {
            $table->id();
            $table->string('id_job')->unique();
            $table->foreignId('pengaju_id')->constrained('users')->comment('Requester');
            $table->foreignId('area_id')->constrained('areas')->onDelete('restrict');
            $table->text('list_job');
            $table->date('tanggal_job_mulai')->nullable();
            $table->date('tanggal_job_selesai')->nullable();
            $table->enum('status', ['open', 'on_process', 'completed', 'closed'])->default('open');
            $table->foreignId('penutup_id')->nullable()->constrained('users');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('job_marsho');
    }
};