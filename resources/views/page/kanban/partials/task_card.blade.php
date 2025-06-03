{{-- File: resources/views/page/kanban/partials/task_card.blade.php --}}
@php
    // --- Initialize all variables to defaults FIRST ---
    $loggedInUser = Auth::user();

    $isSuperAdmin = false;
    $isAdminProject = false;
    $isPengaju = false;
    $isTargetDepartmentMember = false;
    $departmentName = 'N/A';
    $departmentSlug = 'na';
    $headerBgClass = 'bg-gray-400 dark:bg-gray-600';
    $processedApproval = null;
    $approverName = null;
    $processedAt = null;
    $rejectionNotes = null;
    $userIsPendingApprover = false;

    // Task specific properties
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

    // --- CRITICAL: Only access $task properties if $task is NOT NULL ---
    if (isset($task) && $task !== null) {
        $taskId = $task->id;
        $taskIdJob = $task->id_job;
        $taskStatus = $task->status;

        if ($loggedInUser) {
            $isPengaju = isset($task->pengaju_id) && $task->pengaju_id === $loggedInUser->id;
            $isTargetDepartmentMember = (isset($task->department_id) && isset($loggedInUser->department_id) && ($task->department_id === $loggedInUser->department_id));
        }

        $departmentName = optional($task->department)->department_name ?? 'N/A';
        $departmentSlug = Illuminate\Support\Str::slug($departmentName ?: 'na'); // Ensure slug is always generated

        // Determine header background class based on status
        switch ($taskStatus) {
            case \App\Models\Task::STATUS_PENDING_APPROVAL: $headerBgClass = 'bg-yellow-500 dark:bg-yellow-600'; break;
            case \App\Models\Task::STATUS_OPEN: $headerBgClass = 'bg-blue-500 dark:bg-blue-600'; break;
            case \App\Models\Task::STATUS_COMPLETED: $headerBgClass = 'bg-green-500 dark:bg-green-600'; break;
            case \App\Models\Task::STATUS_CANCELLED: $headerBgClass = 'bg-orange-500 dark:bg-orange-600'; break;
            case \App\Models\Task::STATUS_REJECTED: $headerBgClass = 'bg-red-600 dark:bg-red-700'; break;
            case \App\Models\Task::STATUS_CLOSED: $headerBgClass = 'bg-gray-500 dark:bg-gray-700'; break;
        }

        if (method_exists($task, 'processedApprovalDetail')) {
            $processedApproval = $task->processedApprovalDetail();
            if ($processedApproval) {
                $approverName = optional($processedApproval->approver)->name ?? $processedApproval->approver_nik;
                $processedAt = $processedApproval->processed_at ?? $processedApproval->updated_at; // Fallback to updated_at
                if ($processedApproval->status === \App\Models\JobApprovalDetail::STATUS_REJECTED) {
                    $rejectionNotes = $processedApproval->notes;
                }
            }
        }

        $taskPengajuName = optional($task->pengaju)->name ?? 'N/A';
        $taskTanggalMulaiFormatted = $task->tanggal_job_mulai ? \Carbon\Carbon::parse($task->tanggal_job_mulai)->format('d M Y') : '---';
        $taskTanggalSelesaiFormatted = $task->tanggal_job_selesai ? \Carbon\Carbon::parse($task->tanggal_job_selesai)->format('d M Y') : '---';

        if ($taskStatus == \App\Models\Task::STATUS_CANCELLED) {
            $taskCancelReason = $task->cancel_reason;
        }

        if ($taskStatus == \App\Models\Task::STATUS_CLOSED) {
            $taskPenutupName = optional($task->penutup)->name ?? 'N/A';
            $taskClosedAtFormatted = $task->closed_at ? \Carbon\Carbon::parse($task->closed_at)->format('d M Y H:i') : '---';
        }
        $taskArea = $task->area ?: 'N/A';
        $taskListJob = $task->list_job ?? '';

        // Check if the logged-in user is a pending approver for this task
        if ($loggedInUser && isset($loggedInUser->nik) && $taskStatus == \App\Models\Task::STATUS_PENDING_APPROVAL && method_exists($task, 'pendingApprovalDetails')) {
            $userIsPendingApprover = $task->pendingApprovalDetails()->where('approver_nik', $loggedInUser->nik)->exists();
        }
    }
@endphp

