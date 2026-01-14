@props(['job'])

@php
    $user = Auth::user();
    $isSuperAdmin = $user && $user->isSuperAdmin();
    $latestRoute = $job->latestRoute;
    $currentDeptId = $latestRoute->to_department_id ?? null;
    $currentDeptName = trim($latestRoute->toDepartment->department_name ?? 'Default');
    $initialAttachments = $job->initial_attachments;
    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'];
    
    // Authorization
    $userMarshoDepartmentId = optional($user->marshoProfile)->marsho_department_id;
    $canAct = ($userMarshoDepartmentId == $currentDeptId) || $isSuperAdmin;
    $isRequester = $job->pengaju_id == $user->id;

    // SLA & Deadline Logic
    $daysInStage = \Carbon\Carbon::now()->diffInDays($job->last_stage_update);
    $isStageOverdue = $daysInStage > 3; // Lebih dari 3 hari di tahap yang sama
    $isDeadlineOverdue = \Carbon\Carbon::parse($job->deadline)->isPast() && $job->status !== 'completed' && $job->status !== 'closed';

    // Color Coding
    $departmentColors = [
        'Engineering & Maintainance' => 'bg-blue-500 text-white dark:bg-blue-600',
        'Finance Admin'              => 'bg-green-500 text-white dark:bg-green-600',
        'HCD'                        => 'bg-pink-500 text-white dark:bg-pink-600 dark:text-white',
        'Marsho'                     => 'bg-indigo-500 text-white dark:bg-indigo-600 dark:text-white',
        'Batch'                      => 'bg-rose-500 text-white dark:bg-rose-600 dark:text-white',
        'QM & HSE'                   => 'bg-red-500 text-white dark:bg-red-600 dark:text-white',
        'R&D'                        => 'bg-purple-500 text-white dark:bg-purple-600 dark:text-white',
        'Sales & Marketing'          => 'bg-sky-500 text-white dark:bg-sky-600 dark:text-white',
        'PPIC'                       => 'bg-amber-500 text-white dark:bg-amber-600 dark:text-white',
        'Inward Warehouse'           => 'bg-lime-500 text-white dark:bg-lime-600 dark:text-white',
        'Outward Warehouse'          => 'bg-cyan-500 text-white dark:bg-cyan-600 dark:text-white',
        'Purchasing'                 => 'bg-orange-500 text-white dark:bg-orange-600 dark:text-white',
        'site service'               => 'bg-teal-500 text-white dark:bg-teal-600 dark:text-white',
        'Default'                    => 'bg-gray-500 text-white dark:bg-gray-600 dark:text-white',
    ];
    $colorClasses = $departmentColors[$currentDeptName] ?? $departmentColors['Default'];
@endphp

