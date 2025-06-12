<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Task; 

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('id_job')->unique();

            $table->foreignId('pengaju_id')->constrained('users')->comment('User yang mengajukan task');
            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade')->comment('Departemen Tujuan');
            $table->string('area');
            $table->text('list_job');
            $table->date('tanggal_job_mulai')->nullable();
            $table->date('tanggal_job_selesai')->nullable();

            // MODIFIED: Added new statuses, default to pending_approval
            $table->string('status')->default(Task::STATUS_PENDING_APPROVAL)->comment('Status: pending_approval, rejected, open, completed, closed, cancelled');

            $table->foreignId('penutup_id')->nullable()->constrained('users')->comment('User yang menutup task');
            $table->timestamp('closed_at')->nullable()->comment('Waktu task ditutup/diarsipkan');

            // NEW FIELDS FOR APPROVAL
            $table->foreignId('approver_id')->nullable()->constrained('users')->comment('User yang menyetujui/menolak task');
            $table->timestamp('approved_at')->nullable()->comment('Waktu task disetujui/ditolak');
            $table->text('rejection_reason')->nullable();
            $table->string('approval_token')->nullable()->unique()->comment('Token untuk approval via email');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tasks');
    }
};
