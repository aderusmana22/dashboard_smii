{{-- File: resources/views/page/kanban/index.blade.php --}}
<x-app-layout>
    @section('title', 'Kanban Board Tugas')

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight mb-4 md:mb-0">
                {{ __('Kanban Board Tugas') }}
            </h2>
             @if(isset($user))
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    <span class="font-medium">{{ $user->name }}</span> ({{ $user->nik }}) - {{ optional($user->position)->name ?? 'N/A Position' }} | {{ optional($user->department)->department_name ?? 'N/A Department' }}
                </div>
            @endif
        </div>
    </x-slot>

    <div class="py-8 md:py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">

                    <div class="flex justify-end items-center mb-8">
                        <button id="openTaskModalBtn"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition duration-150 ease-in-out">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-2 -mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Tambah Tugas Baru
                        </button>
                    </div>

                    @include('page.kanban.partials.task_modal', ['departments' => $departments ?? collect(), 'task' => null])
                    @include('page.kanban.reject_task_form', ['task' => null, 'token' => null, 'approvalDetail' => null])


                    {{-- Kanban Columns Container --}}
                    <div class="flex overflow-x-auto space-x-6 pb-4 -mx-6 px-6 md:-mx-8 md:px-8">

                        {{-- PENDING APPROVAL Column --}}
                        <div class="flex-shrink-0 w-full sm:w-80 md:w-96 lg:w-[23rem]">
                            <div class="flex flex-col rounded-lg overflow-hidden shadow-lg h-full bg-yellow-50 dark:bg-gray-750">
                                <div class="bg-yellow-500 dark:bg-yellow-600 p-3">
                                    <h3 class="text-lg font-semibold text-white text-center tracking-wide">PENDING APPROVAL</h3>
                                </div>
                                <div id="pending-approval-column" class="p-4 kanban-column-body flex-1">
                                    <div id="pending-approval-tasks" class="space-y-4 kanban-tasks-container">
                                        @forelse($pendingApprovalTasks as $task)
                                            @include('page.kanban.partials.task_card', ['task' => $task])
                                        @empty
                                            <p class="text-center text-gray-500 dark:text-gray-400 py-4">Tidak ada tugas.</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- OPEN Column --}}
                        <div class="flex-shrink-0 w-full sm:w-80 md:w-96 lg:w-[23rem]">
                           <div class="flex flex-col rounded-lg overflow-hidden shadow-lg h-full bg-blue-50 dark:bg-gray-750">
                                <div class="bg-blue-500 dark:bg-blue-600 p-3">
                                    <h3 class="text-lg font-semibold text-white text-center tracking-wide">OPEN</h3>
                                </div>
                                <div id="open-column" class="p-4 kanban-column-body flex-1">
                                    <div id="open-tasks" class="space-y-4 kanban-tasks-container">
                                        @forelse($openTasks as $task)
                                            @include('page.kanban.partials.task_card', ['task' => $task])
                                        @empty
                                            <p class="text-center text-gray-500 dark:text-gray-400 py-4">Tidak ada tugas.</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- COMPLETED Column --}}
                        <div class="flex-shrink-0 w-full sm:w-80 md:w-96 lg:w-[23rem]">
                            <div class="flex flex-col rounded-lg overflow-hidden shadow-lg h-full bg-green-50 dark:bg-gray-750">
                                <div class="bg-green-500 dark:bg-green-600 p-3">
                                    <h3 class="text-lg font-semibold text-white text-center tracking-wide">COMPLETED</h3>
                                </div>
                                <div id="completed-column" class="p-4 kanban-column-body flex-1">
                                    <div id="completed-tasks" class="space-y-4 kanban-tasks-container">
                                        @forelse($completedTasks as $task)
                                            @include('page.kanban.partials.task_card', ['task' => $task])
                                        @empty
                                            <p class="text-center text-gray-500 dark:text-gray-400 py-4">Tidak ada tugas.</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- CANCELLED Column --}}
                        <div class="flex-shrink-0 w-full sm:w-80 md:w-96 lg:w-[23rem]">
                            <div class="flex flex-col rounded-lg overflow-hidden shadow-lg h-full bg-orange-50 dark:bg-gray-750">
                                <div class="bg-orange-500 dark:bg-orange-600 p-3">
                                    <h3 class="text-lg font-semibold text-white text-center tracking-wide">CANCELLED</h3>
                                </div>
                                <div id="cancelled-column" class="p-4 kanban-column-body flex-1">
                                    <div id="cancelled-tasks" class="space-y-4 kanban-tasks-container">
                                        @forelse($cancelledTasks as $task)
                                            @include('page.kanban.partials.task_card', ['task' => $task])
                                        @empty
                                            <p class="text-center text-gray-500 dark:text-gray-400 py-4">Tidak ada tugas.</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- REJECTED Column --}}
                        <div class="flex-shrink-0 w-full sm:w-80 md:w-96 lg:w-[23rem]">
                            <div class="flex flex-col rounded-lg overflow-hidden shadow-lg h-full bg-red-50 dark:bg-gray-750">
                                <div class="bg-red-600 dark:bg-red-700 p-3">
                                    <h3 class="text-lg font-semibold text-white text-center tracking-wide">REJECTED</h3>
                                </div>
                                <div id="rejected-column" class="p-4 kanban-column-body flex-1">
                                    <div id="rejected-tasks" class="space-y-4 kanban-tasks-container">
                                        @forelse($rejectedTasks as $task)
                                            @include('page.kanban.partials.task_card', ['task' => $task])
                                        @empty
                                            <p class="text-center text-gray-500 dark:text-gray-400 py-4">Tidak ada tugas.</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- CLOSED Column --}}
                        <div class="flex-shrink-0 w-full sm:w-80 md:w-96 lg:w-[23rem]">
                            <div class="flex flex-col rounded-lg overflow-hidden shadow-lg h-full bg-gray-100 dark:bg-gray-750">
                                 <div class="bg-gray-500 dark:bg-gray-600 p-3">
                                    <h3 class="text-lg font-semibold text-white text-center tracking-wide">CLOSED (30 Hari)</h3>
                                </div>
                                <div id="closed-column" class="p-4 kanban-column-body flex-1">
                                    <div id="closed-tasks" class="space-y-4 kanban-tasks-container">
                                         @forelse($closedTasks as $task)
                                            @include('page.kanban.partials.task_card', ['task' => $task])
                                        @empty
                                            <p class="text-center text-gray-500 dark:text-gray-400 py-4">Tidak ada tugas.</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> {{-- End Kanban Columns Container --}}
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .kanban-column-body {
            min-height: 400px; /* Ensure columns have a minimum height */
            max-height: calc(100vh - 250px); /* Example: viewport height minus header/footer/padding */
            overflow-y: auto; /* Allow vertical scroll within column body */
            scrollbar-width: thin;
            scrollbar-color: #A0AEC0 #E2E8F0; /* thumb track for light mode */
        }
        .dark .kanban-column-body {
            scrollbar-color: #4A5568 #2D3748; /* thumb track for dark mode */
        }
        .kanban-column-body::-webkit-scrollbar { width: 8px; }
        .kanban-column-body::-webkit-scrollbar-track { background: #E2E8F0; border-radius: 10px; }
        .dark .kanban-column-body::-webkit-scrollbar-track { background: #2D3748; }
        .kanban-column-body::-webkit-scrollbar-thumb { background-color: #A0AEC0; border-radius: 10px; border: 2px solid #E2E8F0; }
        .dark .kanban-column-body::-webkit-scrollbar-thumb { background-color: #4A5568; border: 2px solid #2D3748; }

        .kanban-tasks-container {
            min-height: 350px; /* Visual consistency for empty columns */
        }
        .task-content-scrollable {
            max-height: 120px; /* Max height for description */
            overflow-y: auto;
            scrollbar-width: thin;
            padding: 0.5rem;
            border-radius: 0.25rem;
        }
        .dark .task-content-scrollable { scrollbar-color: #4B5563 #374151; }
        .task-content-scrollable::-webkit-scrollbar { width: 6px; }
        .task-content-scrollable::-webkit-scrollbar-track { background: #E5E7EB; border-radius: 10px; }
        .dark .task-content-scrollable::-webkit-scrollbar-track { background: #374151; }
        .task-content-scrollable::-webkit-scrollbar-thumb { background-color: #9CA3AF; border-radius: 10px; border: 1px solid #E5E7EB; }
        .dark .task-content-scrollable::-webkit-scrollbar-thumb { background-color: #4B5563; border: 1px solid #374151; }

        .card-header-label { font-size: 0.75rem; display: block; margin-bottom: 0.125rem; }
        .card-header-value { font-size: 0.875rem; font-weight: 500; }
        .status-badge { font-size: 0.65rem; padding: 0.2rem 0.5rem; border-radius: 9999px; font-weight: 600; text-transform: uppercase; line-height: 1; letter-spacing: 0.025em;}

        /* Light mode status badges */
        .status-badge.status-pending_approval { background-color: #FEF3C7; color: #92400E;}
        .status-badge.status-open { background-color: #DBEAFE; color: #1D4ED8;}
        .status-badge.status-completed { background-color: #D1FAE5; color: #047857;}
        .status-badge.status-cancelled { background-color: #FFEDD5; color: #9A3412;}
        .status-badge.status-rejected { background-color: #FEE2E2; color: #991B1B;}
        .status-badge.status-closed { background-color: #E5E7EB; color: #374151;}

        /* Dark mode status badges */
        .dark .status-badge.status-pending_approval { background-color: #78350F; color: #FEF3C7;}
        .dark .status-badge.status-open { background-color: #1E40AF; color: #DBEAFE;}
        .dark .status-badge.status-completed { background-color: #065F46; color: #D1FAE5;}
        .dark .status-badge.status-cancelled { background-color: #7C2D12; color: #FFEDD5;}
        .dark .status-badge.status-rejected { background-color: #7F1D1D; color: #FEE2E2;}
        .dark .status-badge.status-closed { background-color: #4B5563; color: #E5E7EB;}

        .task-content-scrollable pre { white-space: pre-wrap; word-break: break-word; }

        /* Dark mode general text/bg adjustments */
        .dark .text-gray-700 { color: #D1D5DB; } .dark .text-gray-800 { color: #E5E7EB; }
        .dark .bg-gray-100 { background-color: #374151; } /* For closed column body */
        .dark .bg-yellow-50 { background-color: #4A3B12; } /* Example for dark pending column body */
        .dark .bg-blue-50 { background-color: #1E3A8A; }
        .dark .bg-green-50 { background-color: #054E3A; }
        .dark .bg-orange-50 { background-color: #7C2D12; }
        .dark .bg-red-50 { background-color: #7F1D1D; }
        .dark .bg-gray-750 { background-color: #2d3748; } /* Slightly darker than gray-700 for column bodies */


        .dark .border-gray-300 { border-color: #4B5563; } .dark .border-gray-200 { border-color: #4B5563; }
        .dark .card-header-label { color: #9CA3AF; } .dark .card-header-value { color: #F3F4F6; }
        .dark .task-card { background-color: #1F2937; border-color: #374151; }

        /* Department Badge Colors - Light Mode */
        .dept-badge { font-size: 0.7rem; padding: 0.15rem 0.4rem; }
        .dept-badge.dept-engineering-maintainance { background-color: #FECACA; color: #991B1B; }
        .dept-badge.dept-finance-admin { background-color: #FEF08A; color: #854D0E; }
        .dept-badge.dept-hcd { background-color: #A7F3D0; color: #047857; }
        .dept-badge.dept-manufacturing { background-color: #BFDBFE; color: #1D4ED8; }
        .dept-badge.dept-qm-hse { background-color: #C7D2FE; color: #4338CA; }
        .dept-badge.dept-rd { background-color: #DDD6FE; color: #6D28D9; }
        .dept-badge.dept-sales-marketing { background-color: #FBCFE8; color: #9D174D; }
        .dept-badge.dept-supply-chain { background-color: #A5F3FC; color: #0E7490; }
        .dept-badge.dept-it { background-color: #E0E7FF; color: #3730A3; } /* Example for IT */
        .dept-badge.dept-secret { background-color: #D1D5DB; color: #374151; }
        .dept-badge.dept-default, .dept-badge.dept-na { background-color: #E5E7EB; color: #4B5563; }

        /* Department Badge Colors - Dark Mode */
        .dark .dept-badge.dept-engineering-maintainance { background-color: #7F1D1D; color: #FECACA; }
        .dark .dept-badge.dept-finance-admin { background-color: #713F12; color: #FEF08A; }
        .dark .dept-badge.dept-hcd { background-color: #065F46; color: #A7F3D0; }
        .dark .dept-badge.dept-manufacturing { background-color: #1E40AF; color: #BFDBFE; }
        .dark .dept-badge.dept-qm-hse { background-color: #3730A3; color: #C7D2FE; }
        .dark .dept-badge.dept-rd { background-color: #5B21B6; color: #DDD6FE; }
        .dark .dept-badge.dept-sales-marketing { background-color: #831843; color: #FBCFE8; }
        .dark .dept-badge.dept-supply-chain { background-color: #155E75; color: #A5F3FC; }
        .dark .dept-badge.dept-it { background-color: #312E81; color: #E0E7FF; } /* Example for IT */
        .dark .dept-badge.dept-secret { background-color: #4B5563; color: #D1D5DB; }
        .dark .dept-badge.dept-default, .dark .dept-badge.dept-na { background-color: #374151; color: #9CA3AF; }

        /* Modal transition */
        #taskModal .relative, #cancelTaskModal .inline-block {
            transition-property: transform, opacity;
        }
        #taskModal:not(.hidden) .relative, #cancelTaskModal:not(.hidden) .inline-block {
            transform: scale(1);
            opacity: 1;
        }
    </style>
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const taskModal = document.getElementById('taskModal');
            const openTaskModalBtn = document.getElementById('openTaskModalBtn');
            const closeTaskModalBtn = document.getElementById('closeTaskModalBtn');
            const taskForm = document.getElementById('taskForm');
            const cancelTaskFormBtnInsideModal = document.getElementById('cancelTaskFormBtn');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const cancelTaskModal = document.getElementById('cancelTaskModal');
            const closeCancelTaskModalBtn = document.getElementById('closeCancelTaskModalBtn');
            const cancelCancelTaskFormBtn = document.getElementById('cancelCancelTaskFormBtn');
            const cancelTaskForm = document.getElementById('cancelTaskForm');
            const cancelTaskIdJobDisplay = document.getElementById('cancel_task_id_job_display');
            const cancelTaskIdModalInput = document.getElementById('cancel_task_id_modal');

            @php
                $phpCurrentUser = isset($user)
                    ? array_merge(
                        $user->toArray(), // Assumes $user is already loaded with department, position
                        [
                            'is_super_admin' => $user->isSuperAdmin(),
                            'is_admin_project' => $user->isAdminProject(),
                        ]
                    )
                    : null;
            @endphp
            const currentUser = @json($phpCurrentUser);

            // --- MODAL HANDLING ---
            function openModal(modalElement) {
                if (!modalElement) return;
                modalElement.classList.remove('hidden');
                // For transition:
                setTimeout(() => {
                    const innerModal = modalElement.querySelector('.relative, .inline-block');
                    if (innerModal) {
                        innerModal.classList.remove('scale-95', 'opacity-0');
                        innerModal.classList.add('scale-100', 'opacity-100');
                    }
                    const firstInput = modalElement.querySelector('input:not([type="hidden"]):not([readonly]), select, textarea');
                    if (firstInput) firstInput.focus();
                }, 10); // Small delay for CSS transition to pick up
            }

            function closeModal(modalElement) {
                if (!modalElement) return;
                const innerModal = modalElement.querySelector('.relative, .inline-block');
                if (innerModal) {
                    innerModal.classList.remove('scale-100', 'opacity-100');
                    innerModal.classList.add('scale-95', 'opacity-0');
                }
                setTimeout(() => {
                    modalElement.classList.add('hidden');
                }, 300); // Match CSS transition duration
            }


            if (openTaskModalBtn && taskModal) {
                openTaskModalBtn.addEventListener('click', () => {
                    if (taskForm) taskForm.reset();
                    openModal(taskModal);
                });
            }
            if (closeTaskModalBtn && taskModal) closeTaskModalBtn.addEventListener('click', () => closeModal(taskModal));
            if (cancelTaskFormBtnInsideModal && taskModal) cancelTaskFormBtnInsideModal.addEventListener('click', (e) => { e.preventDefault(); closeModal(taskModal); });

            if (closeCancelTaskModalBtn && cancelTaskModal) closeCancelTaskModalBtn.addEventListener('click', () => closeModal(cancelTaskModal));
            if (cancelCancelTaskFormBtn && cancelTaskModal) cancelCancelTaskFormBtn.addEventListener('click', (e) => { e.preventDefault(); closeModal(cancelTaskModal); });


            // --- HELPER FUNCTIONS ---
            function getStatusText(status) { return status ? status.replace(/_/g, ' ').toUpperCase() : 'N/A'; }
            function getDepartmentSlug(departmentName) {
                if (!departmentName) return 'na';
                return String(departmentName).toLowerCase()
                    .replace(/\s+/g, '-')          // Replace spaces with -
                    .replace(/[^\w-]+/g, '')       // Remove all non-word chars except -
                    .replace(/--+/g, '-')          // Replace multiple - with single -
                    .replace(/^-+/, '')             // Trim - from start of text
                    .replace(/-+$/, '');            // Trim - from end of text
            }
             function formatDate(dateString, includeTime = false) {
                if (!dateString) return '---';
                try {
                    const date = new Date(dateString);
                    if (isNaN(date.getTime())) return '---';

                    const options = { day: '2-digit', month: 'short', year: 'numeric' };
                    if (includeTime) {
                        options.hour = '2-digit';
                        options.minute = '2-digit';
                        options.hour12 = false; // Use 24-hour format
                    }
                    return date.toLocaleDateString('id-ID', options); // Use 'en-GB' for dd/mm/yyyy or 'id-ID' for Indonesian style
                } catch (e) {
                    console.error("Error formatting date:", dateString, e);
                    return 'Date Error';
                }
            }

            function generateButtonsHTML(task, loggedInUser) {
                let buttonsHtml = '';
                if (!loggedInUser || !task) return buttonsHtml;

                const commonButtonClass = "text-xs px-3 py-1.5 rounded-md font-semibold focus:outline-none focus:ring-2 focus:ring-offset-1 dark:focus:ring-offset-gray-800 whitespace-nowrap";
                const isSuperAdmin = loggedInUser.is_super_admin;
                const isAdminProject = loggedInUser.is_admin_project;
                const isPengaju = loggedInUser.id === task.pengaju_id;
                const isTargetDepartmentMember = task.department_id && loggedInUser.department && loggedInUser.department.id === task.department_id;


                if (task.status === 'pending_approval') {
                    if (isPengaju || isAdminProject || isSuperAdmin) {
                        buttonsHtml += `<button data-action="cancel" data-task-id="${task.id}" data-task-idjob="${task.id_job}" class="${commonButtonClass} bg-orange-500 hover:bg-orange-600 text-white focus:ring-orange-400">Cancel</button>`;
                    }
                } else if (task.status === 'open') {
                    if (isPengaju || isAdminProject || isSuperAdmin || isTargetDepartmentMember) {
                        buttonsHtml += `<button data-action="complete" data-task-id="${task.id}" class="${commonButtonClass} bg-green-500 hover:bg-green-600 text-white focus:ring-green-400">Complete</button>`;
                    }
                    if (isPengaju || isAdminProject || isSuperAdmin) {
                        buttonsHtml += `<button data-action="cancel" data-task-id="${task.id}" data-task-idjob="${task.id_job}" class="${commonButtonClass} bg-orange-500 hover:bg-orange-600 text-white focus:ring-orange-400 ml-2">Cancel</button>`;
                    }
                } else if (task.status === 'completed') {
                    if (isPengaju || isAdminProject || isSuperAdmin) {
                        buttonsHtml += `<button data-action="archive" data-task-id="${task.id}" class="${commonButtonClass} bg-gray-500 hover:bg-gray-600 text-white focus:ring-gray-400">Archive</button>`;
                    }
                    if (isAdminProject || isSuperAdmin) {
                        buttonsHtml += `<button data-action="reopen" data-task-id="${task.id}" class="${commonButtonClass} bg-blue-500 hover:bg-blue-600 text-white focus:ring-blue-400 ml-2">Re-Open</button>`;
                    }
                } else if (task.status === 'rejected' || task.status === 'cancelled') {
                    if (isPengaju && task.status === 'rejected') {
                        buttonsHtml += `<button data-action="archive" data-task-id="${task.id}" class="${commonButtonClass} bg-gray-500 hover:bg-gray-600 text-white focus:ring-gray-400">Archive</button>`;
                    }
                    if (isAdminProject || isSuperAdmin) {
                        buttonsHtml += `<button data-action="reopen" data-task-id="${task.id}" class="${commonButtonClass} bg-blue-500 hover:bg-blue-600 text-white focus:ring-blue-400 ${ (isPengaju && task.status === 'rejected') ? 'ml-2' : '' }">Re-Open</button>`;
                        buttonsHtml += ` <button data-action="delete_permanently" data-task-id="${task.id}" class="${commonButtonClass} bg-red-700 hover:bg-red-800 text-white focus:ring-red-600 ml-2">Delete</button>`;
                    }
                } else if (task.status === 'closed') { // Archived
                    if (isAdminProject || isSuperAdmin) {
                        buttonsHtml += `<button data-action="reopen" data-task-id="${task.id}" class="${commonButtonClass} bg-blue-500 hover:bg-blue-600 text-white focus:ring-blue-400">Re-Open</button>`;
                        buttonsHtml += ` <button data-action="delete_permanently" data-task-id="${task.id}" class="${commonButtonClass} bg-red-700 hover:bg-red-800 text-white focus:ring-red-600 ml-2">Delete</button>`;
                    }
                }
                return buttonsHtml;
            }

            function createTaskCardHTML(task) {
                const pengajuName = task.pengaju ? task.pengaju.name : 'N/A';
                const departmentName = task.department ? task.department.department_name : 'N/A';
                const departmentSlug = getDepartmentSlug(departmentName);
                const penutupName = task.penutup ? task.penutup.name : 'N/A';

                let processedApprovalDetail = null;
                let approverName = 'N/A';
                let processedAt = null;
                let rejectionNotes = null;

                if (task.approval_details && task.approval_details.length > 0) {
                    processedApprovalDetail = task.approval_details
                        .filter(d => d.status === 'approved' || d.status === 'rejected')
                        .sort((a, b) => new Date(b.processed_at || b.updated_at || 0) - new Date(a.processed_at || a.updated_at || 0))[0];

                    if (processedApprovalDetail) {
                        approverName = processedApprovalDetail.approver ? processedApprovalDetail.approver.name : (processedApprovalDetail.approver_nik || 'N/A');
                        processedAt = processedApprovalDetail.processed_at || processedApprovalDetail.updated_at;
                        if (processedApprovalDetail.status === 'rejected') {
                            rejectionNotes = processedApprovalDetail.notes;
                        }
                    }
                }

                let headerBgClass = 'bg-gray-400 dark:bg-gray-600';
                if (task.status === 'pending_approval') headerBgClass = 'bg-yellow-500 dark:bg-yellow-600';
                else if (task.status === 'open') headerBgClass = 'bg-blue-500 dark:bg-blue-600';
                else if (task.status === 'completed') headerBgClass = 'bg-green-500 dark:bg-green-600';
                else if (task.status === 'cancelled') headerBgClass = 'bg-orange-500 dark:bg-orange-600';
                else if (task.status === 'rejected') headerBgClass = 'bg-red-600 dark:bg-red-700';
                else if (task.status === 'closed') headerBgClass = 'bg-gray-500 dark:bg-gray-700';

                // Sanitize HTML content for display in <pre>
                const listJobDisplay = task.list_job ? task.list_job.replace(/</g, "&lt;").replace(/>/g, "&gt;") : '';
                const rejectionNotesDisplay = rejectionNotes ? rejectionNotes.replace(/</g, "&lt;").replace(/>/g, "&gt;") : '';
                const cancelReasonDisplay = task.cancel_reason ? task.cancel_reason.replace(/</g, "&lt;").replace(/>/g, "&gt;") : '';


                let cardHTML = `
                <div class="task-card bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg shadow-md flex flex-col h-full" data-task-id="${task.id}" data-task-idjob="${task.id_job || ''}">
                    <div class="flex-grow">
                        <div class="flex justify-between items-center ${headerBgClass} text-white p-3 rounded-t-lg">
                            <div>
                                <span class="block text-xs opacity-80">ID JOB</span>
                                <span class="text-sm font-semibold">${task.id_job || 'N/A'}</span>
                            </div>
                            <span class="status-badge status-${task.status}">${getStatusText(task.status)}</span>
                        </div>
                        <div class="p-3 space-y-2">
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <span class="card-header-label text-gray-500 dark:text-gray-400">From</span>
                                    <span class="card-header-value text-gray-900 dark:text-gray-100 truncate block" title="${pengajuName}">${pengajuName}</span>
                                </div>
                                <div>
                                    <span class="card-header-label text-gray-500 dark:text-gray-400">To Department</span>
                                    <span class="dept-badge dept-${departmentSlug} text-xs font-medium px-2 py-0.5 rounded-full inline-block truncate" title="${departmentName}">${departmentName}</span>
                                </div>
                                <div>
                                    <span class="card-header-label text-gray-500 dark:text-gray-400">Start</span>
                                    <span class="card-header-value text-gray-900 dark:text-gray-100">${formatDate(task.tanggal_job_mulai)}</span>
                                </div>
                                <div>
                                    <span class="card-header-label text-gray-500 dark:text-gray-400">End</span>
                                    <span class="card-header-value text-gray-900 dark:text-gray-100">${formatDate(task.tanggal_job_selesai)}</span>
                                </div>
                            </div>`;

                if (processedApprovalDetail) {
                    cardHTML += `
                            <div class="pt-1 border-t border-gray-200 dark:border-gray-700 mt-2">
                                <span class="card-header-label text-gray-500 dark:text-gray-400">${processedApprovalDetail.status === 'rejected' ? 'Processed by (Rejected)' : 'Processed by (Approved)'}</span>
                                <span class="card-header-value text-gray-900 dark:text-gray-100">${approverName} ${processedAt ? 'at ' + formatDate(processedAt, true) : ''}</span>
                            </div>`;
                    if (rejectionNotesDisplay) {
                        cardHTML += `
                            <div class="pt-1">
                                <span class="card-header-label text-gray-500 dark:text-gray-400">Rejection Notes</span>
                                <span class="card-header-value text-red-600 dark:text-red-400 text-xs break-all">${rejectionNotesDisplay}</span>
                            </div>`;
                    }
                }

                if (task.status === 'cancelled' && cancelReasonDisplay) {
                    cardHTML += `
                            <div class="pt-1 border-t border-gray-200 dark:border-gray-700 mt-2">
                                <span class="card-header-label text-gray-500 dark:text-gray-400">Cancellation Reason</span>
                                <span class="card-header-value text-orange-600 dark:text-orange-400 text-xs break-all">${cancelReasonDisplay}</span>
                            </div>`;
                }

                if (task.status === 'closed' && task.penutup) { // Ensure penutup is not null
                    cardHTML += `
                            <div class="pt-1 border-t border-gray-200 dark:border-gray-700 mt-2">
                                <span class="card-header-label text-gray-500 dark:text-gray-400">Closed by</span>
                                <span class="card-header-value text-gray-900 dark:text-gray-100">${penutupName} at ${task.closed_at ? formatDate(task.closed_at, true) : '---'}</span>
                            </div>`;
                }

                cardHTML += `
                        </div>
                        <div class="px-3 py-2 border-t border-gray-200 dark:border-gray-700">
                            <span class="card-header-label text-gray-500 dark:text-gray-400">Location</span>
                            <span class="card-header-value text-gray-900 dark:text-gray-100">${task.area || 'N/A'}</span>
                        </div>
                        <div class="px-3 py-2">
                            <p class="text-xs font-medium mb-1 text-gray-600 dark:text-gray-400">Description:</p>
                            <div class="task-content-scrollable bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600">
                                <pre class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap break-words">${listJobDisplay}</pre>
                            </div>
                        </div>
                    </div>
                    <div class="mt-auto p-3 border-t border-gray-200 dark:border-gray-600 flex flex-wrap gap-2 justify-end">
                        ${generateButtonsHTML(task, currentUser)}
                    </div>
                </div>`;
                return cardHTML;
            }


            function getTargetColumnTasksId(status) {
                switch (status) {
                    case 'pending_approval': return 'pending-approval-tasks';
                    case 'open': return 'open-tasks';
                    case 'completed': return 'completed-tasks';
                    case 'cancelled': return 'cancelled-tasks';
                    case 'rejected': return 'rejected-tasks';
                    case 'closed': return 'closed-tasks';
                    default: return null;
                }
            }

            function reRenderTask(task) {
                const existingCard = document.querySelector(`.task-card[data-task-id="${task.id}"]`);
                const newCardHTML = createTaskCardHTML(task);
                const targetColumnTasksId = getTargetColumnTasksId(task.status);

                if (!targetColumnTasksId) {
                    console.error(`Unknown status for task ${task.id}: ${task.status}`);
                    return;
                }
                const targetTasksContainer = document.getElementById(targetColumnTasksId);
                if (!targetTasksContainer) {
                    console.error(`Target container ${targetColumnTasksId} not found for task ${task.id}`);
                    return;
                }

                // Remove "Tidak ada tugas" placeholder if it exists
                const placeholder = targetTasksContainer.querySelector('.no-tasks-placeholder');
                if(placeholder) placeholder.remove();


                if (existingCard) {
                    const currentTasksContainer = existingCard.parentElement;
                    if (currentTasksContainer && currentTasksContainer.id === targetColumnTasksId) {
                        existingCard.outerHTML = newCardHTML; // Replace in same column
                    } else {
                        existingCard.remove(); // Remove from old column
                        targetTasksContainer.insertAdjacentHTML('afterbegin', newCardHTML); // Add to new column
                        // Add placeholder back to old column if it's empty
                        if(currentTasksContainer && currentTasksContainer.childElementCount === 0) {
                             currentTasksContainer.innerHTML = '<p class="text-center text-gray-500 dark:text-gray-400 py-4 no-tasks-placeholder">Tidak ada tugas.</p>';
                        }
                    }
                } else {
                    targetTasksContainer.insertAdjacentHTML('afterbegin', newCardHTML); // Add new card
                }
            }

            if (taskForm) {
                taskForm.addEventListener('submit', async function(event) {
                    event.preventDefault();
                    const formData = new FormData(taskForm);
                    if (currentUser && currentUser.id && !formData.has('pengaju_id')) {
                        formData.append('pengaju_id', currentUser.id);
                    }
                    const data = Object.fromEntries(formData.entries());

                    // Basic client-side validation (though server validation is key)
                    if (!data.id_job || !data.department_id || !data.area || !data.list_job) {
                        Swal.fire('Error', 'ID JOB, Department, Location, and Description are required fields.', 'error');
                        return;
                    }

                    Swal.fire({ title: 'Menyimpan...', text: 'Mohon tunggu sebentar.', didOpen: () => Swal.showLoading(), allowOutsideClick: false });

                    try {
                        const response = await fetch("{{ route('tasks.store') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify(data)
                        });
                        const responseData = await response.json();

                        if (!response.ok) {
                            let htmlMessage = responseData.message || 'Failed to save task.';
                            if (responseData.errors) {
                                htmlMessage += '<br><ul class="text-left list-disc list-inside mt-2">';
                                for (const field in responseData.errors) {
                                    htmlMessage += `<li>${responseData.errors[field].join(', ')}</li>`;
                                }
                                htmlMessage += '</ul>';
                            }
                            Swal.fire({ icon: 'error', title: 'Validation Failed', html: htmlMessage });
                            return;
                        }

                        if (responseData && responseData.id) {
                            reRenderTask(responseData);
                            closeModal(taskModal);
                            taskForm.reset();
                            Swal.fire('Success!', responseData.message || 'Task berhasil dibuat dan menunggu persetujuan.', 'success');
                        } else {
                             Swal.fire({ icon: 'error', title: 'Submission Error', text: 'Task data not received from server or invalid format.' });
                        }
                    } catch (error) {
                        console.error('Error submitting task:', error);
                        Swal.fire({ icon: 'error', title: 'Submission Error', text: 'Could not save task. Check connection or server logs.' });
                    }
                });
            }

            async function handleTaskAction(taskId, action, taskData = null) {
                let url, method = 'PATCH', newStatus, confirmationTitle, confirmationText, successMessage;
                let requiresConfirmation = false;
                let requestBody = {};

                switch (action) {
                    case 'complete': newStatus = 'completed'; successMessage = 'Task marked as COMPLETED.'; requestBody = { status: newStatus }; break;
                    case 'archive': newStatus = 'closed'; successMessage = 'Task ARCHIVED.'; requestBody = { status: newStatus }; break;
                    case 'cancel':
                        if (cancelTaskModal && taskData && typeof taskData.id_job !== 'undefined') {
                            cancelTaskIdModalInput.value = taskId;
                            cancelTaskIdJobDisplay.innerText = taskData.id_job || 'N/A';
                            document.getElementById('cancel_reason_modal').value = ''; // Clear previous reason
                            document.getElementById('requester_confirmation_cancel_modal').checked = false; // Uncheck
                            openModal(cancelTaskModal);
                        } else {
                            Swal.fire('Error', 'Cannot open cancel modal. Task information is missing.', 'error');
                        }
                        return; // Exit function, modal submission will handle it
                    case 'reopen': newStatus = 'open'; successMessage = 'Task RE-OPENED.'; requestBody = { status: newStatus }; break;
                    case 'delete_permanently':
                        method = 'DELETE';
                        confirmationTitle = 'Hapus Task Permanen?';
                        confirmationText = "Tindakan ini tidak dapat dibatalkan. Task akan dihapus selamanya.";
                        requiresConfirmation = true;
                        successMessage = 'Task berhasil dihapus permanen.';
                        break;
                    default: console.error('Unknown action:', action); return;
                }

                url = `/tasks/${taskId}${method === 'DELETE' ? '' : '/status'}`; // Adjust URL based on method

                if (requiresConfirmation) {
                    const result = await Swal.fire({
                        title: confirmationTitle, text: confirmationText, icon: 'warning',
                        showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, Lanjutkan!', cancelButtonText: 'Tidak, Batal'
                    });
                    if (!result.isConfirmed) return;
                }

                Swal.fire({ title: 'Memproses...', text: 'Mohon tunggu sebentar.', didOpen: () => Swal.showLoading(), allowOutsideClick: false });

                try {
                    const fetchOptions = {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                    };
                    if (Object.keys(requestBody).length > 0) { // Only add body if it's not empty
                        fetchOptions.body = JSON.stringify(requestBody);
                    }

                    const response = await fetch(url, fetchOptions);
                    const responseData = (method === 'DELETE' && response.status === 204)
                        ? { message: successMessage }
                        : await response.json().catch(() => ({ message: "Error: Respons server tidak valid."}));

                    if (!response.ok) {
                        let htmlMessage = responseData.message || 'Terjadi kesalahan.';
                        if (responseData.errors) {
                            htmlMessage += '<br><ul class="text-left list-disc list-inside mt-2">';
                            for (const field in responseData.errors) htmlMessage += `<li>${responseData.errors[field].join(', ')}</li>`;
                            htmlMessage += '</ul>';
                        } else if (!responseData.message && response.statusText) {
                             htmlMessage += ` ${response.status}: ${response.statusText}`;
                        }
                        Swal.fire({ icon: 'error', title: 'Oops...', html: htmlMessage });
                        return;
                    }

                    if (method === 'DELETE') {
                        const cardToRemove = document.querySelector(`.task-card[data-task-id="${taskId}"]`);
                        if (cardToRemove) {
                            const parentContainer = cardToRemove.parentElement;
                            cardToRemove.remove();
                            if(parentContainer && parentContainer.childElementCount === 0) {
                                 parentContainer.innerHTML = '<p class="text-center text-gray-500 dark:text-gray-400 py-4 no-tasks-placeholder">Tidak ada tugas.</p>';
                            }
                        }
                        Swal.fire('Berhasil!', responseData.message || successMessage, 'success');
                    } else if (responseData && responseData.id) {
                        reRenderTask(responseData);
                        Swal.fire('Berhasil!', responseData.message || successMessage, 'success');
                    } else {
                         Swal.fire({ icon: 'error', title: 'Action Error', text: 'Respons tidak terduga dari server.' });
                    }

                } catch (error) {
                    console.error(`Error during action ${action} for task ${taskId}:`, error);
                    Swal.fire({ icon: 'error', title: 'Network Error', text: 'Tidak dapat terhubung ke server.' });
                }
            }

            if (cancelTaskForm) {
                cancelTaskForm.addEventListener('submit', async function(event) {
                    event.preventDefault();
                    const taskId = document.getElementById('cancel_task_id_modal').value;
                    const cancelReason = document.getElementById('cancel_reason_modal').value;
                    const confirmation = document.getElementById('requester_confirmation_cancel_modal').checked;

                    if (!cancelReason.trim()) { Swal.fire('Error', 'Alasan pembatalan harus diisi.', 'error'); return; }
                    if (!confirmation) { Swal.fire('Error', 'Anda harus mencentang kotak konfirmasi.', 'error'); return; }

                    const requestBody = {
                        status: 'cancelled',
                        cancel_reason: cancelReason,
                        requester_confirmation_cancel: confirmation ? 1 : 0
                    };

                    Swal.fire({ title: 'Membatalkan...', text: 'Mohon tunggu sebentar.', didOpen: () => Swal.showLoading(), allowOutsideClick: false });

                    try {
                        const response = await fetch(`/tasks/${taskId}/status`, {
                            method: 'PATCH',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                            body: JSON.stringify(requestBody)
                        });
                        const responseData = await response.json();

                        if (!response.ok) {
                            let htmlMessage = responseData.message || 'Gagal membatalkan task.';
                            if (responseData.errors) {
                                htmlMessage += '<br><ul class="text-left list-disc list-inside mt-2">';
                                for (const field in responseData.errors) htmlMessage += `<li>${responseData.errors[field].join(', ')}</li>`;
                                htmlMessage += '</ul>';
                            }
                            Swal.fire({ icon: 'error', title: 'Oops...', html: htmlMessage });
                            return;
                        }

                        if (responseData && responseData.id) {
                            reRenderTask(responseData);
                            closeModal(cancelTaskModal);
                            cancelTaskForm.reset();
                            Swal.fire('Berhasil!', responseData.message || 'Task berhasil dibatalkan.', 'success');
                        } else {
                             Swal.fire({ icon: 'error', title: 'Error', text: 'Data task tidak diterima setelah pembatalan atau format tidak valid.' });
                        }
                    } catch (error) {
                        console.error(`Error cancelling task ${taskId}:`, error);
                        Swal.fire({ icon: 'error', title: 'Network Error', text: 'Tidak dapat terhubung ke server.' });
                    }
                });
            }

            // Event delegation for action buttons on task cards
            document.addEventListener('click', function(event) {
                const targetButton = event.target.closest('button[data-action]');
                if (!targetButton) return;

                event.preventDefault();
                const action = targetButton.dataset.action;
                const taskId = targetButton.dataset.taskId;
                const taskIdJob = targetButton.dataset.taskIdjob; // For cancel modal display

                if (action && taskId) {
                    handleTaskAction(taskId, action, { id_job: taskIdJob });
                }
            });
        });
    </script>
    @endpush
</x-app-layout>