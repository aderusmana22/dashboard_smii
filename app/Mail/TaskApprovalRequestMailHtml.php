<?php

namespace App\Mail;

use App\Models\Task;
use App\Models\JobApprovalDetail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TaskApprovalRequestMailHtml extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Task $task;
    public JobApprovalDetail $approvalDetail;

    public function __construct(Task $task, JobApprovalDetail $approvalDetail)
    {
        $this->task = $task->loadMissing(['pengaju:id,name,nik', 'department:id,department_name']);
        $this->approvalDetail = $approvalDetail->loadMissing('approver:id,name,nik');
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
            view: 'emails.tasks.approval_request_html',
            with: [
                'task_id_job' => $this->task->id_job,
                'requester_name' => $this->task->pengaju->name ?? 'N/A',
                'task_location' => $this->task->area,
                'task_description' => $this->task->list_job,
                'department_name' => $this->task->department->department_name ?? 'N/A',
                'approver_name' => $this->approvalDetail->approver->name ?? $this->approvalDetail->approver_nik,
                'approveUrl' => route('tasks.handle_approval', ['token' => $this->approvalDetail->token, 'action' => 'approve']),
                'rejectUrl' => route('tasks.handle_approval', ['token' => $this->approvalDetail->token, 'action' => 'reject']),
                'company_name' => config('app.company_name', 'PT. Sinar Meadow International Indonesia'),
                'app_subname' => config('app.subname', 'Marsho JobBoard'),
                'footer_year' => date('Y'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}