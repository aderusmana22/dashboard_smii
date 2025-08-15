<?php

namespace App\Jobs;

use App\Models\JobMarsho;
use App\Mail\JobCompletedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendJobCompletedEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $jobMarsho;

    /**
     * Create a new job instance.
     *
     * @param  \App\Models\JobMarsho  $jobMarsho
     * @return void
     */
    public function __construct(JobMarsho $jobMarsho)
    {
        $this->jobMarsho = $jobMarsho;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $requester = $this->jobMarsho->pengaju;
        Mail::to($requester->email)->send(new JobCompletedMail($this->jobMarsho));
    }
}