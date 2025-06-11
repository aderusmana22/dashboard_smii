<?php

namespace App\Mail;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TaskStatusUpdateMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Task $task;
    public string $emailSubject;
    public string $messageBody;
    public ?User $recipient; // To address the email correctly

    public function __construct(Task $task, string $emailSubject, string $messageBody, ?User $recipient = null)
    {
        $this->task = $task;
        $this->emailSubject = $emailSubject;
        $this->messageBody = $messageBody;
        $this->recipient = $recipient ?? $task->pengaju; // Default to pengaju if not specified
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.tasks.status_update',
            with: [
                'task' => $this->task,
                'messageBody' => $this->messageBody,
                'recipientName' => $this->recipient->name ?? 'User',
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}