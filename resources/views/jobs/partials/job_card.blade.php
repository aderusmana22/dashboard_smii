{{-- resources/views/jobs/partials/job-card.blade.php --}}

@props(['job'])

@php
    // --- Persiapan Data (SEKARANG JAUH LEBIH BERSIH) ---
    $user = Auth::user();
    $isSuperAdmin = $user->is_super_admin ?? false;

    // Relasi dan data utama
    $firstRoute = $job->routes->first();
    $latestRoute = $job->latestRoute;
    $currentDeptId = $latestRoute->to_department_id ?? null;

    // Daftar ekstensi file gambar yang didukung
    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'];

    // Panggil accessor yang sudah kita buat di Model. JAUH LEBIH ANDAL!
    $initialAttachments = $job->initial_attachments;
    $closingAttachments = $job->closing_attachments;

    // Logika untuk menampilkan tombol aksi
    $canAct = ($user->department_id == $currentDeptId) || $isSuperAdmin;
    $isRequester = $job->pengaju_id == $user->id;

    // Siapkan array URL gambar dalam format JSON untuk slider
    $initialImagePaths = $initialAttachments
        ->filter(fn($att) => in_array(strtolower(pathinfo($att->file_name, PATHINFO_EXTENSION)), $imageExtensions))
        ->map(fn($att) => Storage::url($att->file_path))
        ->values();

    $closingImagePaths = $closingAttachments
        ->filter(fn($att) => in_array(strtolower(pathinfo($att->file_name, PATHINFO_EXTENSION)), $imageExtensions))
        ->map(fn($att) => Storage::url($att->file_path))
        ->values();

@endphp

