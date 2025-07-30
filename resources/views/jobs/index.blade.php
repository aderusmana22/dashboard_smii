<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight mb-4 md:mb-0">
                {{ __('Job Kanban Board') }}
            </h2>
             @if(isset($user))
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    <span class="font-medium">{{ $user->name }}</span>
                    <span class="hidden sm:inline">| {{ optional($user->department)->department_name ?? 'N/A Department' }}</span>
                </div>
            @endif
        </div>
    </x-slot>

    <div class="py-8 md:py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden">
                <div class="flex justify-end items-center mb-8">
                    <button id="openCreateJobModalBtn"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition duration-150 ease-in-out">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-2 -mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Add New Job
                    </button>
                </div>

                <div class="flex overflow-x-auto gap-6 pb-4">
                    <!-- Kolom Open -->
                    <div class="flex-shrink-0 w-[85vw] sm:w-[45vw] md:w-[30vw] lg:w-[23%]">
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

                    <!-- Kolom On Process -->
                    <div class="flex-shrink-0 w-[85vw] sm:w-[45vw] md:w-[30vw] lg:w-[23%]">
                        <div class="flex flex-col rounded-lg shadow-lg h-full bg-blue-100 dark:bg-gray-700">
                            <div class="bg-blue-500 dark:bg-blue-600 p-3 rounded-t-lg">
                                <h3 class="text-lg font-semibold text-white text-center tracking-wide">ON PROCESS</h3>
                            </div>
                            <div id="on_process-column" class="p-4 space-y-4 kanban-column-body flex-1">
                                @forelse($onProcessJobs as $job)
                                    @include('jobs.partials.job_card', ['job' => $job])
                                @empty
                                    <p class="no-jobs-placeholder text-center text-gray-500 dark:text-gray-400 py-4">No jobs available.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Kolom Completed -->
                    <div class="flex-shrink-0 w-[85vw] sm:w-[45vw] md:w-[30vw] lg:w-[23%]">
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

                    <!-- Kolom Closed -->
                    <div class="flex-shrink-0 w-[85vw] sm:w-[45vw] md:w-[30vw] lg:w-[23%]">
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

    @include('jobs.modals.create_job_modal')
    @include('jobs.modals.forward_job_modal')
    @include('jobs.modals.complete_job_modal')

    @push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .kanban-column-body {
            min-height: 400px;
            max-height: calc(100vh - 300px);
            overflow-y: auto;
        }
    </style>
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const user = @json($user);

            const createModalEl = document.getElementById('createJobModal');
            const forwardModalEl = document.getElementById('forwardJobModal');
            const completeModalEl = document.getElementById('completeJobModal');

            const createForm = document.getElementById('createJobForm');
            const forwardForm = document.getElementById('forwardJobForm');
            const completeForm = document.getElementById('completeJobForm');

            function openModal(modalEl) { modalEl.classList.remove('hidden'); }
            function closeModal(modalEl) { modalEl.classList.add('hidden'); }

            document.getElementById('openCreateJobModalBtn').addEventListener('click', () => openModal(createModalEl));
            createModalEl.querySelector('.cancel-modal-btn').addEventListener('click', () => closeModal(createModalEl));
            forwardModalEl.querySelector('.cancel-modal-btn').addEventListener('click', () => closeModal(forwardModalEl));
            completeModalEl.querySelector('.cancel-modal-btn').addEventListener('click', () => closeModal(completeModalEl));

            function getCardHtml(job) {
                const latestRoute = job.latest_route || {};
                const currentDept = latestRoute.to_department || {};
                const isSuperAdmin = user.is_super_admin || false;
                const canAct = user.department_id == currentDept.id || isSuperAdmin;
                
                let buttons = '';
                if (job.status == 'open' && canAct) {
                    buttons = `<button class="start-job-btn text-xs bg-blue-500 hover:bg-blue-600 text-white font-semibold px-3 py-1 rounded-md" data-job-id="${job.id}">Start Job</button>`;
                } else if (job.status == 'on_process' && canAct) {
                    buttons = `
                        <button class="forward-job-btn text-xs bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-3 py-1 rounded-md" data-job-id="${job.id}">Forward</button>
                        <button class="complete-job-btn text-xs bg-green-500 hover:bg-green-600 text-white font-semibold px-3 py-1 rounded-md" data-job-id="${job.id}">Complete</button>
                    `;
                } else if (job.status == 'completed' && (job.pengaju_id == user.id || isSuperAdmin)) {
                    buttons = `<button class="close-job-btn text-xs bg-gray-600 hover:bg-gray-700 text-white font-semibold px-3 py-1 rounded-md" data-job-id="${job.id}">Close Job</button>`;
                }

                return `
                    <div class="job-card bg-white dark:bg-gray-800 rounded-lg p-4 shadow-md border border-gray-200 dark:border-gray-700" data-job-id="${job.id}">
                        <div class="font-bold text-lg mb-2 text-gray-800 dark:text-gray-100">${job.id_job}</div>
                        <div class="space-y-2 text-sm">
                            <p><span class="font-semibold text-gray-600 dark:text-gray-400">Area:</span> <span class="text-gray-800 dark:text-gray-200">${job.area}</span></p>
                            <p><span class="font-semibold text-gray-600 dark:text-gray-400">Requester:</span> <span class="text-gray-800 dark:text-gray-200">${job.pengaju ? job.pengaju.name : 'N/A'}</span></p>
                            <p><span class="font-semibold text-gray-600 dark:text-gray-400">Current Dept:</span> 
                                <span class="bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 px-2 py-1 rounded-full text-xs font-medium">${currentDept.department_name || 'N/A'}</span>
                            </p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-600 flex justify-end flex-wrap gap-2">${buttons}</div>
                    </div>
                `;
            }

            function updateCard(jobData) {
                const existingCard = document.querySelector(`.job-card[data-job-id="${jobData.id}"]`);
                const newCardHtml = getCardHtml(jobData);
                const targetColumn = document.getElementById(`${jobData.status}-column`);

                if (existingCard) {
                    const sourceColumn = existingCard.parentElement;
                    if (sourceColumn.id !== targetColumn.id) {
                        existingCard.remove();
                        const placeholder = targetColumn.querySelector('.no-jobs-placeholder');
                        if (placeholder) placeholder.remove();
                        targetColumn.insertAdjacentHTML('afterbegin', newCardHtml);
                        if (sourceColumn.children.length === 0) {
                            sourceColumn.innerHTML = '<p class="no-jobs-placeholder text-center text-gray-500 dark:text-gray-400 py-4">No jobs available.</p>';
                        }
                    } else {
                        existingCard.outerHTML = newCardHtml;
                    }
                } else if (targetColumn) {
                    const placeholder = targetColumn.querySelector('.no-jobs-placeholder');
                    if (placeholder) placeholder.remove();
                    targetColumn.insertAdjacentHTML('afterbegin', newCardHtml);
                }
            }

            async function handleFormSubmit(url, method, formData, successCallback) {
                try {
                    const response = await fetch(url, {
                        method: method,
                        body: formData,
                        headers: {'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json'}
                    });
                    const data = await response.json();
                    if (!response.ok) {
                        let errorHtml = data.message || 'An unknown error occurred.';
                        if (response.status === 422 && data.errors) {
                            errorHtml = 'Please fix the following errors:<br><ul class="text-left list-disc list-inside mt-2">';
                            for (const field in data.errors) {
                                errorHtml += `<li>${data.errors[field][0]}</li>`;
                            }
                            errorHtml += '</ul>';
                        }
                        Swal.fire({icon: 'error', title: 'Validation Failed', html: errorHtml});
                        return;
                    }
                    successCallback(data);
                } catch (error) {
                    Swal.fire('Error', 'Could not connect to the server.', 'error');
                }
            }

            createForm.addEventListener('submit', function(e) {
                e.preventDefault();
                handleFormSubmit('{{ route("jobs.store") }}', 'POST', new FormData(this), (data) => {
                    updateCard(data);
                    closeModal(createModalEl);
                    this.reset();
                    Swal.fire('Success', 'Job created!', 'success');
                });
            });

            forwardForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const jobId = this.querySelector('#forward_job_id').value;
                handleFormSubmit(`/jobs/${jobId}/forward`, 'POST', new FormData(this), (data) => {
                    updateCard(data);
                    closeModal(forwardModalEl);
                    Swal.fire('Success', 'Job forwarded!', 'success');
                });
            });
            
            completeForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const jobId = this.querySelector('#complete_job_id').value;
                const formData = new FormData(this);
                const requestBody = { note: formData.get('note'), _method: 'PATCH' };
                fetch(`/jobs/${jobId}/complete`, {
                    method: 'POST',
                    body: JSON.stringify(requestBody),
                    headers: {'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json'}
                })
                .then(res => res.json()).then(data => {
                    if(data.id) {
                        updateCard(data);
                        closeModal(completeModalEl);
                        Swal.fire('Success', 'Job completed!', 'success');
                    } else {
                        Swal.fire('Error', data.message || 'Failed to complete.', 'error');
                    }
                });
            });

            document.body.addEventListener('click', function(e) {
                const target = e.target.closest('button[data-job-id]');
                if (!target) return;
                const jobId = target.dataset.jobId;
                let url, method = 'PATCH';
                if (target.classList.contains('start-job-btn')) {
                    url = `/jobs/${jobId}/start`;
                } else if (target.classList.contains('close-job-btn')) {
                    url = `/jobs/${jobId}/close`;
                } else if (target.classList.contains('forward-job-btn')) {
                    document.getElementById('forward_job_id').value = jobId;
                    openModal(forwardModalEl);
                    return;
                } else if (target.classList.contains('complete-job-btn')) {
                    document.getElementById('complete_job_id').value = jobId;
                    openModal(completeModalEl);
                    return;
                } else {
                    return;
                }
                fetch(url, { method: method, headers: {'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json'} })
                .then(res => res.json()).then(data => {
                    if(data.id) {
                        updateCard(data);
                        Swal.fire('Success', `Job status updated!`, 'success');
                    } else {
                        Swal.fire('Error', data.message || 'Action failed.', 'error');
                    }
                });
            });
        });
    </script>
    @endpush
</x-app-layout>