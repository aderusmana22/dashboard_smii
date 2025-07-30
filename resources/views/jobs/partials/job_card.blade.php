@php
    $user = Auth::user();
    $latestRoute = $job->latestRoute;
    $currentDeptId = $latestRoute->to_department_id ?? null;
    $isSuperAdmin = $user->is_super_admin ?? false;
    $canAct = $user->department_id == $currentDeptId || $isSuperAdmin;
@endphp

<div class="job-card bg-white dark:bg-gray-800 rounded-lg p-4 shadow-md border border-gray-200 dark:border-gray-700" data-job-id="{{ $job->id }}">
    <div class="font-bold text-lg mb-2 text-gray-800 dark:text-gray-100">{{ $job->id_job }}</div>
    <div class="space-y-2 text-sm">
        <p><span class="font-semibold text-gray-600 dark:text-gray-400">Area:</span> <span class="text-gray-800 dark:text-gray-200">{{ $job->area }}</span></p>
        <p><span class="font-semibold text-gray-600 dark:text-gray-400">Requester:</span> <span class="text-gray-800 dark:text-gray-200">{{ $job->pengaju->name ?? 'N/A' }}</span></p>
        <p><span class="font-semibold text-gray-600 dark:text-gray-400">Current Dept:</span> 
            <span class="bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 px-2 py-1 rounded-full text-xs font-medium">{{ $latestRoute->toDepartment->department_name ?? 'N/A' }}</span>
        </p>
    </div>
    <div class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-600 flex justify-end flex-wrap gap-2">
        @if($job->status == 'open' && $canAct)
            <button class="start-job-btn text-xs bg-blue-500 hover:bg-blue-600 text-white font-semibold px-3 py-1 rounded-md" data-job-id="{{ $job->id }}">Start Job</button>
        @endif

        @if($job->status == 'on_process' && $canAct)
            <button class="forward-job-btn text-xs bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-3 py-1 rounded-md" data-job-id="{{ $job->id }}">Forward</button>
            <button class="complete-job-btn text-xs bg-green-500 hover:bg-green-600 text-white font-semibold px-3 py-1 rounded-md" data-job-id="{{ $job->id }}">Complete</button>
        @endif

        @if($job->status == 'completed' && ($job->pengaju_id == $user->id || $isSuperAdmin))
            <button class="close-job-btn text-xs bg-gray-600 hover:bg-gray-700 text-white font-semibold px-3 py-1 rounded-md" data-job-id="{{ $job->id }}">Close Job</button>
        @endif
    </div>
</div>