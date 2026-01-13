<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight mb-4 md:mb-0">
                {{ __('Job Kanban Board') }}
            </h2>
             @if(isset($user))
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    <span class="font-medium">{{ $user->name }}</span>
                    <span class="hidden sm:inline">| {{ optional(optional($user->marshoProfile)->department)->department_name ?? 'N/A Marsho Dept.' }}</span>
                </div>
            @endif
        </div>
    </x-slot>

   <div class="py-8 md:py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden">
                <div class="flex justify-end items-center mb-8">
                    <!-- Tombol Add Job -->
                    <button id="openCreateJobModalBtn"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
                            @if($areas->isEmpty() || $departments->isEmpty()) disabled title="Cannot add job: Areas or Departments are not configured." @endif>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-2 -mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Add New Job
                    </button>
                </div>

                <!-- CONTAINER UTAMA -->
                <!-- flex-nowrap: Agar tidak turun ke bawah -->
                <!-- items-stretch: Agar tinggi semua kolom sama -->
                <div class="flex flex-nowrap overflow-x-auto gap-3 pb-4 items-stretch min-h-[calc(100vh-250px)]">

                    <!-- SETTING LEBAR KOLOM (Responsive) -->
                    <!-- w-[85vw] : Di HP lebar hampir full (85%) agar fokus -->
                    <!-- md:w-1/2 : Di Tablet lebar 50% (2 kolom) -->
                    <!-- lg:w-1/3 : Di Desktop lebar 33.3% (3 kolom pas) -->
                    <!-- flex-none : Mencegah kolom mengecil/gepeng -->
                    
                    @php
                        $columnClass = "flex-none w-[85vw] md:w-1/2 lg:w-[calc(100%/3-10px)]";
                    @endphp

                    <!-- 1. To Be Scheduled -->
                    <div class="{{ $columnClass }}">
                        <div class="flex flex-col rounded-lg shadow-lg h-full bg-gray-100 dark:bg-gray-700">
                            <div class="bg-gray-500 dark:bg-gray-600 p-3 rounded-t-lg">
                                <h3 class="text-sm font-bold text-white text-center uppercase">To Be Scheduled</h3>
                            </div>
                            <div id="to-be-scheduled-column" class="p-3 space-y-3 kanban-column-body flex-1">
                                @forelse($toBeScheduledJobs as $job)
                                    @include('jobs.partials.job_card', ['job' => $job])
                                @empty
                                    <div class="flex items-center justify-center h-20">
                                        <p class="text-center text-xs text-gray-500">No jobs.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- 2. Scheduled -->
                    <div class="{{ $columnClass }}">
                        <div class="flex flex-col rounded-lg shadow-lg h-full bg-indigo-50 dark:bg-gray-700">
                            <div class="bg-indigo-500 dark:bg-indigo-600 p-3 rounded-t-lg">
                                <h3 class="text-sm font-bold text-white text-center uppercase">Scheduled</h3>
                            </div>
                            <div id="scheduled-column" class="p-3 space-y-3 kanban-column-body flex-1">
                                @forelse($scheduledJobs as $job)
                                    @include('jobs.partials.job_card', ['job' => $job])
                                @empty
                                    <div class="flex items-center justify-center h-20">
                                        <p class="text-center text-xs text-gray-500">No jobs.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- 3. Preparation -->
                    <div class="{{ $columnClass }}">
                        <div class="flex flex-col rounded-lg shadow-lg h-full bg-yellow-50 dark:bg-gray-700">
                            <div class="bg-yellow-500 dark:bg-yellow-600 p-3 rounded-t-lg">
                                <h3 class="text-sm font-bold text-white text-center uppercase">Preparation</h3>
                            </div>
                            <div id="preparation-column" class="p-3 space-y-3 kanban-column-body flex-1">
                                @forelse($preparationJobs as $job)
                                    @include('jobs.partials.job_card', ['job' => $job])
                                @empty
                                    <div class="flex items-center justify-center h-20">
                                        <p class="text-center text-xs text-gray-500">No jobs.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- 4. On Going -->
                    <div class="{{ $columnClass }}">
                        <div class="flex flex-col rounded-lg shadow-lg h-full bg-blue-100 dark:bg-gray-700">
                            <div class="bg-blue-600 dark:bg-blue-700 p-3 rounded-t-lg">
                                <h3 class="text-sm font-bold text-white text-center uppercase">On Going</h3>
                            </div>
                            <div id="on-going-column" class="p-3 space-y-3 kanban-column-body flex-1">
                                @forelse($onGoingJobs as $job)
                                    @include('jobs.partials.job_card', ['job' => $job])
                                @empty
                                    <div class="flex items-center justify-center h-20">
                                        <p class="text-center text-xs text-gray-500">No jobs.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- 5. Completed -->
                    <div class="{{ $columnClass }}">
                        <div class="flex flex-col rounded-lg shadow-lg h-full bg-green-100 dark:bg-gray-700">
                            <div class="bg-green-600 dark:bg-green-700 p-3 rounded-t-lg">
                                <h3 class="text-sm font-bold text-white text-center uppercase">Completed</h3>
                            </div>
                            <div id="completed-column" class="p-3 space-y-3 kanban-column-body flex-1">
                                @forelse($completedJobs as $job)
                                    @include('jobs.partials.job_card', ['job' => $job])
                                @empty
                                    <div class="flex items-center justify-center h-20">
                                        <p class="text-center text-xs text-gray-500">No jobs.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- 6. Closed -->
                    <div class="{{ $columnClass }}">
                        <div class="flex flex-col rounded-lg shadow-lg h-full bg-gray-200 dark:bg-gray-800">
                            <div class="bg-gray-800 dark:bg-black p-3 rounded-t-lg">
                                <h3 class="text-sm font-bold text-white text-center uppercase">Closed</h3>
                            </div>
                            <div id="closed-column" class="p-3 space-y-3 kanban-column-body flex-1">
                                @forelse($closedJobs as $job)
                                    @include('jobs.partials.job_card', ['job' => $job])
                                @empty
                                    <div class="flex items-center justify-center h-20">
                                        <p class="text-center text-xs text-gray-500">No jobs.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    @include('jobs.modals.create')
    @include('jobs.modals.forward')
    @include('jobs.modals.complete')
    @include('jobs.modals.close')

    <div id="global-spinner" class="hidden fixed inset-0 z-50 bg-black bg-opacity-60 flex items-center justify-center">
        <div class="flex flex-col items-center">
            <div class="w-16 h-16 border-4 border-white border-t-blue-500 rounded-full animate-spin"></div>
            <p class="text-white text-lg mt-4">Processing...</p>
        </div>
    </div>

    @push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .kanban-column-body { 
            /* Perubahan di sini: hapus min-height 400px agar mengikuti tinggi parent */
            overflow-y: auto; 
            /* scrollbar custom agar cantik */
            scrollbar-width: thin;
        }
        .job_card { transition: opacity 0.3s ease-in-out; }
        
        /* Custom Scrollbar untuk Container Utama */
        .overflow-x-auto::-webkit-scrollbar { height: 12px; }
        .overflow-x-auto::-webkit-scrollbar-track { background: #e5e7eb; border-radius: 6px; }
        .overflow-x-auto::-webkit-scrollbar-thumb { background: #9ca3af; border-radius: 6px; }
        .overflow-x-auto::-webkit-scrollbar-thumb:hover { background: #6b7280; }

        /* Custom Scrollbar untuk Kolom */
        .kanban-column-body::-webkit-scrollbar { width: 6px; }
        .kanban-column-body::-webkit-scrollbar-track { background: transparent; }
        .kanban-column-body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    </style>
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/js/app.js'])

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const spinner = document.getElementById('global-spinner');
        const showSpinner = () => spinner.classList.remove('hidden');
        const hideSpinner = () => spinner.classList.add('hidden');

        function updateKanbanUI(job, html) {
            const oldCard = document.getElementById(`job-card-${job.id}`);
            if (oldCard) oldCard.remove();

            const targetStatus = job.status.replace(/_/g, '-');
            const targetColumn = document.getElementById(`${targetStatus}-column`);

            if (targetColumn) {
                const placeholder = targetColumn.querySelector('.no-jobs-placeholder') || targetColumn.querySelector('.text-center.text-xs');
                if (placeholder) placeholder.closest('div')?.remove() || placeholder.remove();

                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                targetColumn.insertAdjacentElement('afterbegin', tempDiv.firstChild);
            }
        }

        async function handleFormSubmit(url, formData) {
            showSpinner();
            try {
                const response = await fetch(url, {
                    method: 'POST', body: formData,
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                });
                const data = await response.json();
                if (!response.ok) {
                    let errorHtml = data.message || 'An unknown error occurred.';
                    if (response.status === 422 && data.errors) {
                        errorHtml = '<ul class="text-left list-disc list-inside mt-2">';
                        for (const field in data.errors) { errorHtml += `<li>${data.errors[field][0]}</li>`; }
                        errorHtml += '</ul>';
                    }
                    Swal.fire({ icon: 'error', title: 'Operation Failed', html: errorHtml });
                    return;
                }
                Swal.fire({
                    toast: true, position: 'top-end', icon: 'success',
                    title: data.message, showConfirmButton: false, timer: 3000
                });
                
                if (!window.Echo) {
                     updateKanbanUI(data.job, data.html);
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire('Error', 'Could not connect to the server.', 'error');
            } finally {
                hideSpinner();
            }
        }

        if (window.Echo) {
            window.Echo.channel('jobs')
                .listen('JobUpdated', (data) => {
                    updateKanbanUI(data.job, data.html);
                });
        }

        function openModal(modalEl) { if (modalEl) modalEl.classList.remove('hidden'); }
        function closeModal(modalEl) { if (modalEl) modalEl.classList.add('hidden'); }

        const fileHandlers = new Map();
        function setupAdvancedFileInput(modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) return;
            const triggerButton = modal.querySelector('.trigger-file-input');
            const fileInput = modal.querySelector('.file-input');
            const previewContainer = modal.querySelector('.file-preview-container');
            let selectedFiles = new Map();
            fileHandlers.set(modalId, selectedFiles);

            triggerButton.addEventListener('click', () => fileInput.click());
            
            fileInput.addEventListener('change', (event) => {
                const files = event.target.files;
                let currentFileCount = selectedFiles.size;
                for (const file of files) {
                    if (currentFileCount >= 3) {
                        Swal.fire('Limit Reached', 'Max 3 files.', 'warning');
                        break;
                    }
                    if (!selectedFiles.has(file.name)) {
                        selectedFiles.set(file.name, file);
                        const wrapper = document.createElement('div');
                        wrapper.className = 'flex items-center justify-between p-2 bg-gray-100 dark:bg-gray-700 rounded-md';
                        wrapper.innerHTML = `<span class="truncate text-sm">${file.name}</span><button type="button" class="text-red-500" onclick="this.parentElement.remove()">x</button>`;
                        wrapper.querySelector('button').addEventListener('click', () => selectedFiles.delete(file.name));
                        previewContainer.appendChild(wrapper);
                        currentFileCount++;
                    }
                }
                fileInput.value = '';
            });
        }

        setupAdvancedFileInput('createJobModal');
        setupAdvancedFileInput('completeJobModal');

        document.getElementById('openCreateJobModalBtn')?.addEventListener('click', () => openModal(document.getElementById('createJobModal')));
        document.querySelectorAll('.cancel-modal-btn').forEach(btn => btn.addEventListener('click', () => closeModal(btn.closest('.fixed'))));

        document.getElementById('createJobForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fileHandlers.get('createJobModal')?.forEach(file => formData.append('attachments[]', file));
            closeModal(document.getElementById('createJobModal'));
            handleFormSubmit('{{ route("jobs.store") }}', formData);
            this.reset();
            document.querySelector('#createJobModal .file-preview-container').innerHTML = '';
            fileHandlers.get('createJobModal').clear();
        });

        document.getElementById('forwardJobForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const jobId = this.querySelector('#forward_job_id').value;
            closeModal(document.getElementById('forwardJobModal'));
            handleFormSubmit(`/jobs/${jobId}/forward`, new FormData(this));
            this.reset();
        });

        document.getElementById('completeJobForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const jobId = this.querySelector('#complete_job_id').value;
            const formData = new FormData(this);
            fileHandlers.get('completeJobModal')?.forEach(file => formData.append('attachments[]', file));
            formData.append('_method', 'PATCH');
            closeModal(document.getElementById('completeJobModal'));
            handleFormSubmit(`/jobs/${jobId}/complete`, formData);
            this.reset();
            document.querySelector('#completeJobModal .file-preview-container').innerHTML = '';
            fileHandlers.get('completeJobModal').clear();
        });

        document.getElementById('closeJobForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const jobId = this.querySelector('#close_job_id').value;
            closeModal(document.getElementById('closeJobModal'));
            handleFormSubmit(`/jobs/${jobId}/close`, new FormData(this));
        });

        document.body.addEventListener('click', function(e) {
            const target = e.target.closest('button[data-job-id]');
            if (!target) return;
            const jobId = target.dataset.jobId;
            e.preventDefault();
            const formData = new FormData();
            formData.append('_method', 'PATCH');
            formData.append('_token', csrfToken);

            if (target.classList.contains('schedule-job-btn')) {
                handleFormSubmit(`/jobs/${jobId}/schedule`, formData);
            } else if (target.classList.contains('prepare-job-btn')) {
                handleFormSubmit(`/jobs/${jobId}/prepare`, formData);
            } else if (target.classList.contains('start-job-btn')) {
                handleFormSubmit(`/jobs/${jobId}/start`, formData);
            } else if (target.classList.contains('forward-job-btn')) {
                const modal = document.getElementById('forwardJobModal');
                modal.querySelector('#forward_job_id').value = jobId;
                openModal(modal);
            } else if (target.classList.contains('complete-job-btn')) {
                const modal = document.getElementById('completeJobModal');
                modal.querySelector('#complete_job_id').value = jobId;
                openModal(modal);
            } else if (target.classList.contains('close-job-btn')) {
                const modal = document.getElementById('closeJobModal');
                modal.querySelector('#close_job_id').value = jobId;
                openModal(modal);
            }
        });
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    @endpush
</x-app-layout>