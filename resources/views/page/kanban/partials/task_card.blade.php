@php
    $loggedInUser = Auth::user();

    $isSuperAdmin = false;
    $isAdminProject = false;
    $isPengaju = false;
    $isTargetDepartmentMember = false;
    $departmentName = 'N/A';
    $departmentSlug = 'na';

    $headerBgClass = 'bg-secondary text-white';
    $statusBadgeClass = 'bg-light text-dark';
    $headerStyle = '';

    $processedApproval = null;
    $approverName = null;
    $processedAt = null;
    $rejectionNotes = null;
    $userIsPendingApprover = false;

    $taskId = null;
    $taskIdJob = null;
    $taskStatus = null;
    $taskPengajuName = 'N/A';
    $taskTanggalMulaiFormatted = '---';
    $taskTanggalSelesaiFormatted = '---';
    $taskCancelReason = null;
    $taskPenutupName = null;
    $taskClosedAtFormatted = '---';
    $taskArea = 'N/A';
    $taskListJob = '';

    if ($loggedInUser) {
        $isSuperAdmin = $loggedInUser->isSuperAdmin();
        $isAdminProject = $loggedInUser->isAdminProject();
    }

    if (isset($task) && $task !== null) {
        $taskId = $task->id;
        $taskIdJob = $task->id_job;
        $taskStatus = $task->status;

        if ($loggedInUser) {
            $isPengaju = isset($task->pengaju_id) && $task->pengaju_id === $loggedInUser->id;
            $isTargetDepartmentMember = (isset($task->department_id) && isset($loggedInUser->department_id) && ($task->department_id === $loggedInUser->department_id));
        }

        $departmentName = optional($task->department)->department_name ?? 'N/A';
        $departmentSlug = Illuminate\Support\Str::slug($departmentName ?: 'na');

        switch ($taskStatus) {
            case \App\Models\Task::STATUS_PENDING_APPROVAL:
                $headerBgClass = '';
                $headerStyle = 'background-color: #ffc107 !important; color: #212529 !important;';
                $statusBadgeClass = 'bg-warning-subtle text-warning-emphasis';
                break;
            case \App\Models\Task::STATUS_OPEN:
                $headerBgClass = '';
                $headerStyle = 'background-color: #0dcaf0 !important; color: #212529 !important;';
                $statusBadgeClass = 'bg-info-subtle text-info-emphasis';
                break;
            case \App\Models\Task::STATUS_COMPLETED:
                $headerBgClass = '';
                $headerStyle = 'background-color: #198754 !important; color: #ffffff !important;';
                $statusBadgeClass = 'bg-success-subtle text-success-emphasis';
                break;
            case \App\Models\Task::STATUS_CANCELLED:
                $headerBgClass = '';
                $headerStyle = 'background-color: #ffc107 !important; color: #212529 !important;';
                $statusBadgeClass = 'bg-warning-subtle text-warning-emphasis';
                break;
            case \App\Models\Task::STATUS_REJECTED:
                $headerBgClass = '';
                $headerStyle = 'background-color: #dc3545 !important; color: #ffffff !important;';
                $statusBadgeClass = 'bg-danger-subtle text-danger-emphasis';
                break;
            case \App\Models\Task::STATUS_CLOSED:
                $headerBgClass = '';
                $headerStyle = 'background-color: #6c757d !important; color: #ffffff !important;';
                $statusBadgeClass = 'bg-secondary-subtle text-secondary-emphasis';
                break;
            default:
                $headerBgClass = 'bg-secondary text-white';
                $headerStyle = 'background-color: #6c757d !important; color: #ffffff !important;';
                break;
        }

        if (method_exists($task, 'processedApprovalDetail')) {
            $processedApproval = $task->processedApprovalDetail();
            if ($processedApproval) {
                $approverName = optional($processedApproval->approver)->name ?? $processedApproval->approver_nik;
                $processedAt = $processedApproval->processed_at ?? $processedApproval->updated_at;
                if ($processedApproval->status === \App\Models\JobApprovalDetail::STATUS_REJECTED) {
                    $rejectionNotes = $processedApproval->notes;
                }
            }
        }

        $taskPengajuName = optional($task->pengaju)->name ?? 'N/A';
        $taskTanggalMulaiFormatted = $task->tanggal_job_mulai ? \Carbon\Carbon::parse($task->tanggal_job_mulai)->format('d M Y') : '---';
        $taskTanggalSelesaiFormatted = $task->tanggal_job_selesai ? \Carbon\Carbon::parse($task->tanggal_job_selesai)->format('d M Y') : '---';
        $taskCancelReason = $task->cancel_reason;
        $taskPenutupName = optional($task->penutup)->name ?? 'N/A';
        $taskClosedAtFormatted = $task->closed_at ? \Carbon\Carbon::parse($task->closed_at)->format('d M Y H:i') : '---';
        $taskArea = $task->area ?: 'N/A';
        $taskListJob = $task->list_job ?? '';

        if ($loggedInUser && isset($loggedInUser->nik) && $taskStatus == \App\Models\Task::STATUS_PENDING_APPROVAL && method_exists($task, 'pendingApprovalDetails')) {
            $userIsPendingApprover = $task->pendingApprovalDetails()->where('approver_nik', $loggedInUser->nik)->exists();
        }
    }
