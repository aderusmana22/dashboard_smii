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

                <!-- CONTAINER UTAMA KANBAN -->
                <!-- CONTAINER UTAMA KANBAN -->
<div class="flex flex-nowrap overflow-x-auto gap-4 pb-4 items-stretch min-h-[calc(100vh-250px)] px-2">
    @php 
        $columnClass = "flex-none w-[85vw] md:w-1/2 lg:w-[calc(100%/3-10px)] flex flex-col"; 
    @endphp

    <!-- 1. To Be Scheduled (ROSE / MERAH MUDA) -->
    <div class="{{ $columnClass }}">
        <!-- Hapus dark:bg-gray-800 agar tetap pastel -->
        <div class="flex flex-col rounded-xl shadow-md h-full bg-rose-50 border border-rose-100 overflow-hidden">
            <div class="bg-rose-500 p-3 text-center">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider">To Be Scheduled</h3>
            </div>
            <div id="to-be-scheduled-column" class="p-3 space-y-3 kanban-column-body flex-1">
                @forelse($toBeScheduledJobs as $job) 
                    @include('jobs.partials.job_card', ['job' => $job]) 
                @empty 
                    <div class="flex items-center justify-center h-full opacity-60">
                        <p class="text-xs text-rose-800 font-bold">No Jobs.</p>
                    </div> 
                @endforelse
            </div>
        </div>
    </div>

    <!-- 2. Scheduled (SKY / BIRU LANGIT) -->
    <div class="{{ $columnClass }}">
        <div class="flex flex-col rounded-xl shadow-md h-full bg-sky-50 border border-sky-100 overflow-hidden">
            <div class="bg-sky-500 p-3 text-center">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider">Scheduled</h3>
            </div>
            <div id="scheduled-column" class="p-3 space-y-3 kanban-column-body flex-1">
                @forelse($scheduledJobs as $job) 
                    @include('jobs.partials.job_card', ['job' => $job]) 
                @empty 
                    <div class="flex items-center justify-center h-full opacity-60">
                        <p class="text-xs text-sky-800 font-bold">No Jobs.</p>
                    </div> 
                @endforelse
            </div>
        </div>
    </div>

    <!-- 3. Preparation (PURPLE / UNGU) -->
    <div class="{{ $columnClass }}">
        <div class="flex flex-col rounded-xl shadow-md h-full bg-purple-50 border border-purple-100 overflow-hidden">
            <div class="bg-purple-500 p-3 text-center">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider">Preparation</h3>
            </div>
            <div id="preparation-column" class="p-3 space-y-3 kanban-column-body flex-1">
                @forelse($preparationJobs as $job) 
                    @include('jobs.partials.job_card', ['job' => $job]) 
                @empty 
                    <div class="flex items-center justify-center h-full opacity-60">
                        <p class="text-xs text-purple-800 font-bold">No Jobs.</p>
                    </div> 
                @endforelse
            </div>
        </div>
    </div>

    <!-- 4. On Going (AMBER / KUNING EMAS) -->
    <div class="{{ $columnClass }}">
        <div class="flex flex-col rounded-xl shadow-md h-full bg-amber-50 border border-amber-100 overflow-hidden">
            <div class="bg-amber-500 p-3 text-center">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider">On Going</h3>
            </div>
            <div id="on-going-column" class="p-3 space-y-3 kanban-column-body flex-1">
                @forelse($onGoingJobs as $job) 
                    @include('jobs.partials.job_card', ['job' => $job]) 
                @empty 
                    <div class="flex items-center justify-center h-full opacity-60">
                        <p class="text-xs text-amber-800 font-bold">No Jobs.</p>
                    </div> 
                @endforelse
            </div>
        </div>
    </div>

    <!-- 5. Completed (EMERALD / HIJAU SEGAR) -->
    <div class="{{ $columnClass }}">
        <div class="flex flex-col rounded-xl shadow-md h-full bg-emerald-50 border border-emerald-100 overflow-hidden">
            <div class="bg-emerald-500 p-3 text-center">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider">Completed</h3>
            </div>
            <div id="completed-column" class="p-3 space-y-3 kanban-column-body flex-1">
                @forelse($completedJobs as $job) 
                    @include('jobs.partials.job_card', ['job' => $job]) 
                @empty 
                    <div class="flex items-center justify-center h-full opacity-60">
                        <p class="text-xs text-emerald-800 font-bold">No Jobs.</p>
                    </div> 
                @endforelse
            </div>
        </div>
    </div>

    <!-- 6. Closed (CYAN / BIRU TOSKA CERAH) -->
    <div class="{{ $columnClass }}">
        <div class="flex flex-col rounded-xl shadow-md h-full bg-cyan-50 border border-cyan-100 overflow-hidden">
            <div class="bg-cyan-600 p-3 text-center">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider">Closed</h3>
            </div>
            <div id="closed-column" class="p-3 space-y-3 kanban-column-body flex-1">
                @forelse($closedJobs as $job) 
                    @include('jobs.partials.job_card', ['job' => $job]) 
                @empty 
                    <div class="flex items-center justify-center h-full opacity-60">
                        <p class="text-xs text-cyan-800 font-bold">No Jobs.</p>
                    </div> 
                @endforelse
            </div>
        </div>
    </div>