{{--
    Komponen Kartu Job dengan Alpine.js untuk interaktivitas.
--}}
<div class="job-card bg-white dark:bg-gray-800 rounded-lg p-4 shadow-md border border-gray-200 dark:border-gray-700 flex flex-col"
     data-job-id="{{ $job->id }}"
     x-data="{
         openNotes: false,
         openAttachments: false,
         openRoutes: false,
         openClosingAttachments: false,
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
         },
         nextImage() {
             this.currentImageIndex = (this.currentImageIndex + 1) % this.imageGallery.length;
         },
         prevImage() {
             this.currentImageIndex = (this.currentImageIndex - 1 + this.imageGallery.length) % this.imageGallery.length;
         }
     }">

    {{-- BAGIAN ATAS: ID, Requester, Tanggal --}}
    <div class="flex flex-col sm:flex-row justify-between items-start mb-3 pb-3 border-b border-gray-200 dark:border-gray-600">
        <div class="flex-1">
            <h3 class="font-bold text-lg text-gray-900 dark:text-gray-100">{{ $job->id_job }}</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                <span class="font-semibold">Requester:</span> {{ $job->pengaju->name ?? 'N/A' }}
                @if($firstRoute && $firstRoute->toDepartment)
                <span class="mx-2">→</span>
                <span class="font-semibold">To:</span> {{ $firstRoute->toDepartment->department_name ?? 'N/A' }}
                @endif
            </p>
        </div>
        <div class="text-xs text-gray-500 dark:text-gray-400 mt-2 sm:mt-0 text-left sm:text-right">
            <div>
                <span class="font-semibold">Start:</span>
                {{ \Carbon\Carbon::parse($job->tanggal_job_mulai)->format('d M Y, H:i') }}
            </div>
            @if($job->tanggal_job_selesai)
            <div>
                <span class="font-semibold">End:</span>
                {{ \Carbon\Carbon::parse($job->tanggal_job_selesai)->format('d M Y, H:i') }}
            </div>
            @endif
        </div>
    </div>

    {{-- BAGIAN TENGAH: Deskripsi & Note Awal --}}
    <div class="mb-3">
        <p class="text-gray-800 dark:text-gray-200 mb-2 break-words">{{ $job->list_job }}</p>
        @if($firstRoute && $firstRoute->note && $firstRoute->note !== 'Job created and assigned.')
            <p class="text-sm p-2 bg-gray-100 dark:bg-gray-700 rounded-md break-words">
                <span class="font-semibold">Initial Note:</span> {{ $firstRoute->note }}
            </p>
        @endif
    </div>

    {{-- BAGIAN COLLAPSE: Attachments & Notes --}}
    <div class="space-y-2 mb-4">
        <!-- Tombol Collapse: Initial Attachments -->
        @if($initialAttachments->count() > 0)
        <div>
            <button @click="openAttachments = !openAttachments" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor"><path d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V8a2 2 0 00-2-2h-5L9 4H4z" /></svg>
                <span x-text="openAttachments ? 'Hide Initial Attachments' : 'Show Initial Attachments'"></span>
                <span class="ml-2 bg-blue-100 text-blue-800 text-xs font-medium px-2 rounded-full">{{ $initialAttachments->count() }}</span>
            </button>
            <div x-show="openAttachments" x-transition class="mt-2 p-3 bg-gray-50 dark:bg-gray-700 rounded-md border dark:border-gray-600 space-y-2">
                @foreach($initialAttachments as $attachment)
                    @php
                        $isImage = in_array(strtolower(pathinfo($attachment->file_name, PATHINFO_EXTENSION)), $imageExtensions);
                        $filePath = Storage::url($attachment->file_path);
                    @endphp
                    <a href="{{ $filePath }}"
                       target="_blank"
                       @click="showAttachment($event, '{{ $filePath }}', {{ $isImage ? 'true' : 'false' }}, {{ $isImage ? $initialImagePaths->toJson() : '[]' }})"
                       class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:text-blue-500 dark:hover:text-blue-400 group">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0 {{ $isImage ? 'text-blue-500' : 'text-gray-500' }}" viewBox="0 0 20 20" fill="currentColor">
                            @if($isImage)
                                <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-4 2 2 4-4 2 2z" clip-rule="evenodd" />
                            @else
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                            @endif
                        </svg>
                        <span class="truncate group-hover:underline">{{ $attachment->file_name }}</span>
                    </a>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Tombol Collapse: Route Notes -->
        @if($job->routes->count() > 1 || $job->notes->count() > 0)
        <div>
            <button @click="openRoutes = !openRoutes" class="text-sm font-medium text-green-600 dark:text-green-400 hover:underline flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2a.75.75 0 01.75.75v.25h3.5a.75.75 0 010 1.5h-3.5v1.25a.75.75 0 01-1.5 0V4.5h-3.5a.75.75 0 010-1.5h3.5V2.75A.75.75 0 0110 2zM8.25 9.5a.75.75 0 01.75-.75h2a.75.75 0 010 1.5h-2a.75.75 0 01-.75-.75zM7.5 12.25a.75.75 0 00-1.5 0v.25h-2a.75.75 0 000 1.5h2v.25a.75.75 0 001.5 0v-.25h2a.75.75 0 000-1.5h-2v-.25z" clip-rule="evenodd" /></svg>
                <span x-text="openRoutes ? 'Hide Notes & Routes' : 'Show Notes & Routes'"></span>
            </button>
            <div x-show="openRoutes" x-transition class="mt-2 p-3 bg-gray-50 dark:bg-gray-700 rounded-md text-xs space-y-3 border dark:border-gray-600">
                @foreach($job->routes->slice(1) as $route)
                    @if($route->note)
                    <div class="border-l-2 pl-2 border-yellow-500">
                        <p class="font-semibold">
                            From: {{ $route->fromDepartment->department_name ?? 'N/A' }} → To: {{ $route->toDepartment->department_name ?? 'N/A' }}
                        </p>
                        <p class="text-gray-700 dark:text-gray-300 break-words">{{ $route->note }}</p>
                        <p class="text-gray-500 text-right">by {{ $route->creator->name ?? 'System' }}</p>
                    </div>
                    @endif
                @endforeach
                @foreach($job->notes as $note)
                    <div class="border-l-2 pl-2 border-green-500">
                        <p class="font-semibold text-green-700 dark:text-green-300">Completion Note:</p>
                        <p class="text-gray-700 dark:text-gray-300 break-words">{{ $note->note }}</p>
                        <p class="text-gray-500 text-right">by {{ $note->creator->name ?? 'System' }}</p>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Tombol Collapse: Closing Attachments -->
        @if($closingAttachments->count() > 0)
        <div>
            <button @click="openClosingAttachments = !openClosingAttachments" class="text-sm font-medium text-purple-600 dark:text-purple-400 hover:underline flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor"><path d="M2 6a2 2 0 012-2h5.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V18a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" /></svg>
                <span x-text="openClosingAttachments ? 'Hide Closing Attachments' : 'Show Closing Attachments'"></span>
                <span class="ml-2 bg-purple-100 text-purple-800 text-xs font-medium px-2 rounded-full">{{ $closingAttachments->count() }}</span>
            </button>
            <div x-show="openClosingAttachments" x-transition class="mt-2 p-3 bg-gray-50 dark:bg-gray-700 rounded-md border dark:border-gray-600 space-y-2">
                @foreach($closingAttachments as $attachment)
                    @php
                        $isImage = in_array(strtolower(pathinfo($attachment->file_name, PATHINFO_EXTENSION)), $imageExtensions);
                        $filePath = Storage::url($attachment->file_path);
                    @endphp
                    <a href="{{ $filePath }}"
                       target="_blank"
                       @click="showAttachment($event, '{{ $filePath }}', {{ $isImage ? 'true' : 'false' }}, {{ $isImage ? $closingImagePaths->toJson() : '[]' }})"
                       class="flex items-center space-x-2 text-gray-700 dark:text-gray-300 hover:text-purple-500 dark:hover:text-purple-400 group">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0 {{ $isImage ? 'text-purple-500' : 'text-gray-500' }}" viewBox="0 0 20 20" fill="currentColor">
                            @if($isImage)
                                <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-4 2 2 4-4 2 2z" clip-rule="evenodd" />
                            @else
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                            @endif
                        </svg>
                        <span class="truncate group-hover:underline">{{ $attachment->file_name }}</span>
                    </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- BAGIAN BAWAH: Area & Tombol Aksi --}}
    <div class="mt-auto pt-3 border-t border-gray-200 dark:border-gray-600 flex justify-between items-center">
        <div class="text-sm font-semibold text-gray-600 dark:text-gray-400">
            Area: <span class="text-gray-800 dark:text-gray-200">{{ $job->area->name ?? 'N/A' }}</span>
        </div>
        <div class="flex flex-wrap gap-2 justify-end">
            @if($job->status == 'open' && $canAct)
                <button class="start-job-btn text-xs bg-blue-500 hover:bg-blue-600 text-white font-semibold px-3 py-1 rounded-md transition-colors" data-job-id="{{ $job->id }}">Start Job</button>
            @endif

            @if($job->status == 'on_process' && $canAct)
                <button class="forward-job-btn text-xs bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-3 py-1 rounded-md transition-colors" data-job-id="{{ $job->id }}">Forward</button>
                <button class="complete-job-btn text-xs bg-green-500 hover:bg-green-600 text-white font-semibold px-3 py-1 rounded-md transition-colors" data-job-id="{{ $job->id }}">Complete</button>
            @endif

            @if($job->status == 'completed' && ($isRequester || $isSuperAdmin))
                <button class="close-job-btn text-xs bg-gray-600 hover:bg-gray-700 text-white font-semibold px-3 py-1 rounded-md transition-colors" data-job-id="{{ $job->id }}">Close Job</button>
            @endif
        </div>
    </div>

    <!-- MODAL GAMBAR DENGAN SLIDER -->
    <div x-show="imageModalOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="imageModalOpen = false"
         @keydown.escape.window="imageModalOpen = false"
         @keydown.arrow-right.window="if(imageModalOpen && imageGallery.length > 1) nextImage()"
         @keydown.arrow-left.window="if(imageModalOpen && imageGallery.length > 1) prevImage()"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-75"
         style="display: none;">

        <div class="relative max-w-4xl max-h-full" @click.stop>
            <button @click="imageModalOpen = false" class="absolute -top-2 -right-2 z-30 bg-white rounded-full p-1 text-gray-800 hover:bg-gray-200 transition-transform transform hover:scale-110">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>

            <button x-show="imageGallery.length > 1" @click="prevImage()" class="absolute top-1/2 -translate-y-1/2 -left-12 z-30 p-2 bg-white/50 hover:bg-white/80 rounded-full text-gray-800 transition-transform transform hover:scale-110">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            </button>

            <button x-show="imageGallery.length > 1" @click="nextImage()" class="absolute top-1/2 -translate-y-1/2 -right-12 z-30 p-2 bg-white/50 hover:bg-white/80 rounded-full text-gray-800 transition-transform transform hover:scale-110">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </button>

            <img :src="imageGallery[currentImageIndex]" alt="Image Preview" class="object-contain max-w-full max-h-[90vh] rounded-lg shadow-lg">
        </div>
    </div>
</div>