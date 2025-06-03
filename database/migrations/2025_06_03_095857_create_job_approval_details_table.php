<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\JobApprovalDetail; // Pastikan path model benar

return new class extends Migration
{
    public function up()
    {
        Schema::create('job_approval_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->onDelete('cascade');

            // users.nik adalah VARCHAR(255), bisa jadi NOT NULL atau NULLABLE
            // Sesuaikan 'nullable()' di sini jika users.nik bisa null
            $table->string('approver_nik', 255); // Jika users.nik NOT NULL
            // $table->string('approver_nik', 255)->nullable(); // Jika users.nik NULLABLE

            $table->string('status')->default(JobApprovalDetail::STATUS_PENDING);
            $table->string('token')->nullable()->unique()->comment('Token untuk approval/rejection via email');
            $table->text('notes')->nullable()->comment('Catatan, terutama untuk alasan penolakan');
            $table->timestamp('processed_at')->nullable()->comment('Waktu disetujui atau ditolak');
            $table->timestamps();

            $table->foreign('approver_nik')
                  ->references('nik')
                  ->on('users')
                  ->onDelete('cascade');

            $table->index('token');
        });
    }

    public function down()
    {
        Schema::table('job_approval_details', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign('job_approval_details_approver_nik_foreign');
            }
        });
        Schema::dropIfExists('job_approval_details');
    }
};