@endphp

@if (isset($task) && $task !== null && $taskId !== null)
<div class="card d-flex flex-column task-card" data-task-id="{{ $taskId }}" data-task-idjob="{{ $taskIdJob }}">
    <div class="card-header d-flex justify-content-between align-items-center {{ $headerBgClass }}" style="{{ $headerStyle }}">
        <div>
            <small class="d-block opacity-75">ID JOB</small>
            <span class="fw-semibold">{{ $taskIdJob ?? 'N/A' }}</span>
        </div>
        <span class="badge rounded-pill fs-6 {{ $statusBadgeClass }}">
            {{ Illuminate\Support\Str::upper(str_replace('_', ' ', $taskStatus ?? '')) }}
        </span>
    </div>

    <div class="card-body-scroll-container" style="overflow-y: auto;">
        <div class="card-body py-2 px-3">
      <div style="display: flex; flex-wrap: wrap; margin-bottom: 1rem;">
    {{-- Baris 1 --}}
    <div style="width: 50%; padding-right: 8px; box-sizing: border-box;">
        <small style="color: #6c757d;">From</small>
        <div style="font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $taskPengajuName }}">
            {{ $taskPengajuName }}
        </div>
    </div>
    <div style="width: 50%; padding-left: 8px; box-sizing: border-box;">
        <small style="color: #6c757d;">To Department</small>
        <div>
            <span style="display: inline-block; padding: 2px 6px; background-color: #eee; border-radius: 4px;" title="{{ $departmentName }}">
                {{ $departmentName }}
            </span>
        </div>
    </div>

    {{-- Baris 2 --}}
    <div style="width: 50%; padding-right: 8px; margin-top: 1rem; box-sizing: border-box;">
        <small style="color: #6c757d;">Start</small>
        <div style="font-weight: 500;">{{ $taskTanggalMulaiFormatted }}</div>
    </div>
    <div style="width: 50%; padding-left: 8px; margin-top: 1rem; box-sizing: border-box;">
        <small style="color: #6c757d;">End</small>
        <div style="font-weight: 500;">{{ $taskTanggalSelesaiFormatted }}</div>
    </div>
</div>

            @if ($processedApproval || ($taskStatus == \App\Models\Task::STATUS_CANCELLED && $taskCancelReason) || ($taskStatus == \App\Models\Task::STATUS_CLOSED && $taskPenutupName !== 'N/A'))
            <div class="border-top pt-2 mt-2">
                @if ($processedApproval)
                    <div>
                        <small class="text-muted">
                            @if ($processedApproval->status == \App\Models\JobApprovalDetail::STATUS_REJECTED) Processed by (Rejected)
                            @elseif ($processedApproval->status == \App\Models\JobApprovalDetail::STATUS_APPROVED) Processed by (Approved)
                            @else Processed by @endif
                        </small>
                        <div class="fw-medium small">
                            {{ $approverName ?? 'N/A' }}
                            @if ($processedAt) at {{ \Carbon\Carbon::parse($processedAt)->format('d M Y H:i') }} @endif
                        </div>
                    </div>
                    @if ($rejectionNotes)
                    <div class="mt-1">
                        <small class="text-muted">Rejection Notes</small>
                        <div class="text-danger small" style="word-break: break-all;">{{ $rejectionNotes }}</div>
                    </div>
                    @endif
                @endif

                @if ($taskStatus == \App\Models\Task::STATUS_CANCELLED && $taskCancelReason)
                <div>
                    <small class="text-muted">Cancellation Reason</small>
                    <div class="text-warning-emphasis small" style="word-break: break-all;">{{ $taskCancelReason }}</div>
                </div>
                @endif

                @if ($taskStatus == \App\Models\Task::STATUS_CLOSED && $taskPenutupName !== 'N/A')
                <div>
                    <small class="text-muted">Closed by</small>
                    <div class="fw-medium small">{{ $taskPenutupName }} at {{ $taskClosedAtFormatted }}</div>
                </div>
                @endif
            </div>
            @endif
        </div>

