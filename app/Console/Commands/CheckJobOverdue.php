<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JobMarsho;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\JobOverdueAlert;

class CheckJobOverdue extends Command
{
    protected $signature = 'jobs:check-overdue';
    protected $description = 'Send email if job stays in a stage for more than 3 days to the current Department';

    public function handle()
    {
        // 1. Cari job yang belum selesai/tutup & update terakhir > 3 hari yang lalu
        $jobs = JobMarsho::with(['latestRoute', 'pengaju'])
                         ->whereNotIn('status', ['completed', 'closed'])
                         ->where('last_stage_update', '<', Carbon::now()->subDays(3))
                         ->get();

        foreach ($jobs as $job) {
            // Ambil ID Departemen yang sedang memegang job saat ini
            $currentDeptId = $job->latestRoute->to_department_id ?? null;

            if ($currentDeptId) {
                // 2. Cari semua User yang tergabung dalam Departemen tersebut
                // Asumsi di model User ada relasi 'marshoProfile' ke tabel marsho_users
                $recipients = User::whereHas('marshoProfile', function($query) use ($currentDeptId) {
                    $query->where('marsho_department_id', $currentDeptId);
                })->get();

                if ($recipients->count() > 0) {
                    // Kirim email ke setiap anggota departemen terkait
                    foreach ($recipients as $recipient) {
                        Mail::to($recipient->email)->send(new JobOverdueAlert($job));
                    }
                    
                    $this->info("Alert sent for Job ID: {$job->id_job} to " . $recipients->count() . " users in Dept ID: $currentDeptId");
                } else {
                    $this->warn("Job ID: {$job->id_job} is overdue, but no users found in Dept ID: $currentDeptId");
                }
            }
            
            // Opsi: Tetap kirim CC ke Pengaju agar tahu kalau job-nya macet
            // if ($job->pengaju && $job->pengaju->email) {
            //     Mail::to($job->pengaju->email)->cc(...)->send(new JobOverdueAlert($job));
            // }
        }
    }
}