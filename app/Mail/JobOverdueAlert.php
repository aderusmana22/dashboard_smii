<?php

namespace App\Mail;

use App\Models\JobMarsho;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class JobOverdueAlert extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public JobMarsho $job)
    {
        // Data $job otomatis bisa diakses di view karena property public
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $days = Carbon::now()->diffInDays($this->job->last_stage_update);

        return new Envelope(
            subject: "URGENT: Job {$this->job->id_job} Stuck for {$days} Days",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.jobs.overdue', // Kita akan buat view ini di langkah 2
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}