{{-- Only render the card if $task was valid and processed --}}
@if (isset($task) && $task !== null && $taskId !== null)
<div class="task-card bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg shadow-md flex flex-col h-full" data-task-id="{{ $taskId }}" data-task-idjob="{{ $taskIdJob }}">
    <div class="flex-grow">
        {{-- Header --}}
        <div class="flex justify-between items-center {{ $headerBgClass }} text-white p-3 rounded-t-lg">
            <div>
                <span class="block text-xs opacity-80">ID JOB</span>
                <span class="text-sm font-semibold">{{ $taskIdJob ?? 'N/A' }}</span>
            </div>
            <span class="status-badge status-{{$taskStatus}}">
                {{ Illuminate\Support\Str::upper(str_replace('_', ' ', $taskStatus ?? '')) }}
            </span>
        </div>

        {{-- Main Content --}}
        <div class="p-3 space-y-2">
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <span class="card-header-label text-gray-500 dark:text-gray-400">From</span>
                    <span class="card-header-value text-gray-900 dark:text-gray-100 truncate block" title="{{ $taskPengajuName }}">{{ $taskPengajuName }}</span>
                </div>
                <div>
                    <span class="card-header-label text-gray-500 dark:text-gray-400">To Department</span>
                    <span class="dept-badge dept-{{ $departmentSlug }} text-xs font-medium px-2 py-0.5 rounded-md inline-block truncate" title="{{ $departmentName }}">{{ $departmentName }}</span>
                </div>
                <div>
                    <span class="card-header-label text-gray-500 dark:text-gray-400">Start</span>
                    <span class="card-header-value text-gray-900 dark:text-gray-100">{{ $taskTanggalMulaiFormatted }}</span>
                </div>
                <div>
                    <span class="card-header-label text-gray-500 dark:text-gray-400">End</span>
                    <span class="card-header-value text-gray-900 dark:text-gray-100">{{ $taskTanggalSelesaiFormatted }}</span>
                </div>
            </div>

            @if ($processedApproval)
                <div class="pt-1 border-t border-gray-200 dark:border-gray-700 mt-2">
                    <span class="card-header-label text-gray-500 dark:text-gray-400">
                        @if ($processedApproval->status == \App\Models\JobApprovalDetail::STATUS_REJECTED) Processed by (Rejected)
                        @elseif ($processedApproval->status == \App\Models\JobApprovalDetail::STATUS_APPROVED) Processed by (Approved)
                        @else Processed by @endif
                    </span>
                    <span class="card-header-value text-gray-900 dark:text-gray-100">
                        {{ $approverName ?? 'N/A' }}
                        @if ($processedAt) at {{ \Carbon\Carbon::parse($processedAt)->format('d M Y H:i') }} @endif
                    </span>
                </div>
                @if ($rejectionNotes)
                <div class="pt-1">
                    <span class="card-header-label text-gray-500 dark:text-gray-400">Rejection Notes</span>
                    <span class="card-header-value text-red-600 dark:text-red-400 text-xs break-all">{{ $rejectionNotes }}</span>
                </div>
                @endif
            @endif

            @if ($taskStatus == \App\Models\Task::STATUS_CANCELLED && $taskCancelReason)
            <div class="pt-1 border-t border-gray-200 dark:border-gray-700 mt-2">
                <span class="card-header-label text-gray-500 dark:text-gray-400">Cancellation Reason</span>
                <span class="card-header-value text-orange-600 dark:text-orange-400 text-xs break-all">{{ $taskCancelReason }}</span>
            </div>
            @endif

            @if ($taskStatus == \App\Models\Task::STATUS_CLOSED && $taskPenutupName !== 'N/A')
            <div class="pt-1 border-t border-gray-200 dark:border-gray-700 mt-2">
                <span class="card-header-label text-gray-500 dark:text-gray-400">Closed by</span>
                <span class="card-header-value text-gray-900 dark:text-gray-100">{{ $taskPenutupName }} at {{ $taskClosedAtFormatted }}</span>
            </div>
            @endif
        </div>

        <div class="px-3 py-2 border-t border-gray-200 dark:border-gray-700">
            <span class="card-header-label text-gray-500 dark:text-gray-400">Location</span>
            <span class="card-header-value text-gray-900 dark:text-gray-100">{{ $taskArea }}</span>
        </div>

        <div class="px-3 py-2">
            <p class="text-xs font-medium mb-1 text-gray-600 dark:text-gray-400">Description:</p>
            <div class="task-content-scrollable bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600">
                <pre class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap break-words">{{ $taskListJob }}</pre>
            </div>
        </div>
    </div>

    {{-- Buttons Area --}}
    <div class="mt-auto p-3 border-t border-gray-200 dark:border-gray-600 flex flex-wrap gap-2 justify-end">
        @php $commonButtonClass = "text-xs px-3 py-1.5 rounded-md font-semibold focus:outline-none focus:ring-2 focus:ring-offset-1 dark:focus:ring-offset-gray-800 whitespace-nowrap"; @endphp

        @if ($taskStatus == \App\Models\Task::STATUS_PENDING_APPROVAL)
            @if ($isPengaju || $isAdminProject || $isSuperAdmin)
                <button data-action="cancel" data-task-id="{{ $taskId }}" data-task-idjob="{{ $taskIdJob }}" class="{{ $commonButtonClass }} bg-orange-500 hover:bg-orange-600 text-white focus:ring-orange-400">Cancel</button>
            @endif
            {{-- UI-based approve/reject for users with permission, if you implement this in JS --}}
            {{-- @if($userIsPendingApprover || $isAdminProject || $isSuperAdmin)
                <button data-action="approve_ui" data-task-id="{{ $taskId }}" class="{{ $commonButtonClass }} bg-green-500 hover:bg-green-600 text-white focus:ring-green-400">Approve (UI)</button>
                <button data-action="reject_ui" data-task-id="{{ $taskId }}" data-task-idjob="{{ $taskIdJob }}" class="{{ $commonButtonClass }} bg-red-500 hover:bg-red-600 text-white focus:ring-red-400">Reject (UI)</button>
            @endif --}}
        @elseif ($taskStatus == \App\Models\Task::STATUS_OPEN)
            @if ($isPengaju || $isAdminProject || $isSuperAdmin || $isTargetDepartmentMember)
                <button data-action="complete" data-task-id="{{ $taskId }}" class="{{ $commonButtonClass }} bg-green-500 hover:bg-green-600 text-white focus:ring-green-400">Complete</button>
            @endif
            @if ($isPengaju || $isAdminProject || $isSuperAdmin)
                <button data-action="cancel" data-task-id="{{ $taskId }}" data-task-idjob="{{ $taskIdJob }}" class="{{ $commonButtonClass }} bg-orange-500 hover:bg-orange-600 text-white focus:ring-orange-400 ml-2">Cancel</button>
            @endif
        @elseif ($taskStatus == \App\Models\Task::STATUS_COMPLETED)
            @if ($isPengaju || $isAdminProject || $isSuperAdmin)
                <button data-action="archive" data-task-id="{{ $taskId }}" class="{{ $commonButtonClass }} bg-gray-500 hover:bg-gray-600 text-white focus:ring-gray-400">Archive</button>
            @endif
            @if ($isAdminProject || $isSuperAdmin)
                <button data-action="reopen" data-task-id="{{ $taskId }}" class="{{ $commonButtonClass }} bg-blue-500 hover:bg-blue-600 text-white focus:ring-blue-400 ml-2">Re-Open</button>
            @endif
        @elseif (in_array($taskStatus, [\App\Models\Task::STATUS_REJECTED, \App\Models\Task::STATUS_CANCELLED]))
            @if ($isPengaju && $taskStatus == \App\Models\Task::STATUS_REJECTED) {{-- Only Pengaju can archive their rejected task --}}
                <button data-action="archive" data-task-id="{{ $taskId }}" class="{{ $commonButtonClass }} bg-gray-500 hover:bg-gray-600 text-white focus:ring-gray-400">Archive</button>
            @endif
            @if ($isAdminProject || $isSuperAdmin) {{-- Admins can reopen or delete --}}
                <button data-action="reopen" data-task-id="{{ $taskId }}" class="{{ $commonButtonClass }} bg-blue-500 hover:bg-blue-600 text-white focus:ring-blue-400 {{ ($isPengaju && $taskStatus == \App\Models\Task::STATUS_REJECTED) ? 'ml-2' : '' }}">Re-Open</button>
                <button data-action="delete_permanently" data-task-id="{{ $taskId }}" class="{{ $commonButtonClass }} bg-red-700 hover:bg-red-800 text-white focus:ring-red-600 ml-2">Delete</button>
            @endif
        @elseif ($taskStatus == \App\Models\Task::STATUS_CLOSED) {{-- Archived tasks --}}
            @if ($isAdminProject || $isSuperAdmin)
                <button data-action="reopen" data-task-id="{{ $taskId }}" class="{{ $commonButtonClass }} bg-blue-500 hover:bg-blue-600 text-white focus:ring-blue-400">Re-Open</button>
                <button data-action="delete_permanently" data-task-id="{{ $taskId }}" class="{{ $commonButtonClass }} bg-red-700 hover:bg-red-800 text-white focus:ring-red-600 ml-2">Delete</button>
            @endif
        @endif
    </div>
</div>
@else
    {{-- Fallback or placeholder if task data is not available --}}
    {{-- <div class="p-4 text-center text-gray-500">Task data unavailable.</div> --}}
@endif