<div class="job-card rounded-lg p-4 shadow-md flex flex-col {{ $colorClasses }} relative transition hover:shadow-lg"
     id="job-card-{{ $job->id }}"
     x-data="{
         openAttachments: false,
         imageModalOpen: false, 
         imageGallery: [], 
         currentImageIndex: 0,
         showAttachment(event, clickedPath, isImage, imageGroup) {
             if (isImage && imageGroup.length > 0) {
                 event.preventDefault();
                 this.imageGallery = imageGroup;
                 this.currentImageIndex = this.imageGallery.indexOf(clickedPath);
                 this.imageModalOpen = true;
             }
         }
     }">

    {{-- SLA WARNING BADGE --}}
    @if($isStageOverdue && $job->status != 'completed' && $job->status != 'closed')
        <div class="absolute -top-2 -right-2 z-10">
            <span class="flex h-6 w-6 relative">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-6 w-6 bg-red-600 text-white text-[10px] items-center justify-center font-bold border-2 border-white" title="Stuck for {{ $daysInStage }} days">
                    !
                </span>
            </span>
        </div>
    @endif

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row justify-between items-start mb-3 pb-3 border-b border-current opacity-75">
        <div class="flex-1">
            <h3 class="font-bold text-lg">{{ $job->id_job }}</h3>
            <p class="text-sm">
                <span class="font-semibold">Req:</span> {{ $job->pengaju->name ?? 'N/A' }}
            </p>
        </div>
        <div class="text-xs mt-2 sm:mt-0 text-left sm:text-right space-y-1">
            <div>
                <span class="font-semibold">Start:</span>
                {{ \Carbon\Carbon::parse($job->tanggal_job_mulai)->format('d M') }}
            </div>
            <div class="{{ $isDeadlineOverdue ? 'bg-red-600 text-white px-1 rounded animate-pulse font-bold' : '' }}">
                <span class="font-semibold">Deadline:</span>
                {{ \Carbon\Carbon::parse($job->deadline)->format('d M') }}
            </div>
            @if($isStageOverdue && $job->status != 'completed' && $job->status != 'closed')
                <div class="text-red-100 bg-red-800/50 px-1 rounded font-bold">
                    Stuck: {{ $daysInStage }} Days
                </div>
            @endif
        </div>
    </div>

    {{-- BODY --}}
    <div class="mb-3">
        <p class="mb-2 break-words text-sm">{{ Str::limit($job->list_job, 100) }}</p>
        
        {{-- Tampilkan Note Terakhir Saja --}}
        @php
            $lastNote = $job->notes->last();
            $displayText = $lastNote ? $lastNote->note : ($latestRoute->note ?? '-');
        @endphp
        
        <div class="text-xs p-2 bg-black/10 rounded-md break-words border-l-2 border-white/50">
            <span class="font-semibold text-[10px] uppercase opacity-70">Latest Note:</span><br>
            {{ Str::limit($displayText, 80) }}
        </div>
    </div>

    {{-- ATTACHMENTS (INITIAL) --}}
    <div class="space-y-2 mb-4">
        @if($initialAttachments->count() > 0)
            @php
                 $initialImagePaths = $initialAttachments
                    ->filter(fn($att) => in_array(strtolower(pathinfo($att->file_name, PATHINFO_EXTENSION)), $imageExtensions))
                    ->map(fn($att) => Storage::url($att->file_path))
                    ->values();
            @endphp
            <div>
                <button @click="openAttachments = !openAttachments" class="text-xs font-bold hover:underline flex items-center /20 px-2 py-1 rounded">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" viewBox="0 0 20 20" fill="currentColor"><path d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V8a2 2 0 00-2-2h-5L9 4H4z" /></svg>
                    <span x-text="openAttachments ? 'Hide Initial Files' : 'Show Initial Files'"></span>
                    <span class="ml-1 text-xs  text-black px-1.5 rounded-full">{{ $initialAttachments->count() }}</span>
                </button>
                <div x-show="openAttachments" style="display: none;" class="mt-2 p-2 bg-black/10 rounded-md text-xs space-y-1">
                    @foreach($initialAttachments as $attachment)
                        @php
                            $isImage = in_array(strtolower(pathinfo($attachment->file_name, PATHINFO_EXTENSION)), $imageExtensions);
                            $filePath = Storage::url($attachment->file_path);
                        @endphp
                        <a href="{{ $filePath }}" target="_blank" @click="showAttachment($event, '{{ $filePath }}', {{ $isImage ? 'true' : 'false' }}, {{ $isImage ? $initialImagePaths->toJson() : '[]' }})" class="block truncate hover:underline">
                            {{ $attachment->file_name }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- FOOTER --}}
    <div class="mt-auto pt-2 border-t border-current opacity-80 flex justify-between items-center">
        {{-- Show More Button (Memicu Modal Detail) --}}
        <button type="button" class="show-detail-btn text-xs /20 hover:/30 text-white font-semibold px-2 py-1 rounded transition-colors" data-job-id="{{ $job->id }}">
            Show More
        </button>

        {{-- Action Buttons --}}
        <div class="flex flex-wrap gap-1 justify-end">
            @if($canAct)
                {{-- Universal Move Stage Button Logic --}}
                @if($job->status == 'scheduled')
                    <button class="move-stage-btn text-xs  text-black hover:bg-gray-200 font-bold px-3 py-1 rounded shadow-sm" 
                            data-job-id="{{ $job->id }}" 
                            data-target-status="preparation" 
                            data-title="Start Preparation">
                        Start Prep
                    </button>
                @endif

                @if($job->status == 'preparation')
                    <button class="move-stage-btn text-xs  text-black hover:bg-gray-200 font-bold px-3 py-1 rounded shadow-sm" 
                            data-job-id="{{ $job->id }}" 
                            data-target-status="on_going" 
                            data-title="Start Job Execution">
                        Start Job
                    </button>
                @endif

                @if($job->status == 'on_going')
                    <button class="forward-job-btn text-xs bg-yellow-400 text-black hover:bg-yellow-300 font-bold px-3 py-1 rounded shadow-sm" 
                            data-job-id="{{ $job->id }}">
                        Forward
                    </button>
                    <button class="complete-job-btn text-xs bg-green-500 text-white hover:bg-green-400 font-bold px-3 py-1 rounded shadow-sm" 
                            data-job-id="{{ $job->id }}">
                        Complete
                    </button>
                @endif
            @endif

            @if($job->status == 'completed' && ($isRequester || $isSuperAdmin))
                <button class="close-job-btn text-xs bg-gray-700 text-white hover:bg-gray-600 font-bold px-3 py-1 rounded shadow-sm" 
                        data-job-id="{{ $job->id }}">
                    Close
                </button>
            @endif
            
            {{-- Tombol Set Schedule manual jika Start Date hari ini tapi status masih To Be Scheduled (sebagai fallback command) --}}
            @if($job->status == 'to_be_scheduled' && $canAct && \Carbon\Carbon::parse($job->tanggal_job_mulai)->isToday())
                 <button class="move-stage-btn text-xs  text-black hover:bg-gray-200 font-bold px-3 py-1 rounded shadow-sm"
                        data-job-id="{{ $job->id }}"
                        data-target-status="scheduled"
                        data-title="Set as Scheduled">
                    Schedule Now
                </button>
            @endif
        </div>
    </div>

    {{-- Simple Image Modal (AlpineJS) --}}
    <div x-show="imageModalOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-90" style="display: none;">
        <div class="relative max-w-full max-h-full" @click.stop>
            <button @click="imageModalOpen = false" class="absolute -top-10 right-0 text-white text-2xl font-bold">&times;</button>
            <img :src="imageGallery[currentImageIndex]" class="object-contain max-h-[85vh] rounded shadow-lg border border-white/20">
        </div>
    </div>
</div>