</div>

            </div>
        </div>
    </div>

    <!-- INCLUDE MODALS -->
    @include('jobs.modals.create')
    @include('jobs.modals.move_stage')
    @include('jobs.modals.forward')
    @include('jobs.modals.complete')
    @include('jobs.modals.close')
    @include('jobs.modals.detail')
    @include('jobs.modals.cancel')

    <!-- Global Spinner -->
    <div id="global-spinner" class="hidden fixed inset-0 z-50 bg-black bg-opacity-60 flex items-center justify-center">
        <div class="flex flex-col items-center">
            <div class="w-16 h-16 border-4 border-white border-t-blue-500 rounded-full animate-spin"></div>
            <p class="text-white text-lg mt-4">Processing...</p>
        </div>
    </div>

    @push('styles')
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
        
        // Helper Spinner
        const showSpinner = () => spinner.classList.remove('hidden');
        const hideSpinner = () => spinner.classList.add('hidden');

        // --- Helper: Update UI ---
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

        // --- Helper: Submit Form ---
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

        // --- Modal Helpers ---
        function openModal(id) { 
            const el = document.getElementById(id);
            if(el) el.classList.remove('hidden'); 
            else console.error('Modal not found:', id);
        }
        function closeModal(id) { document.getElementById(id)?.classList.add('hidden'); }

        // --- EVENT DELEGATION UTAMA ---
        document.body.addEventListener('click', function(e) {
            
            // 1. CEK TOMBOL UNIVERSAL (Set Schedule, Prep, Start)
            const moveBtn = e.target.closest('.move-stage-btn');
            
            if (moveBtn) {
                e.preventDefault();
                const jobId = moveBtn.dataset.jobId;
                const targetStatus = moveBtn.dataset.targetStatus;
                const title = moveBtn.dataset.title;

                const modal = document.getElementById('moveStageModal');
                if(modal) {
                    modal.querySelector('#move_job_id').value = jobId;
                    modal.querySelector('#move_target_status').value = targetStatus;
                    const titleEl = modal.querySelector('#moveStageTitle');
                    if(titleEl) titleEl.innerText = title || 'Move Stage';

                    openModal('moveStageModal');
                }
                return;
            }
            
            // 2. Tombol Forward
            const fwdBtn = e.target.closest('.forward-job-btn');
            if (fwdBtn) {
                e.preventDefault();
                const modal = document.getElementById('forwardJobModal');
                modal.querySelector('#forward_job_id').value = fwdBtn.dataset.jobId;
                openModal('forwardJobModal');
                return;
            }

            // 3. Tombol Complete
            const completeBtn = e.target.closest('.complete-job-btn');
            if (completeBtn) {
                e.preventDefault();
                const modal = document.getElementById('completeJobModal');
                modal.querySelector('#complete_job_id').value = completeBtn.dataset.jobId;
                openModal('completeJobModal');
                return;
            }

            // 4. Tombol Close Job
            const closeBtn = e.target.closest('.close-job-btn');
            if (closeBtn) {
                e.preventDefault();
                const modal = document.getElementById('closeJobModal');
                modal.querySelector('#close_job_id').value = closeBtn.dataset.jobId;
                openModal('closeJobModal');
                return;
            }

            // 5. Tombol Show Detail
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

            // 6. Tombol Cancel Modal
            if (e.target.closest('.cancel-modal-btn') || e.target.closest('.close-detail-btn')) {
                e.preventDefault();
                const modal = e.target.closest('.fixed');
                if(modal) modal.classList.add('hidden');
            }

// 7. Tombol Cancel Job
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

        // --- FORM HANDLERS ---
        
        // Create Job
        const createBtn = document.getElementById('openCreateJobModalBtn');
        if(createBtn) createBtn.addEventListener('click', () => openModal('createJobModal'));

        document.getElementById('createJobForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            closeModal('createJobModal');
            handleFormSubmit('{{ route("jobs.store") }}', new FormData(this));
            this.reset();
        });

        // Move Stage (Universal Form)
        document.getElementById('moveStageForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const jobId = this.querySelector('#move_job_id').value;
            closeModal('moveStageModal');
            handleFormSubmit(`/jobs/${jobId}/change-status`, new FormData(this));
            this.reset();
        });

        // Forward
        document.getElementById('forwardJobForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const jobId = this.querySelector('#forward_job_id').value;
            closeModal('forwardJobModal');
            handleFormSubmit(`/jobs/${jobId}/forward`, new FormData(this));
            this.reset();
        });

        // Complete
        document.getElementById('completeJobForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const jobId = this.querySelector('#complete_job_id').value;
            const formData = new FormData(this);
            formData.append('_method', 'PATCH');
            closeModal('completeJobModal');
            handleFormSubmit(`/jobs/${jobId}/complete`, formData);
            this.reset();
        });

        // Close
        document.getElementById('closeJobForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const jobId = this.querySelector('#close_job_id').value;
            closeModal('closeJobModal');
            handleFormSubmit(`/jobs/${jobId}/close`, new FormData(this));
        });

        //cancel
 

        document.getElementById('cancelJobForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const jobId = this.querySelector('#cancel_job_id').value;
    const modal = document.getElementById('cancelJobModal');
    
    // Sembunyikan modal & Show Spinner
    modal.classList.add('hidden');
    const spinner = document.getElementById('global-spinner');
    spinner.classList.remove('hidden');

    const formData = new FormData(this);

    fetch(`/jobs/${jobId}/cancel`, {
        method: 'POST', // Method POST tapi di form ada @method('PATCH') jadi Laravel bacanya PATCH
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
            // Hapus kartu dari board karena statusnya cancelled (atau pindahkan ke kolom closed/cancelled jika ada)
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
    @endpush
</x-app-layout>