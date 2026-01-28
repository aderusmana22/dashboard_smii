<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-4 md:mb-0">
                {{ __('Job Kanban Board') }}
            </h2>
             @if(isset($user))
                <div class="text-sm text-gray-600">
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
                    <button id="openCreateJobModalBtn"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
                            @if($areas->isEmpty() || $departments->isEmpty()) disabled title="Cannot add job: Areas or Departments are not configured." @endif>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-2 -mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Add New Job
                    </button>
                </div>

                <div class="flex flex-nowrap overflow-x-auto gap-4 pb-4 items-stretch min-h-[calc(100vh-250px)] px-2">
                    @php 
                        $columnClass = "flex-none w-[85vw] md:w-1/2 lg:w-[calc(100%/3-10px)] flex flex-col"; 
                    @endphp

                    <div class="{{ $columnClass }}">
                        <div class="flex flex-col rounded-xl shadow-md h-full bg-rose-50 border border-rose-100 overflow-hidden kanban-card-container">
                            <div class="bg-rose-500 p-3 text-center">
                                <h3 class="text-sm font-bold text-white uppercase tracking-wider">To Be Scheduled</h3>
                            </div>
                            <div id="to-be-scheduled-column" class="p-3 space-y-3 kanban-column-body flex-1">
                                @forelse($toBeScheduledJobs as $job) 
                                    @include('jobs.partials.job_card', ['job' => $job]) 
                                @empty 
                                    <div class="flex items-center justify-center h-full opacity-60">
                                        <p class="text-xs text-rose-800 font-bold empty-text">No Jobs.</p>
                                    </div> 
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="{{ $columnClass }}">
                        <div class="flex flex-col rounded-xl shadow-md h-full bg-sky-50 border border-sky-100 overflow-hidden kanban-card-container">
                            <div class="bg-sky-500 p-3 text-center">
                                <h3 class="text-sm font-bold text-white uppercase tracking-wider">Scheduled</h3>
                            </div>
                            <div id="scheduled-column" class="p-3 space-y-3 kanban-column-body flex-1">
                                @forelse($scheduledJobs as $job) 
                                    @include('jobs.partials.job_card', ['job' => $job]) 
                                @empty 
                                    <div class="flex items-center justify-center h-full opacity-60">
                                        <p class="text-xs text-sky-800 font-bold empty-text">No Jobs.</p>
                                    </div> 
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="{{ $columnClass }}">
                        <div class="flex flex-col rounded-xl shadow-md h-full bg-purple-50 border border-purple-100 overflow-hidden kanban-card-container">
                            <div class="bg-purple-500 p-3 text-center">
                                <h3 class="text-sm font-bold text-white uppercase tracking-wider">Preparation</h3>
                            </div>
                            <div id="preparation-column" class="p-3 space-y-3 kanban-column-body flex-1">
                                @forelse($preparationJobs as $job) 
                                    @include('jobs.partials.job_card', ['job' => $job]) 
                                @empty 
                                    <div class="flex items-center justify-center h-full opacity-60">
                                        <p class="text-xs text-purple-800 font-bold empty-text">No Jobs.</p>
                                    </div> 
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="{{ $columnClass }}">
                        <div class="flex flex-col rounded-xl shadow-md h-full bg-amber-50 border border-amber-100 overflow-hidden kanban-card-container">
                            <div class="bg-amber-500 p-3 text-center">
                                <h3 class="text-sm font-bold text-white uppercase tracking-wider">On Going</h3>
                            </div>
                            <div id="on-going-column" class="p-3 space-y-3 kanban-column-body flex-1">
                                @forelse($onGoingJobs as $job) 
                                    @include('jobs.partials.job_card', ['job' => $job]) 
                                @empty 
                                    <div class="flex items-center justify-center h-full opacity-60">
                                        <p class="text-xs text-amber-800 font-bold empty-text">No Jobs.</p>
                                    </div> 
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="{{ $columnClass }}">
                        <div class="flex flex-col rounded-xl shadow-md h-full bg-emerald-50 border border-emerald-100 overflow-hidden kanban-card-container">
                            <div class="bg-emerald-500 p-3 text-center">
                                <h3 class="text-sm font-bold text-white uppercase tracking-wider">Completed</h3>
                            </div>
                            <div id="completed-column" class="p-3 space-y-3 kanban-column-body flex-1">
                                @forelse($completedJobs as $job) 
                                    @include('jobs.partials.job_card', ['job' => $job]) 
                                @empty 
                                    <div class="flex items-center justify-center h-full opacity-60">
                                        <p class="text-xs text-emerald-800 font-bold empty-text">No Jobs.</p>
                                    </div> 
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="{{ $columnClass }}">
                        <div class="flex flex-col rounded-xl shadow-md h-full bg-cyan-50 border border-cyan-100 overflow-hidden kanban-card-container">
                            <div class="bg-cyan-600 p-3 text-center">
                                <h3 class="text-sm font-bold text-white uppercase tracking-wider">Closed</h3>
                            </div>
                            <div id="closed-column" class="p-3 space-y-3 kanban-column-body flex-1">
                                @forelse($closedJobs as $job) 
                                    @include('jobs.partials.job_card', ['job' => $job]) 
                                @empty 
                                    <div class="flex items-center justify-center h-full opacity-60">
                                        <p class="text-xs text-cyan-800 font-bold empty-text">No Jobs.</p>
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
    @include('jobs.modals.move_stage')
    @include('jobs.modals.forward')
    @include('jobs.modals.complete')
    @include('jobs.modals.close')
    @include('jobs.modals.detail')
    @include('jobs.modals.cancel')

    <div id="global-spinner" class="hidden fixed inset-0 z-50 bg-black bg-opacity-60 flex items-center justify-center">
        <div class="flex flex-col items-center">
            <div class="w-16 h-16 border-4 border-white border-t-blue-500 rounded-full animate-spin"></div>
            <p class="text-white text-lg mt-4">Processing...</p>
        </div>
    </div>


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
     <style>
        .kanban-column-body { overflow-y: auto; scrollbar-width: thin; }
        .overflow-x-auto::-webkit-scrollbar { height: 12px; }
        .overflow-x-auto::-webkit-scrollbar-track { background: #e5e7eb; border-radius: 6px; }
        .overflow-x-auto::-webkit-scrollbar-thumb { background: #9ca3af; border-radius: 6px; }
        .overflow-x-auto::-webkit-scrollbar-thumb:hover { background: #6b7280; }
        .kanban-column-body::-webkit-scrollbar { width: 6px; }
        .kanban-column-body::-webkit-scrollbar-track { background: transparent; }
        .kanban-column-body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

        .dark-skin .overflow-x-auto::-webkit-scrollbar-track { background: #374151; }
        .dark-skin .overflow-x-auto::-webkit-scrollbar-thumb { background: #6b7280; }
        .dark-skin .kanban-column-body::-webkit-scrollbar-thumb { background: #4b5563; }

        .dark-skin {
            background-color: rgb(17 24 39); 
            color: rgb(243 244 246);       
        }

        .dark-skin .bg-white {
            background-color: rgb(31 41 55 / var(--tw-bg-opacity, 1)); 
            color: rgb(229 231 235); 
            border-color: rgb(55 65 81); 
        }

        .dark-skin .bg-gray-50, 
        .dark-skin .bg-gray-50\/50 {
            background-color: rgb(17 24 39 / var(--tw-bg-opacity, 1)) !important; 
        }

        .dark-skin .text-gray-900 { color: rgb(243 244 246) !important; } 
        .dark-skin .text-gray-800 { color: rgb(229 231 235) !important; } 
        .dark-skin .text-gray-700 { color: rgb(209 213 219) !important; } 
        .dark-skin .text-gray-600 { color: rgb(156 163 175) !important; } 
        .dark-skin .text-gray-500 { color: rgb(156 163 175) !important; } 
        .dark-skin .text-gray-400 { color: rgb(107 114 128) !important; } 

        .dark-skin .border-gray-100,
        .dark-skin .border-gray-200, 
        .dark-skin .border-gray-300 {
            border-color: rgb(55 65 81 / var(--tw-border-opacity, 1)) !important; 
        }
        .dark-skin .divide-gray-200 > :not([hidden]) ~ :not([hidden]) {
            border-color: rgb(55 65 81); 
        }

        .dark-skin input[type="text"],
        .dark-skin input[type="date"],
        .dark-skin input[type="file"],
        .dark-skin select,
        .dark-skin textarea {
            background-color: rgb(55 65 81); 
            border-color: rgb(75 85 99);     
            color: rgb(243 244 246);         
        }
        .dark-skin input::placeholder,
        .dark-skin textarea::placeholder {
            color: rgb(156 163 175); 
        }

        .dark-skin .bg-gray-100 {
            background-color: rgb(55 65 81); 
            border-color: rgb(75 85 99);
        }

        .dark-skin .bg-gray-200, 
        .dark-skin .modal-cancel-button {
             background-color: rgb(75 85 99); 
             color: rgb(243 244 246); 
        }
        .dark-skin .bg-gray-200:hover,
        .dark-skin .modal-cancel-button:hover {
             background-color: rgb(107 114 128); 
        }

        .dark-skin .bg-rose-50 { background-color: rgb(88 28 135 / 0.15) !important; border-color: rgb(136 19 55 / 0.5) !important; }
        .dark-skin .text-rose-800 { color: rgb(251 113 133) !important; } 

        .dark-skin .bg-sky-50 { background-color: rgb(12 74 110 / 0.2) !important; border-color: rgb(14 165 233 / 0.3) !important; }
        .dark-skin .text-sky-800 { color: rgb(56 189 248) !important; } 

        .dark-skin .bg-purple-50 { background-color: rgb(88 28 135 / 0.2) !important; border-color: rgb(168 85 247 / 0.3) !important; }
        .dark-skin .text-purple-800 { color: rgb(192 132 252) !important; } 

        .dark-skin .bg-amber-50 { background-color: rgb(120 53 15 / 0.2) !important; border-color: rgb(245 158 11 / 0.3) !important; }
        .dark-skin .text-amber-800 { color: rgb(251 191 36) !important; } 

        .dark-skin .bg-emerald-50 { background-color: rgb(6 78 59 / 0.2) !important; border-color: rgb(16 185 129 / 0.3) !important; }
        .dark-skin .text-emerald-800 { color: rgb(52 211 153) !important; } 

        .dark-skin .bg-cyan-50 { background-color: rgb(22 78 99 / 0.2) !important; border-color: rgb(6 182 212 / 0.3) !important; }
        .dark-skin .text-cyan-800 { color: rgb(34 211 238) !important; } 

        .dark-skin .text-blue-600 { color: #60a5fa !important; } 
        .dark-skin .text-blue-600:hover { color: #93c5fd !important; }

        .dark-skin .group:hover .bg-white {
            background-color: rgb(31 41 55); 
        }

        .dark-skin .bg-blue-50 {
    background-color: rgb(30 64 175 / 0.25) !important; 
    border-color: rgb(59 130 246 / 0.4) !important;   
}
.dark-skin .text-blue-800 {
    color: rgb(147 197 253) !important; 
}

.dark-skin .file\:bg-blue-50 {
    background-color: rgb(30 58 138) !important; 
}
.dark-skin .file\:text-blue-700 {
    color: rgb(191 219 254) !important; 
}
.dark-skin .hover\:file\:bg-blue-100:hover {
    background-color: rgb(29 78 216) !important; 
}

.dark-skin .bg-yellow-200 {
    background-color: rgb(120 53 15 / 0.7) !important; 
}
.dark-skin .text-yellow-800 {
    color: rgb(252 211 77) !important; 
}

.dark-skin .text-purple-900 {
    color: rgb(216 180 254) !important; 
}

.dark-skin .bg-gray-900\/50 {
    background-color: rgba(17, 24, 39, 0.75) !important; 
}

.dark-skin .bg-blue-100 {
    background-color: rgb(30 64 175 / 0.3) !important; 
}

.dark-skin .ring-white {
    --tw-ring-color: rgb(31 41 55) !important; 
}

.dark-skin .text-yellow-700 {
    color: rgb(250 204 21) !important; 
}
.dark-skin .text-blue-700 {
    color: rgb(96 165 250) !important; 
}

.dark-skin .border-yellow-400 {
    border-color: rgb(250 204 21 / 0.7) !important; 
}
.dark-skin .border-blue-400 {
    border-color: rgb(96 165 250 / 0.7) !important; 
}

.dark-skin .custom-scrollbar::-webkit-scrollbar-thumb {
    background: #4b5563; 
}
.dark-skin .custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #6b7280; 
}

.dark-skin .bg-rose-100 { background-color: rgb(136 19 55 / 0.3) !important; }
.dark-skin .text-rose-800 { color: rgb(251 113 133) !important; }

.dark-skin .bg-sky-100 { background-color: rgb(14 116 144 / 0.3) !important; }
.dark-skin .text-sky-800 { color: rgb(56 189 248) !important; }

.dark-skin .bg-purple-100 { background-color: rgb(107 33 168 / 0.3) !important; }
.dark-skin .text-purple-800 { color: rgb(192 132 252) !important; }

.dark-skin .bg-amber-100 { background-color: rgb(180 83 9 / 0.3) !important; }
.dark-skin .text-amber-800 { color: rgb(251 191 36) !important; }

.dark-skin .bg-emerald-100 { background-color: rgb(5 102 63 / 0.3) !important; }
.dark-skin .text-emerald-800 { color: rgb(52 211 153) !important; }

.dark-skin .bg-cyan-100 { background-color: rgb(21 94 117 / 0.3) !important; }
.dark-skin .text-cyan-800 { color: rgb(34 211 238) !important; }

.dark-skin .bg-red-100 { background-color: rgb(153 27 27 / 0.3) !important; }
.dark-skin .text-red-800 { color: rgb(248 113 113) !important; }

.dark-skin .ring-white {
    --tw-ring-color: rgb(31 41 55) !important; 
}

.dark-skin .text-yellow-700 { color: rgb(250 204 21) !important; }
.dark-skin .text-blue-700 { color: rgb(96 165 250) !important; }

.dark-skin .border-yellow-400 { border-color: rgb(250 204 21 / 0.7) !important; }
.dark-skin .border-blue-400 { border-color: rgb(96 165 250 / 0.7) !important; }

.dark-skin .bg-white\/50 {
    background-color: rgb(17 24 39 / 0.7) !important; 
}

.dark-skin .text-gray-800 {
    color: rgb(229 231 235) !important; 
}
.dark-skin .hover\:bg-white\/80:hover {
    background-color: rgb(55 65 81 / 0.8) !important; 
}

    </style>

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
                const placeholder = targetColumn.querySelector('.text-center.text-xs');
                if (placeholder && placeholder.closest('div.flex')) placeholder.closest('div.flex').remove();

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
                    let errorHtml = data.message || 'Error occurred.';
                    if (response.status === 422 && data.errors) {
                        errorHtml = '<ul class="text-left list-disc list-inside mt-2">';
                        for (const field in data.errors) { errorHtml += `<li>${data.errors[field][0]}</li>`; }
                        errorHtml += '</ul>';
                    }
                    Swal.fire({ icon: 'error', title: 'Failed', html: errorHtml });
                    return;
                }

                Swal.fire({
                    toast: true, position: 'top-end', icon: 'success',
                    title: data.message, showConfirmButton: false, timer: 3000
                });

                if (!window.Echo) updateKanbanUI(data.job, data.html);
            } catch (error) {
                console.error('Error:', error);
                Swal.fire('Error', 'Connection failed.', 'error');
            } finally {
                hideSpinner();
            }
        }

        if (window.Echo) {
            window.Echo.channel('jobs').listen('JobUpdated', (data) => updateKanbanUI(data.job, data.html));
        }

        function openModal(id) { 
            const el = document.getElementById(id);
            if(el) el.classList.remove('hidden'); 
            else console.error('Modal not found:', id);
        }
        function closeModal(id) { document.getElementById(id)?.classList.add('hidden'); }

        document.body.addEventListener('click', function(e) {

            const moveBtn = e.target.closest('.move-stage-btn');

if (moveBtn) {
    e.preventDefault();
    const jobId = moveBtn.dataset.jobId;
    const targetStatus = moveBtn.dataset.targetStatus;
    const title = moveBtn.dataset.title;

    const modal = document.getElementById('moveStageModal');
    const form = document.getElementById('moveStageForm');

    if(modal && form) {

        form.reset(); 

        modal.querySelector('#move_job_id').value = jobId;
        modal.querySelector('#move_target_status').value = targetStatus;

        const titleEl = modal.querySelector('#moveStageTitle');
        if(titleEl) titleEl.innerText = title || 'Move Stage';

        openModal('moveStageModal');
    }
    return;
}

            const fwdBtn = e.target.closest('.forward-job-btn');
            if (fwdBtn) {
                e.preventDefault();
                const modal = document.getElementById('forwardJobModal');
                modal.querySelector('#forward_job_id').value = fwdBtn.dataset.jobId;
                openModal('forwardJobModal');
                return;
            }

            const completeBtn = e.target.closest('.complete-job-btn');
            if (completeBtn) {
                e.preventDefault();
                const modal = document.getElementById('completeJobModal');
                modal.querySelector('#complete_job_id').value = completeBtn.dataset.jobId;
                openModal('completeJobModal');
                return;
            }

            const closeBtn = e.target.closest('.close-job-btn');
            if (closeBtn) {
                e.preventDefault();
                const modal = document.getElementById('closeJobModal');
                modal.querySelector('#close_job_id').value = closeBtn.dataset.jobId;
                openModal('closeJobModal');
                return;
            }

            const detailBtn = e.target.closest('.show-detail-btn');
            if (detailBtn) {
                e.preventDefault();
                const jobId = detailBtn.dataset.jobId;
                const content = document.getElementById('jobDetailContent');
                openModal('jobDetailModal');
                content.innerHTML = '<div class="flex justify-center p-10"><div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div></div>';

                fetch(`/jobs/${jobId}/details`)
                    .then(res => res.json())
                    .then(data => { content.innerHTML = data.html; })
                    .catch(() => { content.innerHTML = '<p class="text-red-500 text-center">Failed to load details.</p>'; });
                return;
            }

            if (e.target.closest('.cancel-modal-btn') || e.target.closest('.close-detail-btn')) {
                e.preventDefault();
                const modal = e.target.closest('.fixed');
                if(modal) modal.classList.add('hidden');
            }

                   const cancelBtn = e.target.closest('.cancel-job-btn');
if (cancelBtn) {
    e.preventDefault();
    const modal = document.getElementById('cancelJobModal');
    if(modal) {
        modal.querySelector('#cancel_job_id').value = cancelBtn.dataset.jobId;
        modal.classList.remove('hidden');
    }
    return;
}
        });

        const createBtn = document.getElementById('openCreateJobModalBtn');
        if(createBtn) createBtn.addEventListener('click', () => openModal('createJobModal'));

        document.getElementById('createJobForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            closeModal('createJobModal');
            handleFormSubmit('{{ route("jobs.store") }}', new FormData(this));
            this.reset();
        });

        document.getElementById('moveStageForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const jobId = this.querySelector('#move_job_id').value;
            closeModal('moveStageModal');
            handleFormSubmit(`/jobs/${jobId}/change-status`, new FormData(this));
            this.reset();
        });

        document.getElementById('forwardJobForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const jobId = this.querySelector('#forward_job_id').value;
            closeModal('forwardJobModal');
            handleFormSubmit(`/jobs/${jobId}/forward`, new FormData(this));
            this.reset();
        });

        document.getElementById('completeJobForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const jobId = this.querySelector('#complete_job_id').value;
            const formData = new FormData(this);
            formData.append('_method', 'PATCH');
            closeModal('completeJobModal');
            handleFormSubmit(`/jobs/${jobId}/complete`, formData);
            this.reset();
        });

        document.getElementById('closeJobForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const jobId = this.querySelector('#close_job_id').value;
            closeModal('closeJobModal');
            handleFormSubmit(`/jobs/${jobId}/close`, new FormData(this));
        });

        document.getElementById('cancelJobForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const jobId = this.querySelector('#cancel_job_id').value;
    const modal = document.getElementById('cancelJobModal');

    modal.classList.add('hidden');
    const spinner = document.getElementById('global-spinner');
    spinner.classList.remove('hidden');

    const formData = new FormData(this);

    fetch(`/jobs/${jobId}/cancel`, {
        method: 'POST', 

        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        spinner.classList.add('hidden');
        if (data.job) {
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: data.message, showConfirmButton: false, timer: 3000 });

            const card = document.getElementById(`job-card-${data.job.id}`);
            if(card) card.remove();
        } else {
             Swal.fire('Error', data.message || 'Failed to cancel', 'error');
        }
    })
    .catch(err => {
        spinner.classList.add('hidden');
        console.error(err);
        Swal.fire('Error', 'Connection error', 'error');
    });
});

    });

    </script>

    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

</x-app-layout>