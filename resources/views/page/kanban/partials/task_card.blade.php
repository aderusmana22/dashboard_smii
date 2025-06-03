@php
    // This PHP block is for initial load. JS will override/regenerate.
    $isSuperAdmin = $currentUser && method_exists($currentUser, 'isSuperAdmin') && $currentUser->isSuperAdmin();
    $isAdminProject = $currentUser && method_exists($currentUser, 'isAdminProject') && $currentUser->isAdminProject();
    $isPengaju = $currentUser && $task->pengaju_id && $currentUser->id === $task->pengaju_id;
    $isTargetDepartmentMember = $currentUser && $currentUser->department_id === $task->department_id;


    $headerBgClass = 'bg-gray-400 dark:bg-gray-600'; // Default
    if ($task->status == \App\Models\Task::STATUS_PENDING_APPROVAL) {
        $headerBgClass = 'bg-yellow-500 dark:bg-yellow-600';
    } elseif ($task->status == \App\Models\Task::STATUS_OPEN) {
        $headerBgClass = 'bg-blue-500 dark:bg-blue-600';
    } elseif ($task->status == \App\Models\Task::STATUS_COMPLETED) {
        $headerBgClass = 'bg-green-500 dark:bg-green-600';
    } elseif ($task->status == \App\Models\Task::STATUS_CANCELLED) {
        $headerBgClass = 'bg-orange-500 dark:bg-orange-600';
    } elseif ($task->status == \App\Models\Task::STATUS_REJECTED) {
        $headerBgClass = 'bg-red-600 dark:bg-red-700';
    } elseif ($task->status == \App\Models\Task::STATUS_CLOSED) {
        $headerBgClass = 'bg-gray-500 dark:bg-gray-700';
    }

    $departmentName = $task->department->department_name ?? 'N/A';
    $departmentSlug = Str::slug($departmentName, '-');
    if (empty($departmentSlug) || $departmentName === 'N/A') {
        $departmentSlug = 'default';
    }

@endphp

