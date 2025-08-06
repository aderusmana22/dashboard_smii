<?php

namespace App\Mail;

use App\Models\JobMarsho;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class JobCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $job;

    /**
     * Create a new message instance.
     *
     * @param  \App\Models\JobMarsho  $job
     * @return void
     */
    public function __construct(JobMarsho $job)
    {
        $this->job = $job;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Pekerjaan Telah Selesai: ' . $this->job->id_job)
                    ->view('emails.job_completed');
    }
}