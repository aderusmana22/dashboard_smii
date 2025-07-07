<x-app-layout>
    @section('title', 'Task Kanban Board')

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight mb-4 md:mb-0">
                {{ __('Task Kanban Board') }}
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
            <div class="overflow-hidden">
                    <div class="flex justify-end items-center mb-8">
                        <button id="openTaskModalBtn"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition duration-150 ease-in-out">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-2 -mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Add New Task
                        </button>
                    </div>

                    @include('page.kanban.partials.task_modal', ['departments' => $departments ?? collect(), 'task' => null])
                    @include('page.kanban.reject_task_form', ['task' => null, 'token' => null, 'approvalDetail' => null])

                    @php
                        $show_pending_approval = false;
                        $show_open = false;
                        $show_completed = false;
                        $show_cancelled = false;
                        $show_rejected = false;
                        $show_closed = false;

                        if (isset($user)) {
                            $isAdmin = $user->isSuperAdmin() || $user->isAdminProject();
                            $isApprover = method_exists($user, 'isApprover') && $user->isApprover();
                            $isTvUser = method_exists($user, 'isTvUser') && $user->isTvUser();

                            if ($isAdmin) {
                                $show_pending_approval = true;
                                $show_open = true;
                                $show_completed = true;
                                $show_cancelled = true;
                                $show_rejected = true;
                                $show_closed = true;
                            } elseif ($isApprover) {
                                $show_pending_approval = true;
                                $show_cancelled = true;
                                $show_rejected = true;
                            } elseif ($isTvUser) {
                                $show_open = true;
                                $show_completed = true;
                                $show_closed = true;
                            } else {
                                $show_open = true;
                                $show_completed = true;
                                $show_rejected = true;
                                $show_cancelled = true;
                                $show_closed = true;
                            }
                        }
                    @endphp

                    <div class="flex overflow-x-auto gap-3 kanban-container">

                        @if($show_pending_approval)
                        <div class="flex-shrink-0 w-[85vw] sm:w-[45vw] md:w-[30vw] lg:w-kanban-column kanban-column">
                            <div class="flex flex-col rounded-lg overflow-hidden shadow-lg h-full bg-yellow-100 dark:bg-gray-750">
                                <div class="bg-yellow-500 dark:bg-yellow-600 p-3">
                                    <h3 class="text-lg font-semibold text-white text-center tracking-wide">PENDING APPROVAL</h3>
                                </div>
                                <div id="pending-approval-column" class="p-4 kanban-column-body flex-1">
                                    <div id="pending-approval-tasks" class="space-y-4 kanban-tasks-container">
                                        @forelse($pendingApprovalTasks as $task)
                                            @include('page.kanban.partials.task_card', ['task' => $task])
                                        @empty
                                            <p class="text-center text-gray-500 dark:text-gray-400 py-4">No tasks available.</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($show_open)
                        <div class="flex-shrink-0 w-[85vw] sm:w-[45vw] md:w-[30vw] lg:w-kanban-column kanban-column">
                           <div class="flex flex-col rounded-lg overflow-hidden shadow-lg h-full bg-cyan-100 dark:bg-gray-750">
                                <div class="bg-cyan-500 dark:bg-cyan-600 p-3">
                                    <h3 class="text-lg font-semibold text-white text-center tracking-wide">OPEN</h3>
                                </div>
                                <div id="open-column" class="p-4 kanban-column-body flex-1">
                                    <div id="open-tasks" class="space-y-4 kanban-tasks-container">
                                        @forelse($openTasks as $task)
                                            @include('page.kanban.partials.task_card', ['task' => $task])
                                        @empty
                                            <p class="text-center text-gray-500 dark:text-gray-400 py-4">No tasks available.</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($show_completed)
                        <div class="flex-shrink-0 w-[85vw] sm:w-[45vw] md:w-[30vw] lg:w-kanban-column kanban-column">
                            <div class="flex flex-col rounded-lg overflow-hidden shadow-lg h-full bg-green-100 dark:bg-gray-750">
                                <div class="bg-green-600 dark:bg-green-700 p-3">
                                    <h3 class="text-lg font-semibold text-white text-center tracking-wide">COMPLETED</h3>
                                </div>
                                <div id="completed-column" class="p-4 kanban-column-body flex-1">
                                    <div id="completed-tasks" class="space-y-4 kanban-tasks-container">
                                        @forelse($completedTasks as $task)
                                            @include('page.kanban.partials.task_card', ['task' => $task])
                                        @empty
                                            <p class="text-center text-gray-500 dark:text-gray-400 py-4">No tasks available.</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($show_cancelled)
                        <div class="flex-shrink-0 w-[85vw] sm:w-[45vw] md:w-[30vw] lg:w-kanban-column kanban-column">
                            <div class="flex flex-col rounded-lg overflow-hidden shadow-lg h-full bg-yellow-100 dark:bg-gray-750">
                                <div class="bg-yellow-500 dark:bg-yellow-600 p-3">
                                    <h3 class="text-lg font-semibold text-white text-center tracking-wide">CANCELLED</h3>
                                </div>
                                <div id="cancelled-column" class="p-4 kanban-column-body flex-1">
                                    <div id="cancelled-tasks" class="space-y-4 kanban-tasks-container">
                                        @forelse($cancelledTasks as $task)
                                            @include('page.kanban.partials.task_card', ['task' => $task])
                                        @empty
                                            <p class="text-center text-gray-500 dark:text-gray-400 py-4">No tasks available.</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($show_rejected)
                        <div class="flex-shrink-0 w-[85vw] sm:w-[45vw] md:w-[30vw] lg:w-kanban-column kanban-column">
                            <div class="flex flex-col rounded-lg overflow-hidden shadow-lg h-full bg-red-100 dark:bg-gray-750">
                                <div class="bg-red-600 dark:bg-red-700 p-3">
                                    <h3 class="text-lg font-semibold text-white text-center tracking-wide">REJECTED</h3>
                                </div>
                                <div id="rejected-column" class="p-4 kanban-column-body flex-1">
                                    <div id="rejected-tasks" class="space-y-4 kanban-tasks-container">
                                        @forelse($rejectedTasks as $task)
                                            @include('page.kanban.partials.task_card', ['task' => $task])
                                        @empty
                                            <p class="text-center text-gray-500 dark:text-gray-400 py-4">No tasks available.</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($show_closed)
                        <div class="flex-shrink-0 w-[85vw] sm:w-[45vw] md:w-[30vw] lg:w-kanban-column kanban-column">
                            <div class="flex flex-col rounded-lg overflow-hidden shadow-lg h-full bg-gray-200 dark:bg-gray-750">
                                 <div class="bg-gray-600 dark:bg-gray-700 p-3">
                                    <h3 class="text-lg font-semibold text-white text-center tracking-wide">CLOSED (30 Days)</h3>
                                </div>
                                <div id="closed-column" class="p-4 kanban-column-body flex-1">
                                    <div id="closed-tasks" class="space-y-4 kanban-tasks-container">
                                         @forelse($closedTasks as $task)
                                            @include('page.kanban.partials.task_card', ['task' => $task])
                                        @empty
                                            <p class="text-center text-gray-500 dark:text-gray-400 py-4">No tasks available.</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
            </div>
        </div>
    </div>

    @push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .kanban-container {
            scroll-snap-type: x mandatory;
            overscroll-behavior-x: contain;
        }

        .kanban-column {
            scroll-snap-align: start;
        }

        .lg\:w-kanban-column {
            width: calc((100% - 2 * 1.5rem) / 3);
        }

        .kanban-column-body {
            min-height: 400px;
            max-height: calc(100vh - 300px);
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #A0AEC0 #E2E8F0;
        }
        .dark .kanban-column-body {
            scrollbar-color: #4A5568 #2D3748;
        }
        .kanban-column-body::-webkit-scrollbar { width: 8px; }
        .kanban-column-body::-webkit-scrollbar-track { background: #E2E8F0; border-radius: 10px; }
        .dark .kanban-column-body::-webkit-scrollbar-track { background: #2D3748; }
        .kanban-column-body::-webkit-scrollbar-thumb { background-color: #A0AEC0; border-radius: 10px; border: 2px solid #E2E8F0; }
        .dark .kanban-column-body::-webkit-scrollbar-thumb { background-color: #4A5568; border: 2px solid #2D3748; }

        .kanban-tasks-container {
            min-height: 350px;
        }
        .task-content-scrollable {
            max-height: 120px;
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

        .status-badge.status-pending_approval { background-color: #fef3c7; color: #92400e; }
        .status-badge.status-open { background-color: #cffafe; color: #0e7490; }
        .status-badge.status-completed { background-color: #d1fae5; color: #047857; }
        .status-badge.status-cancelled { background-color: #fef3c7; color: #92400e; }
        .status-badge.status-rejected { background-color: #fee2e2; color: #991b1b; }
        .status-badge.status-closed { background-color: #e5e7eb; color: #374151; }
        .dark .status-badge.status-pending_approval { background-color: #78350f; color: #fef3c7; }
        .dark .status-badge.status-open { background-color: #164e63; color: #cffafe; }
        .dark .status-badge.status-completed { background-color: #065f46; color: #d1fae5; }
        .dark .status-badge.status-cancelled { background-color: #78350f; color: #fef3c7; }
        .dark .status-badge.status-rejected { background-color: #7f1d1d; color: #fee2e2; }
        .dark .status-badge.status-closed { background-color: #4b5563; color: #e5e7eb; }

        .task-content-scrollable pre { white-space: pre-wrap; word-break: break-word; }

        .dark .text-gray-700 { color: #D1D5DB; } .dark .text-gray-800 { color: #E5E7EB; }
        .dark .bg-gray-750 { background-color: #2d3748; }
        .dark .border-gray-300 { border-color: #4B5563; } .dark .border-gray-200 { border-color: #4B5563; }
        .dark .card-header-label { color: #9CA3AF; } .dark .card-header-value { color: #F3F4F6; }
        .dark .task-card { background-color: #1F2937; border-color: #374151; }
        .dept-badge { font-size: 0.7rem; padding: 0.15rem 0.4rem; }
        .dept-badge.dept-engineering-maintainance { background-color: #FECACA; color: #991B1B; }
        .dept-badge.dept-finance-admin { background-color: #FEF08A; color: #854D0E; }
        .dept-badge.dept-hcd { background-color: #A7F3D0; color: #047857; }
        .dept-badge.dept-manufacturing { background-color: #BFDBFE; color: #1D4ED8; }
        .dept-badge.dept-qm-hse { background-color: #C7D2FE; color: #4338CA; }
        .dept-badge.dept-rd { background-color: #DDD6FE; color: #6D28D9; }
        .dept-badge.dept-sales-marketing { background-color: #FBCFE8; color: #9D174D; }
        .dept-badge.dept-supply-chain { background-color: #A5F3FC; color: #0E7490; }
        .dept-badge.dept-it { background-color: #E0E7FF; color: #3730A3; }
        .dept-badge.dept-secret { background-color: #D1D5DB; color: #374151; }
        .dept-badge.dept-default, .dept-badge.dept-na { background-color: #E5E7EB; color: #4B5563; }
        .dark .dept-badge.dept-engineering-maintainance { background-color: #7F1D1D; color: #FECACA; }
        .dark .dept-badge.dept-finance-admin { background-color: #713F12; color: #FEF08A; }
        .dark .dept-badge.dept-hcd { background-color: #065F46; color: #A7F3D0; }
        .dark .dept-badge.dept-manufacturing { background-color: #1E40AF; color: #BFDBFE; }
        .dark .dept-badge.dept-qm-hse { background-color: #3730A3; color: #C7D2FE; }
        .dark .dept-badge.dept-rd { background-color: #5B21B6; color: #DDD6FE; }
        .dark .dept-badge.dept-sales-marketing { background-color: #831843; color: #FBCFE8; }
        .dark .dept-badge.dept-supply-chain { background-color: #155E75; color: #A5F3FC; }
        .dark .dept-badge.dept-it { background-color: #312E81; color: #E0E7FF; }
        .dark .dept-badge.dept-secret { background-color: #4B5563; color: #D1D5DB; }
        .dark .dept-badge.dept-default, .dark .dept-badge.dept-na { background-color: #374151; color: #9CA3AF; }

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
                        $user->toArray(),
                        [
                            'is_super_admin' => $user->isSuperAdmin(),
                            'is_admin_project' => $user->isAdminProject(),
                        ]
                    )
                    : null;
            @endphp
            const currentUser = @json($phpCurrentUser);

            function openModal(modalElement) {
                if (!modalElement) return;
                modalElement.classList.remove('hidden');
                setTimeout(() => {
                    const innerModal = modalElement.querySelector('.relative, .inline-block');
                    if (innerModal) {
                        innerModal.classList.remove('scale-95', 'opacity-0');
                        innerModal.classList.add('scale-100', 'opacity-100');
                    }
                    const firstInput = modalElement.querySelector('input:not([type="hidden"]):not([readonly]), select, textarea');
                    if (firstInput) firstInput.focus();
                }, 10);
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
                }, 300);
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

            function getStatusText(status) { return status ? status.replace(/_/g, ' ').toUpperCase() : 'N/A'; }
            function getDepartmentSlug(departmentName) {
                if (!departmentName) return 'na';
                return String(departmentName).toLowerCase()
                    .replace(/\s+/g, '-')
                    .replace(/[^\w-]+/g, '')
                    .replace(/--+/g, '-')
                    .replace(/^-+/, '')
                    .replace(/-+$/, '');
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
                        options.hour12 = false;
                    }
                    return date.toLocaleDateString('en-GB', options);
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
                        buttonsHtml += `<button data-action="cancel" data-task-id="${task.id}" data-task-idjob="${task.id_job}" class="${commonButtonClass} bg-yellow-500 hover:bg-yellow-600 text-white focus:ring-yellow-400">Cancel</button>`;
                    }
                } else if (task.status === 'open') {
                    if (isPengaju || isAdminProject || isSuperAdmin || isTargetDepartmentMember) {
                        buttonsHtml += `<button data-action="complete" data-task-id="${task.id}" class="${commonButtonClass} bg-green-600 hover:bg-green-700 text-white focus:ring-green-500">Complete</button>`;
                    }
                    if (isPengaju || isAdminProject || isSuperAdmin) {
                        buttonsHtml += `<button data-action="cancel" data-task-id="${task.id}" data-task-idjob="${task.id_job}" class="${commonButtonClass} bg-yellow-500 hover:bg-yellow-600 text-white focus:ring-yellow-400 ml-2">Cancel</button>`;
                    }
                } else if (task.status === 'completed') {
                    if (isPengaju || isAdminProject || isSuperAdmin) {
                        buttonsHtml += `<button data-action="archive" data-task-id="${task.id}" class="${commonButtonClass} bg-gray-600 hover:bg-gray-700 text-white focus:ring-gray-500">Archive</button>`;
                    }
                    if (isAdminProject || isSuperAdmin) {
                        buttonsHtml += `<button data-action="reopen" data-task-id="${task.id}" class="${commonButtonClass} bg-cyan-500 hover:bg-cyan-600 text-white focus:ring-cyan-400 ml-2">Re-Open</button>`;
                    }
                } else if (task.status === 'rejected' || task.status === 'cancelled') {
                    if (isPengaju && task.status === 'rejected') {
                        buttonsHtml += `<button data-action="archive" data-task-id="${task.id}" class="${commonButtonClass} bg-gray-600 hover:bg-gray-700 text-white focus:ring-gray-500">Archive</button>`;
                    }
                    if (isAdminProject || isSuperAdmin) {
                        buttonsHtml += `<button data-action="reopen" data-task-id="${task.id}" class="${commonButtonClass} bg-cyan-500 hover:bg-cyan-600 text-white focus:ring-cyan-400 ${ (isPengaju && task.status === 'rejected') ? 'ml-2' : '' }">Re-Open</button>`;
                        buttonsHtml += ` <button data-action="delete_permanently" data-task-id="${task.id}" class="${commonButtonClass} bg-red-600 hover:bg-red-700 text-white focus:ring-red-500 ml-2">Delete</button>`;
                    }
                } else if (task.status === 'closed') {
                    if (isAdminProject || isSuperAdmin) {
                        buttonsHtml += `<button data-action="reopen" data-task-id="${task.id}" class="${commonButtonClass} bg-cyan-500 hover:bg-cyan-600 text-white focus:ring-cyan-400">Re-Open</button>`;
                        buttonsHtml += ` <button data-action="delete_permanently" data-task-id="${task.id}" class="${commonButtonClass} bg-red-600 hover:bg-red-700 text-white focus:ring-red-500 ml-2">Delete</button>`;
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

                let headerBgClass = 'bg-gray-600 dark:bg-gray-700';
                if (task.status === 'pending_approval') headerBgClass = 'bg-yellow-500 dark:bg-yellow-600';
                else if (task.status === 'open') headerBgClass = 'bg-cyan-500 dark:bg-cyan-600';
                else if (task.status === 'completed') headerBgClass = 'bg-green-600 dark:bg-green-700';
                else if (task.status === 'cancelled') headerBgClass = 'bg-yellow-500 dark:bg-yellow-600';
                else if (task.status === 'rejected') headerBgClass = 'bg-red-600 dark:bg-red-700';
                else if (task.status === 'closed') headerBgClass = 'bg-gray-600 dark:bg-gray-700';

                const listJobDisplay = task.list_job ? task.list_job.replace(/</g, "<").replace(/>/g, ">") : '';
                const rejectionNotesDisplay = rejectionNotes ? rejectionNotes.replace(/</g, "<").replace(/>/g, ">") : '';
                const cancelReasonDisplay = task.cancel_reason ? task.cancel_reason.replace(/</g, "<").replace(/>/g, ">") : '';


                let cardHTML = `
                <div class="task-card rounded-lg shadow-md flex flex-col h-full" data-task-id="${task.id}" data-task-idjob="${task.id_job || ''}">
                    <div class="flex-grow">
                        <div class="flex justify-between items-center ${headerBgClass} text-white p-3 rounded-t-lg">
                            <div>
                                <span class="block text-xs opacity-80">JOB ID</span>
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
                                <span class="card-header-value text-gray-900 dark:text-gray-100">${approverName} ${processedAt ? ' at ' + formatDate(processedAt, true) : ''}</span>
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
                                <span class="card-header-value text-yellow-700 dark:text-yellow-500 text-xs break-all">${cancelReasonDisplay}</span>
                            </div>`;
                }

                if (task.status === 'closed' && task.penutup) {
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
                            <div class="task-content-scrollable">
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

                const placeholder = targetTasksContainer.querySelector('.no-tasks-placeholder');
                if(placeholder) placeholder.remove();


                if (existingCard) {
                    const currentTasksContainer = existingCard.parentElement;
                    if (currentTasksContainer && currentTasksContainer.id === targetColumnTasksId) {
                        existingCard.outerHTML = newCardHTML;
                    } else {
                        existingCard.remove();
                        targetTasksContainer.insertAdjacentHTML('afterbegin', newCardHTML);
                        if(currentTasksContainer && currentTasksContainer.childElementCount === 0) {
                             currentTasksContainer.innerHTML = '<p class="text-center text-gray-500 dark:text-gray-400 py-4 no-tasks-placeholder">No tasks available.</p>';
                        }
                    }
                } else {
                    targetTasksContainer.insertAdjacentHTML('afterbegin', newCardHTML);
                }
            }

// Ganti blok ini di dalam file Blade Anda
if (taskForm) {
    taskForm.addEventListener('submit', async function(event) {
        event.preventDefault();
        const formData = new FormData(taskForm);
        if (currentUser && currentUser.id && !formData.has('pengaju_id')) {
            formData.append('pengaju_id', currentUser.id);
        }
        const data = Object.fromEntries(formData.entries());

        // ==================================================
        // PERBAIKAN: Hapus pengecekan untuk id_job
        // ==================================================
        if (!data.department_id || !data.area || !data.list_job) {
            // Sesuaikan juga pesan errornya
            Swal.fire('Error', 'Department, Location, and Description are required fields.', 'error');
            return;
        }
        // ==================================================

        Swal.fire({ title: 'Saving...', text: 'Please wait a moment.', didOpen: () => Swal.showLoading(), allowOutsideClick: false });

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
                // Pesan sukses yang lebih informatif
                Swal.fire('Success!', `Task ${responseData.id_job} created and is awaiting approval.`, 'success');
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
                            document.getElementById('cancel_reason_modal').value = '';
                            document.getElementById('requester_confirmation_cancel_modal').checked = false;
                            openModal(cancelTaskModal);
                        } else {
                            Swal.fire('Error', 'Cannot open cancel modal. Task information is missing.', 'error');
                        }
                        return;
                    case 'reopen': newStatus = 'open'; successMessage = 'Task RE-OPENED.'; requestBody = { status: newStatus }; break;
                    case 'delete_permanently':
                        method = 'DELETE';
                        confirmationTitle = 'Permanently Delete Task?';
                        confirmationText = "This action cannot be undone. The task will be deleted forever.";
                        requiresConfirmation = true;
                        successMessage = 'Task permanently deleted successfully.';
                        break;
                    default: console.error('Unknown action:', action); return;
                }

                url = `/tasks/${taskId}${method === 'DELETE' ? '' : '/status'}`;

                if (requiresConfirmation) {
                    const result = await Swal.fire({
                        title: confirmationTitle, text: confirmationText, icon: 'warning',
                        showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, proceed!', cancelButtonText: 'No, Cancel'
                    });
                    if (!result.isConfirmed) return;
                }

                Swal.fire({ title: 'Processing...', text: 'Please wait a moment.', didOpen: () => Swal.showLoading(), allowOutsideClick: false });

                try {
                    const fetchOptions = {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                    };
                    if (Object.keys(requestBody).length > 0) {
                        fetchOptions.body = JSON.stringify(requestBody);
                    }

                    const response = await fetch(url, fetchOptions);
                    const responseData = (method === 'DELETE' && response.status === 204)
                        ? { message: successMessage }
                        : await response.json().catch(() => ({ message: "Error: Invalid server response."}));

                    if (!response.ok) {
                        let htmlMessage = responseData.message || 'An error occurred.';
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
                                 parentContainer.innerHTML = '<p class="text-center text-gray-500 dark:text-gray-400 py-4 no-tasks-placeholder">No tasks available.</p>';
                            }
                        }
                        Swal.fire('Success!', responseData.message || successMessage, 'success');
                    } else if (responseData && responseData.id) {
                        reRenderTask(responseData);
                        Swal.fire('Success!', responseData.message || successMessage, 'success');
                    } else {
                         Swal.fire({ icon: 'error', title: 'Action Error', text: 'Unexpected response from the server.' });
                    }

                } catch (error) {
                    console.error(`Error during action ${action} for task ${taskId}:`, error);
                    Swal.fire({ icon: 'error', title: 'Network Error', text: 'Could not connect to the server.' });
                }
            }

            if (cancelTaskForm) {
                cancelTaskForm.addEventListener('submit', async function(event) {
                    event.preventDefault();
                    const taskId = document.getElementById('cancel_task_id_modal').value;
                    const cancelReason = document.getElementById('cancel_reason_modal').value;
                    const confirmation = document.getElementById('requester_confirmation_cancel_modal').checked;

                    if (!cancelReason.trim()) { Swal.fire('Error', 'Cancellation reason is required.', 'error'); return; }
                    if (!confirmation) { Swal.fire('Error', 'You must check the confirmation box.', 'error'); return; }

                    const requestBody = {
                        status: 'cancelled',
                        cancel_reason: cancelReason,
                        requester_confirmation_cancel: confirmation ? 1 : 0
                    };

                    Swal.fire({ title: 'Cancelling...', text: 'Please wait a moment.', didOpen: () => Swal.showLoading(), allowOutsideClick: false });

                    try {
                        const response = await fetch(`/tasks/${taskId}/status`, {
                            method: 'PATCH',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                            body: JSON.stringify(requestBody)
                        });
                        const responseData = await response.json();

                        if (!response.ok) {
                            let htmlMessage = responseData.message || 'Failed to cancel the task.';
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
                            Swal.fire('Success!', responseData.message || 'Task cancelled successfully.', 'success');
                        } else {
                             Swal.fire({ icon: 'error', title: 'Error', text: 'Task data not received after cancellation or format is invalid.' });
                        }
                    } catch (error) {
                        console.error(`Error cancelling task ${taskId}:`, error);
                        Swal.fire({ icon: 'error', title: 'Network Error', text: 'Could not connect to the server.' });
                    }
                });
            }

            document.addEventListener('click', function(event) {
                const targetButton = event.target.closest('button[data-action]');
                if (!targetButton) return;

                event.preventDefault();
                const action = targetButton.dataset.action;
                const taskId = targetButton.dataset.taskId;
                const taskIdJob = targetButton.dataset.taskIdjob;

                if (action && taskId) {
                    handleTaskAction(taskId, action, { id_job: taskIdJob });
                }
            });
        });
    </script>
    @endpush
</x-app-layout>