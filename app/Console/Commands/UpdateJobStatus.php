<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JobMarsho;
use Carbon\Carbon;
use App\Events\JobUpdated;

class UpdateJobStatus extends Command
{
    protected $signature = 'jobs:update-status';
    protected $description = 'Automatically update job status to scheduled based on start date';

    public function handle()
    {
        $today = Carbon::today();
        
        // Cari job yang masih 'to_be_scheduled' TAPI tanggal mulainya hari ini atau sudah lewat
        $jobs = JobMarsho::where('status', 'to_be_scheduled')
                         ->whereDate('tanggal_job_mulai', '<=', $today)
                         ->get();

        foreach ($jobs as $job) {
            $job->update([
                'status' => 'scheduled',
                'last_stage_update' => Carbon::now() // Reset timer 3 hari
            ]);
            
            // Opsional: Trigger event agar Kanban board real-time update jika ada yang buka
            // JobUpdated::dispatch($job, ''); 
        }

        $this->info("Updated {$jobs->count()} jobs to Scheduled.");
    }
}