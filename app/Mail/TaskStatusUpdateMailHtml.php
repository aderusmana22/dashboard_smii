<?php

namespace App\Mail;

use App\Models\Task;
use App\Models\User;
use App\Models\JobApprovalDetail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class TaskStatusUpdateMailHtml extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Task $task;
    public string $emailSubject;
    public string $messageBody;
    public ?User $recipientUser;
    public ?string $rejectionReason;
    public ?string $cancelReason;
    public string $actionStatus;

    public function __construct(
        Task $task,
        string $emailSubject,
        string $messageBody,
        ?User $recipientUser = null,
        ?string $rejectionReason = null,
        ?string $cancelReason = null,
        string $actionStatus = ''
    ) {
        $this->task = $task->loadMissing(['pengaju:id,name,nik', 'department:id,department_name']);
        $this->emailSubject = $emailSubject;
        $this->messageBody = $messageBody;
        $this->recipientUser = $recipientUser ?? $task->pengaju;
        $this->rejectionReason = $rejectionReason;
        $this->cancelReason = $cancelReason;
        $this->actionStatus = $actionStatus;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailSubject,
        );
    }

    public function content(): Content
    {
        $viewName = 'emails.tasks.status_update_generic_html';
        $subHeaderText = 'Pembaruan Status Tugas';

        switch ($this->actionStatus) {
            case Task::STATUS_OPEN:
            case Task::STATUS_COMPLETED:
            case Task::STATUS_CANCELLED:
            case Task::STATUS_REJECTED:
                $subHeaderText = 'Marsho JobBoard';
                break;
        }

        return new Content(
            view: $viewName,
            with: [
                'recipient_name' => $this->recipientUser->name ?? 'Pengguna',
                'message_body' => $this->messageBody,
                'task_id_job' => $this->task->id_job,
                'task_location' => $this->task->area,
                'task_description' => $this->task->list_job,
                'department_name' => $this->task->department->department_name ?? 'N/A',
                'requester_name' => $this->task->pengaju->name ?? 'N/A',
                'rejection_reason' => $this->rejectionReason,
                'cancel_reason' => $this->cancelReason,
                'action_status' => $this->actionStatus,
                'current_task_status_text' => Str::upper(str_replace('_', ' ', $this->task->status)),
                'company_name' => config('app.company_name', 'PT. Sinar Meadow International Indonesia'),
                'app_subname' => $subHeaderText,
                'footer_year' => date('Y'),
                'task' => $this->task,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}