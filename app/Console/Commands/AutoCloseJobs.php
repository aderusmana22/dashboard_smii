<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JobMarsho;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\JobAutoClosedNotification; // Kita akan buat Mail ini nanti
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class AutoCloseJobs extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'jobs:autoclose';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Automatically closes completed jobs after 2 working days and notifies all involved users.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to check for completed jobs to auto-close...');

        // Cari pekerjaan yang statusnya 'completed' dan sudah selesai 2 hari kerja yang lalu atau lebih
        $twoWorkDaysAgo = Carbon::now()->subWeekdays(2)->endOfDay();

        $jobsToClose = JobMarsho::where('status', 'completed')
            ->where('tanggal_job_selesai', '<=', $twoWorkDaysAgo)
            ->get();

        if ($jobsToClose->isEmpty()) {
            $this->info('No completed jobs found that are old enough to be closed.');
            return;
        }

        $this->info("Found {$jobsToClose->count()} jobs to be auto-closed.");

        foreach ($jobsToClose as $job) {
            // 1. UBAH STATUS JOB
            $job->update([
                'status' => 'closed',
                'closed_at' => Carbon::now(),
                'penutup_id' => null, // Tandai sebagai ditutup oleh sistem
            ]);

            $job->notes()->create([
                'job_id' => $job->id,
                'note' => 'AUTO-CLOSED: Job was automatically closed by the system after being completed for more than 2 working days.',
                'created_by' => null, // ID user sistem atau null
            ]);

            $this->info("Job [{$job->id_job}] has been closed.");

            // 2. KUMPULKAN SEMUA USER YANG TERLIBAT
            $involvedUsers = new Collection();

            // a. Requester (Pengaju)
            if ($job->pengaju) {
                $involvedUsers->push($job->pengaju);
            }

            // b. Semua user yang membuat route atau note (creator)
            $job->load(['routes.creator', 'notes.creator']);
            $routeCreators = $job->routes->pluck('creator')->filter();
            $noteCreators = $job->notes->pluck('creator')->filter();
            $involvedUsers = $involvedUsers->merge($routeCreators)->merge($noteCreators);

            // c. Semua user di departemen yang pernah dituju
            $departmentIds = $job->routes->pluck('to_department_id')->filter()->unique();
            if ($departmentIds->isNotEmpty()) {
                $usersInDepts = User::whereHas('marshoProfile', function ($query) use ($departmentIds) {
                    $query->whereIn('marsho_department_id', $departmentIds);
                })->get();
                $involvedUsers = $involvedUsers->merge($usersInDepts);
            }

            // Unikkan koleksi user berdasarkan ID
            $uniqueUsers = $involvedUsers->unique('id');

            // 3. KIRIM NOTIFIKASI
            foreach ($uniqueUsers as $user) {
                try {
                    // Gunakan \App\Mail\... jika belum di-import di atas
                    Mail::to($user->email)->send(new JobAutoClosedNotification($job, $user));
                    $this->line(" - Notified {$user->email}");
                } catch (\Exception $e) {
                    Log::error("Error notifying {$user->email}: " . $e->getMessage());
                }
            }
        }

        $this->info('Auto-close process finished.');
    }
}