<ul class="list-group list-group-flush">
    <li class="list-group-item">
        <small class="text-muted">Location</small>
        <div class="fw-medium">{{ $taskArea }}</div>
    </li>
    <li class="list-group-item">
        <small class="text-muted d-block mb-1">Description</small>
        <div style="border: 1px solid #dee2e6; border-radius: 0.25rem; max-height: 250px; overflow-y: auto;">
            <pre style="margin: 0; padding: 0.5rem; font-size: 0.875em; white-space: pre-wrap; word-break: break-word;">{{ $taskListJob }}</pre>
        </div>
    </li>
</ul>

    </div>

    <div class="card-footer mt-auto d-flex flex-wrap gap-2 justify-content-end">
        @if ($taskStatus == \App\Models\Task::STATUS_PENDING_APPROVAL)
            @if ($isPengaju || $isAdminProject || $isSuperAdmin)
                <button data-action="cancel" data-task-id="{{ $taskId }}" data-task-idjob="{{ $taskIdJob }}" class="btn btn-sm btn-warning">Cancel</button>
            @endif
        @elseif ($taskStatus == \App\Models\Task::STATUS_OPEN)
            @if ($isPengaju || $isAdminProject || $isSuperAdmin || $isTargetDepartmentMember)
                <button data-action="complete" data-task-id="{{ $taskId }}" class="btn btn-sm btn-success">Complete</button>
            @endif
            @if ($isPengaju || $isAdminProject || $isSuperAdmin)
                <button data-action="cancel" data-task-id="{{ $taskId }}" data-task-idjob="{{ $taskIdJob }}" class="btn btn-sm btn-warning">Cancel</button>
            @endif
        @elseif ($taskStatus == \App\Models\Task::STATUS_COMPLETED)
            @if ($isPengaju || $isAdminProject || $isSuperAdmin)
                <button data-action="archive" data-task-id="{{ $taskId }}" class="btn btn-sm btn-secondary">Archive</button>
            @endif
            @if ($isAdminProject || $isSuperAdmin)
                <button data-action="reopen" data-task-id="{{ $taskId }}" class="btn btn-sm btn-info">Re-Open</button>
            @endif
        @elseif (in_array($taskStatus, [\App\Models\Task::STATUS_REJECTED, \App\Models\Task::STATUS_CANCELLED]))
            @if ($isPengaju && $taskStatus == \App\Models\Task::STATUS_REJECTED)
                <button data-action="archive" data-task-id="{{ $taskId }}" class="btn btn-sm btn-secondary">Archive</button>
            @endif
            @if ($isAdminProject || $isSuperAdmin)
                <button data-action="reopen" data-task-id="{{ $taskId }}" class="btn btn-sm btn-info">Re-Open</button>
                <button data-action="delete_permanently" data-task-id="{{ $taskId }}" class="btn btn-sm btn-danger">Delete</button>
            @endif
        @elseif ($taskStatus == \App\Models\Task::STATUS_CLOSED)
            @if ($isAdminProject || $isSuperAdmin)
                <button data-action="reopen" data-task-id="{{ $taskId }}" class="btn btn-sm btn-info">Re-Open</button>
                <button data-action="delete_permanently" data-task-id="{{ $taskId }}" class="btn btn-sm btn-danger">Delete</button>
            @endif
        @endif
    </div>
</div>
@endif