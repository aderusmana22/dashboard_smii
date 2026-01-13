@props(['job'])

@php
    $user = Auth::user();
    $isSuperAdmin = $user->isSuperAdmin();
    $firstRoute = $job->routes->first();
    $latestRoute = $job->latestRoute;
    $currentDeptId = $latestRoute->to_department_id ?? null;
    $currentDeptName = trim($latestRoute->toDepartment->department_name ?? 'Default');
    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'];
    $initialAttachments = $job->initial_attachments;
    $closingAttachments = $job->closing_attachments;
    $userMarshoDepartmentId = optional($user->marshoProfile)->marsho_department_id;
    $canAct = ($userMarshoDepartmentId == $currentDeptId) || $isSuperAdmin;
    $isRequester = $job->pengaju_id == $user->id;
    
    $initialImagePaths = $initialAttachments
        ->filter(fn($att) => in_array(strtolower(pathinfo($att->file_name, PATHINFO_EXTENSION)), $imageExtensions))
        ->map(fn($att) => Storage::url($att->file_path))
        ->values();

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

<div class="job-card rounded-lg p-4 shadow-md  flex flex-col {{ $colorClasses }}"
     id="job-card-{{ $job->id }}"
     x-data="{
         openAttachments: false,
         imageModalOpen: false, imageGallery: [], currentImageIndex: 0,
         showAttachment(event, clickedPath, isImage, imageGroup) {
             if (isImage && imageGroup.length > 0) {
                 event.preventDefault();
                 this.imageGallery = imageGroup;
                 this.currentImageIndex = this.imageGallery.indexOf(clickedPath);
                 this.imageModalOpen = true;
             }
         },
         nextImage() { this.currentImageIndex = (this.currentImageIndex + 1) % this.imageGallery.length; },
         prevImage() { this.currentImageIndex = (this.currentImageIndex - 1 + this.imageGallery.length) % this.imageGallery.length; }
     }">

    <div class="flex flex-col sm:flex-row justify-between items-start mb-3 pb-3 border-b border-current opacity-50">
        <div class="flex-1">
            <h3 class="font-bold text-lg">{{ $job->id_job }}</h3>
            <p class="text-sm opacity-90">
                <span class="font-semibold">Requester:</span> {{ $job->pengaju->name ?? 'N/A' }}
                @if($firstRoute && $firstRoute->toDepartment)
                <span class="mx-2">→</span>
                <span class="font-semibold">To:</span> {{ $firstRoute->toDepartment->department_name ?? 'N/A' }}
                @endif
            </p>
        </div>
        <div class="text-xs opacity-90 mt-2 sm:mt-0 text-left sm:text-right">
            <div>
                <span class="font-semibold">Start:</span>
                {{ \Carbon\Carbon::parse($job->tanggal_job_mulai)->format('d M Y') }}
            </div>
            @if($job->tanggal_job_selesai)
            <div>
                <span class="font-semibold">End:</span>
                {{ \Carbon\Carbon::parse($job->tanggal_job_selesai)->format('d M Y') }}
            </div>
            @endif
        </div>
    </div>

    <div class="mb-3">
        <p class="mb-2 break-words">{{ $job->list_job }}</p>
        @if($firstRoute && $firstRoute->note && $firstRoute->note !== 'Job created.')
            <p class="text-sm p-2 bg-black/5 dark/10 rounded-md break-words">
                <span class="font-semibold">Initial Note:</span> {{ $firstRoute->note }}
            </p>
        @endif
    </div>

    <div class="space-y-2 mb-4">
        @if($initialAttachments->count() > 0)
        <div>
            <button @click="openAttachments = !openAttachments" class="text-sm font-medium hover:underline flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor"><path d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V8a2 2 0 00-2-2h-5L9 4H4z" /></svg>
                <span x-text="openAttachments ? 'Hide Initial Attachments' : 'Show Initial Attachments'"></span>
                <span class="ml-2 text-black text-xs font-medium px-2 rounded-full">{{ $initialAttachments->count() }}</span>
            </button>
            <div x-show="openAttachments" x-transition class="mt-2 p-3 bg-black/5 dark/10 rounded-md border-t border-black/10 dark:border-white/10 space-y-2">
                @foreach($initialAttachments as $attachment)
                    @php
                        $isImage = in_array(strtolower(pathinfo($attachment->file_name, PATHINFO_EXTENSION)), $imageExtensions);
                        $filePath = Storage::url($attachment->file_path);
                    @endphp
                    <a href="{{ $filePath }}" target="_blank" @click="showAttachment($event, '{{ $filePath }}', {{ $isImage ? 'true' : 'false' }}, {{ $isImage ? $initialImagePaths->toJson() : '[]' }})" class="flex items-center space-x-2 hover:opacity-75 group">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            @if($isImage) <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-4 2 2 4-4 2 2z" clip-rule="evenodd" /> @else <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" /> @endif
                        </svg>
                        <span class="truncate group-hover:underline">{{ $attachment->file_name }}</span>
                    </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="mt-auto pt-3 border-t border-current opacity-50 flex justify-between items-center">
        <div class="text-sm">
            <span class="font-semibold opacity-90">Current Dept:</span>
            <span class="font-bold text-black bg-white">
                {{ $currentDeptName }}
            </span>
        </div>
        <div class="flex flex-wrap gap-2 justify-end">
            @if($job->status == 'to_be_scheduled' && $canAct)
                <button class="schedule-job-btn text-xs hover:bg-gray-600 text-black font-semibold px-3 py-1 rounded-md transition-colors" data-job-id="{{ $job->id }}">Set Schedule</button>
            @endif

            @if($job->status == 'scheduled' && $canAct)
                <button class="prepare-job-btn text-xs hover:bg-gray-600 text-black font-semibold px-3 py-1 rounded-md transition-colors" data-job-id="{{ $job->id }}">Start Prep</button>
            @endif

            @if($job->status == 'preparation' && $canAct)
                <button class="start-job-btn text-xs hover:bg-gray-600 text-black font-semibold px-3 py-1 rounded-md transition-colors" data-job-id="{{ $job->id }}">Start Job</button>
            @endif

            @if($job->status == 'on_going' && $canAct)
                <button class="forward-job-btn text-xs bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-3 py-1 rounded-md transition-colors" data-job-id="{{ $job->id }}">Forward</button>
                <button class="complete-job-btn text-xs bg-green-500 hover:bg-green-600 text-white font-semibold px-3 py-1 rounded-md transition-colors" data-job-id="{{ $job->id }}">Complete</button>
            @endif

            @if($job->status == 'completed' && ($isRequester || $isSuperAdmin))
                <button class="close-job-btn text-xs bg-gray-600 hover:bg-gray-700 text-white font-semibold px-3 py-1 rounded-md transition-colors" data-job-id="{{ $job->id }}">Close Job</button>
            @endif
        </div>
    </div>

    <div x-show="imageModalOpen" x-transition @click="imageModalOpen = false" @keydown.escape.window="imageModalOpen = false" @keydown.arrow-right.window="if(imageModalOpen && imageGallery.length > 1) nextImage()" @keydown.arrow-left.window="if(imageModalOpen && imageGallery.length > 1) prevImage()" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-75" style="display: none;">
        <div class="relative max-w-4xl max-h-full" @click.stop>
            <button @click="imageModalOpen = false" class="absolute -top-2 -right-2 z-30 rounded-full p-1 text-gray-800 hover:bg-gray-200 transition-transform transform hover:scale-110">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
            <button x-show="imageGallery.length > 1" @click="prevImage()" class="absolute top-1/2 -translate-y-1/2 -left-12 z-30 p-2/50 hover/80 rounded-full text-gray-800 transition-transform transform hover:scale-110">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            </button>
            <button x-show="imageGallery.length > 1" @click="nextImage()" class="absolute top-1/2 -translate-y-1/2 -right-12 z-30 p-2/50 hover/80 rounded-full text-gray-800 transition-transform transform hover:scale-110">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </button>
            <img :src="imageGallery[currentImageIndex]" alt="Image Preview" class="object-contain max-w-full max-h-[90vh] rounded-lg shadow-lg">
        </div>
    </div>
</div>