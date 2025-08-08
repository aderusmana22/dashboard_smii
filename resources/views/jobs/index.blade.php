<x-app-layout>
    {{-- Slot Header --}}
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

    {{-- Main Content --}}
    <div class="py-8 md:py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden">
                {{-- Add New Job Button --}}
                <div class="flex justify-end items-center mb-8">
                    <button id="openCreateJobModalBtn"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
                            @if($areas->isEmpty() || $departments->isEmpty()) disabled title="Cannot add job: Areas or Departments are not configured." @endif>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-2 -mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Add New Job
                    </button>
                </div>

                {{-- Kanban Board Container --}}
                <div class="flex overflow-x-auto gap-6 pb-4">

                    {{-- OPEN Column --}}
                    <div class="flex-shrink-0 w-[85vw] sm:w-[48vw] md:w-[48vw] lg:w-[33%]">
                        <div class="flex flex-col rounded-lg shadow-lg h-full bg-gray-100 dark:bg-gray-700">
                            <div class="bg-gray-500 dark:bg-gray-600 p-3 rounded-t-lg">
                                <h3 class="text-lg font-semibold text-white text-center tracking-wide">OPEN</h3>
                            </div>
                            <div id="open-column" class="p-4 space-y-4 kanban-column-body flex-1">
                                @forelse($openJobs as $job)
                                    @include('jobs.partials.job_card', ['job' => $job])
                                @empty
                                    <p class="no-jobs-placeholder text-center text-gray-500 dark:text-gray-400 py-4">No jobs available.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- ON PROCESS Column --}}
                    <div class="flex-shrink-0 w-[85vw] sm:w-[48vw] md:w-[48vw] lg:w-[32%]">
                        <div class="flex flex-col rounded-lg shadow-lg h-full bg-blue-100 dark:bg-gray-700">
                            <div class="bg-blue-500 dark:bg-blue-600 p-3 rounded-t-lg">
                                <h3 class="text-lg font-semibold text-white text-center tracking-wide">ON PROCESS</h3>
                            </div>
                            <div id="on-process-column" class="p-4 space-y-4 kanban-column-body flex-1">
                                @forelse($onProcessJobs as $job)
                                    @include('jobs.partials.job_card', ['job' => $job])
                                @empty
                                    <p class="no-jobs-placeholder text-center text-gray-500 dark:text-gray-400 py-4">No jobs available.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- COMPLETED Column --}}
                    <div class="flex-shrink-0 w-[85vw] sm:w-[48vw] md:w-[48vw] lg:w-[32%]">
                        <div class="flex flex-col rounded-lg shadow-lg h-full bg-green-100 dark:bg-gray-700">
                            <div class="bg-green-600 dark:bg-green-700 p-3 rounded-t-lg">
                                <h3 class="text-lg font-semibold text-white text-center tracking-wide">COMPLETED</h3>
                            </div>
                            <div id="completed-column" class="p-4 space-y-4 kanban-column-body flex-1">
                                @forelse($completedJobs as $job)
                                    @include('jobs.partials.job_card', ['job' => $job])
                                @empty
                                    <p class="no-jobs-placeholder text-center text-gray-500 dark:text-gray-400 py-4">No jobs available.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- CLOSED Column --}}
                    <div class="flex-shrink-0 w-[85vw] sm:w-[48vw] md:w-[48vw] lg:w-[32%]">
                        <div class="flex flex-col rounded-lg shadow-lg h-full bg-gray-200 dark:bg-gray-800">
                            <div class="bg-gray-600 dark:bg-gray-900 p-3 rounded-t-lg">
                                <h3 class="text-lg font-semibold text-white text-center tracking-wide">CLOSED</h3>
                            </div>
                            <div id="closed-column" class="p-4 space-y-4 kanban-column-body flex-1">
                                @forelse($closedJobs as $job)
                                    @include('jobs.partials.job_card', ['job' => $job])
                                @empty
                                    <p class="no-jobs-placeholder text-center text-gray-500 dark:text-gray-400 py-4">No jobs available.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Include all Modals from their partial files --}}
    @include('jobs.modals.create')
    @include('jobs.modals.forward')
    @include('jobs.modals.complete')
    @include('jobs.modals.close')

    <!-- PERUBAHAN: Tambahkan elemen spinner di sini -->
    <div id="global-spinner" class="hidden fixed inset-0 z-50 bg-black bg-opacity-60 flex items-center justify-center">
        <div class="flex flex-col items-center">
            <div class="w-16 h-16 border-4 border-white border-t-blue-500 rounded-full animate-spin"></div>
            <p class="text-white text-lg mt-4">Processing...</p>
        </div>
    </div>

    @push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .kanban-column-body { min-height: 400px; max-height: calc(100vh - 300px); overflow-y: auto; }
        .job_card { transition: opacity 0.3s ease-in-out; }
    </style>
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- This line includes your compiled app.js, which contains the configured Echo instance --}}
    @vite(['resources/js/app.js'])

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // --- PERUBAHAN: Definisikan spinner dan fungsi untuk mengontrolnya ---
        const spinner = document.getElementById('global-spinner');
        const showSpinner = () => spinner.classList.remove('hidden');
        const hideSpinner = () => spinner.classList.add('hidden');
        // --- Akhir Perubahan ---

        // --- CORE UI UPDATE FUNCTION ---
        function updateKanbanUI(job, html) {
            const oldCard = document.getElementById(`job-card-${job.id}`);
            const sourceColumn = oldCard ? oldCard.parentElement : null;

            if (oldCard) {
                oldCard.style.opacity = '0';
                setTimeout(() => oldCard.remove(), 300);
            }

            const targetStatus = job.status.replace('_', '-');
            const targetColumn = document.getElementById(`${targetStatus}-column`);

            if (targetColumn) {
                const tempContainer = document.createElement('div');
                tempContainer.innerHTML = html;
                const newCard = tempContainer.firstChild;
                newCard.style.opacity = '0';

                if (targetStatus === 'closed') {
                    targetColumn.insertAdjacentElement('beforeend', newCard);
                } else {
                    targetColumn.insertAdjacentElement('afterbegin', newCard);
                }
                setTimeout(() => newCard.style.opacity = '1', 50);
            }

            if (sourceColumn) checkPlaceholder(sourceColumn);
            if (targetColumn) checkPlaceholder(targetColumn);
        }

        function checkPlaceholder(columnEl) {
            if (!columnEl) return;
            const placeholder = columnEl.querySelector('.no-jobs-placeholder');
            const cards = columnEl.querySelectorAll('.job_card');
            if (placeholder) {
                placeholder.style.display = (cards.length === 0) ? 'block' : 'none';
            }
        }

        // --- AJAX FORM SUBMISSION HANDLER ---
        // --- PERUBAHAN: Tambahkan kontrol spinner di sini ---
        async function handleFormSubmit(url, formData) {
            showSpinner(); // Tampilkan spinner sebelum request
            try {
                const response = await fetch(url, {
                    method: 'POST', body: formData,
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                });
                const data = await response.json();
                if (!response.ok) {
                    let errorHtml = data.message || 'An unknown error occurred.';
                    if (response.status === 422 && data.errors) {
                        errorHtml = 'Please fix the following errors:<br><ul class="text-left list-disc list-inside mt-2">';
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
            } catch (error) {
                console.error('Form submission error:', error);
                Swal.fire('Error', 'Could not connect to the server.', 'error');
            } finally {
                hideSpinner(); // Selalu sembunyikan spinner setelah selesai (baik sukses maupun error)
            }
        }
        // --- Akhir Perubahan ---

        // --- REAL-TIME BROADCAST LISTENER ---
        if (window.Echo) {
            window.Echo.channel('jobs')
                .listen('JobUpdated', (data) => {
                    console.log('Real-time event received:', data);
                    // Jangan tampilkan spinner untuk update real-time agar tidak mengganggu
                    updateKanbanUI(data.job, data.html);
                });
        }

        // --- UTILITY AND SETUP FUNCTIONS ---
        function openModal(modalEl) { if (modalEl) modalEl.classList.remove('hidden'); }
        function closeModal(modalEl) { if (modalEl) modalEl.classList.add('hidden'); }

        const fileHandlers = new Map();
        function setupAdvancedFileInput(modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) return;
            const triggerButton = modal.querySelector('.trigger-file-input');
            if (!triggerButton) return;
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
                        Swal.fire('Limit Reached', 'You can only upload a maximum of 3 files.', 'warning');
                        break;
                    }
                    if (!selectedFiles.has(file.name)) {
                        selectedFiles.set(file.name, file);
                        previewContainer.appendChild(createPreviewElement(file));
                        currentFileCount++;
                    }
                }
                fileInput.value = '';
            });

            previewContainer.addEventListener('click', (event) => {
                const removeBtn = event.target.closest('.remove-file-btn');
                if (removeBtn) {
                    const filename = removeBtn.dataset.filename;
                    selectedFiles.delete(filename);
                    removeBtn.parentElement.parentElement.remove();
                }
            });
        }

        function createPreviewElement(file) {
            const previewWrapper = document.createElement('div');
            previewWrapper.className = 'flex items-center justify-between p-2 bg-gray-100 dark:bg-gray-700 rounded-md';
            const fileInfo = document.createElement('div');
            fileInfo.className = 'flex items-center space-x-2 overflow-hidden';
            const fileIcon = document.createElement('div');
            fileIcon.className = 'flex-shrink-0';
            if (file.type.startsWith('image/')) {
                const img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                img.className = 'w-10 h-10 object-cover rounded';
                img.onload = () => URL.revokeObjectURL(img.src);
                fileIcon.appendChild(img);
            } else {
                fileIcon.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>`;
            }
            const fileNameSpan = document.createElement('span');
            fileNameSpan.className = 'text-sm text-gray-800 dark:text-gray-200 truncate';
            fileNameSpan.textContent = file.name;
            fileInfo.appendChild(fileIcon);
            fileInfo.appendChild(fileNameSpan);
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.dataset.filename = file.name;
            removeBtn.className = 'remove-file-btn flex-shrink-0 text-red-500 hover:text-red-700';
            removeBtn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>`;
            previewWrapper.appendChild(fileInfo);
            previewWrapper.appendChild(removeBtn);
            return previewWrapper;
        }

        // --- EVENT LISTENER INITIALIZATION ---
        setupAdvancedFileInput('createJobModal');
        setupAdvancedFileInput('completeJobModal');

        document.getElementById('openCreateJobModalBtn')?.addEventListener('click', () => {
            if (document.getElementById('openCreateJobModalBtn').disabled) return;
            openModal(document.getElementById('createJobModal'));
        });
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

            if (target.classList.contains('start-job-btn')) {
                const formData = new FormData();
                formData.append('_method', 'PATCH');
                formData.append('_token', csrfToken);
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
    @endpush
</x-app-layout>