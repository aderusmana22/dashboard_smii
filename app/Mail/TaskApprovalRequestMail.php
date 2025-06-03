<?php

namespace App\Mail;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TaskApprovalRequestMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Task $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Permintaan Persetujuan Tugas Baru: ' . $this->task->id_job,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.tasks.approval_request',
            with: [
                'task' => $this->task,
                'approveUrl' => route('tasks.handle_approval', ['token' => $this->task->approval_token, 'action' => 'approve']),
                'rejectUrl' => route('tasks.handle_approval', ['token' => $this->task->approval_token, 'action' => 'reject']), // You might want a form for rejection reason
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}