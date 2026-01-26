@props(['job'])

@php
    $user = Auth::user();
    $isSuperAdmin = $user && $user->isSuperAdmin();
    $latestRoute = $job->latestRoute;
    $currentDeptId = $latestRoute->to_department_id ?? null;
    $currentDeptName = trim($latestRoute->toDepartment->department_name ?? 'Default');

    $userMarshoDepartmentId = optional($user->marshoProfile)->marsho_department_id;
    $canAct = ($userMarshoDepartmentId == $currentDeptId) || $isSuperAdmin;
    $isRequester = $job->pengaju_id == $user->id;

    $departmentColors = [
        'Engineering & Maintainance' => 'bg-blue-600',
        'Finance Admin' => 'bg-green-600',
        'HCD' => 'bg-pink-600',
        'Marsho' => 'bg-indigo-600',
        'Batch' => 'bg-rose-600',
        'QM & HSE' => 'bg-red-600',
        'R&D' => 'bg-purple-600',
        'Sales & Marketing' => 'bg-sky-600',
        'PPIC' => 'bg-amber-600',
        'Inward Warehouse' => 'bg-lime-600',
        'Outward Warehouse' => 'bg-cyan-600',
        'Purchasing' => 'bg-orange-600',
        'site service' => 'bg-teal-600',
        'Default' => 'bg-gray-600',
    ];
    $headerColor = $departmentColors[$currentDeptName] ?? $departmentColors['Default'];
@endphp

<div class="rounded-xl overflow-hidden shadow-lg flex flex-col text-white relative group transition hover:shadow-2xl"
    id="job-card-{{ $job->id }}">

    <div class="{{ $headerColor }} p-3 flex justify-between items-start">
        <div>
            <span class="text-[10px] uppercase opacity-75 block tracking-wider">ID Job</span>
            <h3 class="font-bold text-lg leading-tight">{{ $job->id_job }}</h3>
        </div>
        <div class="bg-white/20 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide">
            {{ str_replace('_', ' ', $job->status) }}
        </div>
    </div>

    <div class="p-4 bg-white space-y-3 text-sm flex-1">

        <div class="grid grid-cols-2 gap-2">
            <div>
                <span class="text-[10px] text-gray-400 block mb-0.5">From</span>

                <p class="font-semibold text-gray-900 truncate" title="{{ $job->pengaju->name }}">
                    {{ $job->pengaju->name }}</p>
            </div>
            <div>
                <span class="text-[10px] text-gray-400 block mb-0.5">To Department</span>

                <span
                    class="bg-yellow-200 text-yellow-800 text-[10px] font-bold px-2 py-0.5 rounded-full truncate inline-block max-w-full">
                    {{ $currentDeptName }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-2 border-t border-gray-200 pt-2 mt-2">
            <div>
                <span class="text-[10px] text-gray-400 block mb-0.5">Start</span>
                <p class="font-medium text-gray-900">
                    {{ \Carbon\Carbon::parse($job->tanggal_job_mulai)->format('d M Y') }}</p>
            </div>
            <div>
                <span class="text-[10px] text-gray-400 block mb-0.5 ">End (Deadline)</span>

                <p
                    class="font-medium{{ \Carbon\Carbon::parse($job->deadline)->isPast() && !in_array($job->status, ['completed', 'closed']) ? ' text-red-400' : ' text-gray-500' }}">
                    {{ \Carbon\Carbon::parse($job->deadline)->format('d M Y') }}
                </p>
            </div>
        </div>

        <div class="border-t border-gray-200 pt-2 mt-2">
            <span class="text-[10px] text-gray-400 block mb-0.5">Processed by (Updated)</span>
            <p class="font-medium text-gray-900 truncate">
                {{ $latestRoute->creator->name ?? 'System' }}
                <span class="text-xs text-gray-500 font-normal ml-1">at
                    {{ \Carbon\Carbon::parse($job->last_stage_update)->format('d M Y H:i') }}</span>
            </p>
        </div>

        <div class="border-t border-gray-200 pt-2 mt-2">
            <span class="text-[10px] text-gray-400 block mb-0.5">Location</span>
            <p class="font-bold text-gray-900">{{ $job->area->name ?? '-' }}</p>
        </div>

        <div class="mt-2">
            <span class="text-[10px] text-gray-400 block mb-1">Description:</span>
            <div class="p-2 bg-gray-900/50 text-xs text-gray-300 h-16 overflow-y-auto custom-scrollbar rounded-md">
                {{ $job->list_job }}
            </div>
        </div>
    </div>

    <div class="p-3 {{ $headerColor }} flex justify-between items-center gap-2">

        <button type="button" class="show-detail-btn text-white hover:text-white/80 text-xs underline"
            data-job-id="{{ $job->id }}">
            Details & History
        </button>

        <div class="flex items-center gap-2">
            @if($isRequester && !in_array($job->status, ['completed', 'closed', 'cancelled']))
                <button
                    class="cancel-job-btn bg-red-600 hover:bg-red-700 text-white text-xs font-bold px-3 py-2 rounded shadow transition"
                    data-job-id="{{ $job->id }}">
                    Cancel
                </button>
            @endif

            @if($canAct)
                @if($job->status == 'scheduled')
                    <button
                        class="move-stage-btn bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-4 py-2 rounded shadow transition"
                        data-job-id="{{ $job->id }}" data-target-status="preparation" data-title="Start Preparation">
                        Start Prep
                    </button>
                @elseif($job->status == 'preparation')

                    <button
                        class="move-stage-btn bg-white text-purple-900 hover:bg-gray-200 text-xs font-bold px-4 py-2 rounded shadow transition"
                        data-job-id="{{ $job->id }}" data-target-status="on_going" data-title="Start Job">
                        Start Job
                    </button>
                @elseif($job->status == 'on_going')
                    <button
                        class="forward-job-btn bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-bold px-3 py-2 rounded shadow transition"
                        data-job-id="{{ $job->id }}">
                        Forward
                    </button>
                    <button
                        class="complete-job-btn bg-green-600 hover:bg-emerald-600 text-white text-xs font-bold px-4 py-2 rounded shadow transition"
                        data-job-id="{{ $job->id }}">
                        Complete
                    </button>
                @endif
            @endif

            @if($job->status == 'completed' && ($isRequester || $isSuperAdmin))
                <button
                    class="close-job-btn bg-gray-600 hover:bg-gray-500 text-white text-xs font-bold px-4 py-2 rounded shadow transition"
                    data-job-id="{{ $job->id }}">
                    Close
                </button>
            @endif
        </div>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #475569;
        border-radius: 2px;
    }
</style>