<div class="task-card bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg shadow-md flex flex-col h-full" data-task-id="{{ $task->id }}">
    <div class="flex-grow">
        {{-- Header --}}
        <div class="flex justify-between items-center {{ $headerBgClass }} text-white p-3 rounded-t-lg">
            <div>
                <span class="block text-xs opacity-80">ID JOB</span>
                <span class="text-sm font-semibold">{{ $task->id_job }}</span>
            </div>
            <span class="status-badge status-{{$task->status}}">
                {{ Illuminate\Support\Str::upper(str_replace('_', ' ', $task->status)) }}
            </span>
        </div>

        {{-- Main Content --}}
        <div class="p-3 space-y-2">
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <span class="card-header-label text-gray-500 dark:text-gray-400">From</span>
                    <span class="card-header-value text-gray-900 dark:text-gray-100 truncate block" title="{{ $task->pengaju->name ?? 'N/A' }}">{{ $task->pengaju->name ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="card-header-label text-gray-500 dark:text-gray-400">To Department</span>
                    <span class="dept-badge dept-{{ $departmentSlug }} text-xs font-medium px-2 py-0.5 rounded-md inline-block truncate" title="{{ $departmentName }}">{{ $departmentName }}</span>
                </div>
                <div>
                    <span class="card-header-label text-gray-500 dark:text-gray-400">Start</span>
                    <span class="card-header-value text-gray-900 dark:text-gray-100">{{ $task->tanggal_job_mulai ? $task->tanggal_job_mulai->format('d M Y') : '---' }}</span>
                </div>
                <div>
                    <span class="card-header-label text-gray-500 dark:text-gray-400">End</span>
                    <span class="card-header-value text-gray-900 dark:text-gray-100">{{ $task->tanggal_job_selesai ? $task->tanggal_job_selesai->format('d M Y') : '---' }}</span>
                </div>
            </div>

            @if ($task->status == \App\Models\Task::STATUS_REJECTED || ($task->approver_id && ($task->status == \App\Models\Task::STATUS_OPEN || $task->status == \App\Models\Task::STATUS_COMPLETED)))
            <div class="pt-1 border-t border-gray-200 dark:border-gray-700 mt-2">
                <span class="card-header-label text-gray-500 dark:text-gray-400">{{ $task->status == \App\Models\Task::STATUS_REJECTED ? 'Processed by (Rejected)' : 'Processed by (Approved)' }}</span>
                <span class="card-header-value text-gray-900 dark:text-gray-100">{{ $task->approver->name ?? 'N/A' }} at {{ $task->approved_at ? $task->approved_at->format('d M Y H:i') : '---' }}</span>
            </div>
                @if ($task->status == \App\Models\Task::STATUS_REJECTED && $task->rejection_reason)
                <div class="pt-1">
                    <span class="card-header-label text-gray-500 dark:text-gray-400">Rejection Reason</span>
                    <span class="card-header-value text-red-600 dark:text-red-400 text-xs">{{ $task->rejection_reason }}</span>
                </div>
                @endif
            @endif

            @if ($task->status == \App\Models\Task::STATUS_CLOSED && $task->penutup)
            <div class="pt-1 border-t border-gray-200 dark:border-gray-700 mt-2">
                <span class="card-header-label text-gray-500 dark:text-gray-400">Closed by</span>
                <span class="card-header-value text-gray-900 dark:text-gray-100">{{ $task->penutup->name }} at {{ $task->closed_at ? $task->closed_at->format('d M Y H:i') : '---' }}</span>
            </div>
            @endif
        </div>

        <div class="px-3 py-2 border-t border-gray-200 dark:border-gray-700">
            <span class="card-header-label text-gray-500 dark:text-gray-400">Location</span>
            <span class="card-header-value text-gray-900 dark:text-gray-100">{{ $task->area ?: 'N/A' }}</span>
        </div>

        <div class="px-3 py-2">
            <p class="text-xs font-medium mb-1 text-gray-600 dark:text-gray-400">Description:</p>
            <div class="task-content-scrollable bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600">
                <pre class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap break-words">{{ $task->list_job }}</pre>
            </div>
        </div>
    </div>

    {{-- Buttons Area --}}
    <div class="mt-auto p-3 border-t border-gray-200 dark:border-gray-600 flex flex-wrap gap-2 justify-end">
        @php $commonButtonClass = "text-xs px-3 py-1.5 rounded-md font-semibold focus:outline-none focus:ring-2 focus:ring-offset-1 whitespace-nowrap"; @endphp

        @if ($task->status == \App\Models\Task::STATUS_PENDING_APPROVAL)
            {{-- Buttons for pending approval are typically not on the card for general users, handled by email/specific roles --}}
        @elseif ($task->status == \App\Models\Task::STATUS_OPEN)
            @if ($isPengaju || $isAdminProject || $isSuperAdmin)
                <button data-action="complete" data-task-id="{{ $task->id }}" class="{{ $commonButtonClass }} bg-green-500 hover:bg-green-600 text-white focus:ring-green-400">Complete</button>
            @endif
            @if ($isPengaju)
                <button data-action="cancel" data-task-id="{{ $task->id }}" class="{{ $commonButtonClass }} bg-orange-500 hover:bg-orange-600 text-white focus:ring-orange-400">Cancel</button>
            @endif
        @elseif ($task->status == \App\Models\Task::STATUS_COMPLETED)
            @if ($isPengaju)
                <button data-action="archive" data-task-id="{{ $task->id }}" class="{{ $commonButtonClass }} bg-gray-500 hover:bg-gray-600 text-white focus:ring-gray-400">Archive</button>
            @endif
            @if ($isAdminProject || $isSuperAdmin)
                <button data-action="reopen" data-task-id="{{ $task->id }}" class="{{ $commonButtonClass }} bg-blue-500 hover:bg-blue-600 text-white focus:ring-blue-400">Re-Open</button>
            @endif
        @elseif (in_array($task->status, [\App\Models\Task::STATUS_REJECTED, \App\Models\Task::STATUS_CANCELLED]))
            @if ($isAdminProject || $isSuperAdmin)
                <button data-action="reopen" data-task-id="{{ $task->id }}" class="{{ $commonButtonClass }} bg-blue-500 hover:bg-blue-600 text-white focus:ring-blue-400">Re-Open</button>
                <button data-action="delete_permanently" data-task-id="{{ $task->id }}" class="{{ $commonButtonClass }} bg-red-700 hover:bg-red-800 text-white focus:ring-red-600">Delete</button>
            @endif
        @endif
    </div>
</div>