<x-app-layout>
    @section('title', 'Kanban Board')

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Kanban Board Tugas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8"> {{-- Changed to max-w-full for more space --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">

                    <div class="flex justify-end items-center mb-8">
                        <button id="openTaskModalBtn"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            + Tambah Tugas Baru
                        </button>
                    </div>
                    @include('page.kanban.partials.task_modal', ['departments' => $departments])

                    {{-- Kanban Columns Container - using flex for horizontal scrolling on smaller screens --}}
                    <div class="flex overflow-x-auto space-x-6 pb-4">

                        {{-- PENDING APPROVAL Column --}}
                        <div class="flex-shrink-0 w-full md:w-1/3 lg:w-1/4 xl:w-1/5"> {{-- Responsive width --}}
                            <div class="flex flex-col rounded-lg overflow-hidden shadow-lg h-full">
                                <div class="bg-yellow-500 p-3">
                                    <h2 class="text-xl font-semibold text-white text-center">PENDING APPROVAL</h2>
                                </div>
                                <div id="pending-approval-column" class="border-2 border-yellow-500 rounded-b-lg p-4 kanban-column-body flex-1 bg-yellow-50 dark:bg-gray-700">
                                    <div id="pending-approval-tasks" class="space-y-4 kanban-tasks-container">
                                        @foreach($pendingApprovalTasks as $task)
                                            @include('page.kanban.partials.task_card', ['task' => $task, 'currentUser' => $user])
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- OPEN Column --}}
                        <div class="flex-shrink-0 w-full md:w-1/3 lg:w-1/4 xl:w-1/5">
                            <div class="flex flex-col rounded-lg overflow-hidden shadow-lg h-full">
                                <div class="bg-blue-500 p-3">
                                    <h2 class="text-xl font-semibold text-white text-center">OPEN</h2>
                                </div>
                                <div id="open-column" class="border-2 border-blue-500 rounded-b-lg p-4 kanban-column-body flex-1 bg-blue-50 dark:bg-gray-700">
                                    <div id="open-tasks" class="space-y-4 kanban-tasks-container">
                                        @foreach($openTasks as $task)
                                            @include('page.kanban.partials.task_card', ['task' => $task, 'currentUser' => $user])
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- COMPLETED Column --}}
                        <div class="flex-shrink-0 w-full md:w-1/3 lg:w-1/4 xl:w-1/5">
                            <div class="flex flex-col rounded-lg overflow-hidden shadow-lg h-full">
                                <div class="bg-green-500 p-3">
                                    <h2 class="text-xl font-semibold text-white text-center">COMPLETED</h2>
                                </div>
                                <div id="completed-column" class="border-2 border-green-500 rounded-b-lg p-4 kanban-column-body flex-1 bg-green-50 dark:bg-gray-700">
                                    <div id="completed-tasks" class="space-y-4 kanban-tasks-container">
                                        @foreach($completedTasks as $task)
                                            @include('page.kanban.partials.task_card', ['task' => $task, 'currentUser' => $user])
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- CANCELLED Column --}}
                        <div class="flex-shrink-0 w-full md:w-1/3 lg:w-1/4 xl:w-1/5">
                            <div class="flex flex-col rounded-lg overflow-hidden shadow-lg h-full">
                                <div class="bg-orange-500 p-3">
                                    <h2 class="text-xl font-semibold text-white text-center">CANCELLED</h2>
                                </div>
                                <div id="cancelled-column" class="border-2 border-orange-500 rounded-b-lg p-4 kanban-column-body flex-1 bg-orange-50 dark:bg-gray-700">
                                    <div id="cancelled-tasks" class="space-y-4 kanban-tasks-container">
                                        @foreach($cancelledTasks as $task)
                                            @include('page.kanban.partials.task_card', ['task' => $task, 'currentUser' => $user])
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- REJECTED Column --}}
                        <div class="flex-shrink-0 w-full md:w-1/3 lg:w-1/4 xl:w-1/5">
                            <div class="flex flex-col rounded-lg overflow-hidden shadow-lg h-full">
                                <div class="bg-red-600 p-3">
                                    <h2 class="text-xl font-semibold text-white text-center">REJECTED</h2>
                                </div>
                                <div id="rejected-column" class="border-2 border-red-600 rounded-b-lg p-4 kanban-column-body flex-1 bg-red-50 dark:bg-gray-700">
                                    <div id="rejected-tasks" class="space-y-4 kanban-tasks-container">
                                        @foreach($rejectedTasks as $task)
                                            @include('page.kanban.partials.task_card', ['task' => $task, 'currentUser' => $user])
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- CLOSED Column --}}
                        <div class="flex-shrink-0 w-full md:w-1/3 lg:w-1/4 xl:w-1/5">
                            <div class="flex flex-col rounded-lg overflow-hidden shadow-lg h-full">
                                 <div class="bg-gray-500 p-3">
                                    <h2 class="text-xl font-semibold text-white text-center">CLOSED (30 Hari Terakhir)</h2>
                                </div>
                                <div id="closed-column" class="border-2 border-gray-500 rounded-b-lg p-4 kanban-column-body flex-1 bg-gray-100 dark:bg-gray-700">
                                    <div id="closed-tasks" class="space-y-4 kanban-tasks-container">
                                         @foreach($closedTasks as $task)
                                            @include('page.kanban.partials.task_card', ['task' => $task, 'currentUser' => $user])
                                        @endforeach
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
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .task-card {
            /* background-color: #FFFFFF; */ /* Will be set by dark mode or default */
            /* border: 1px solid #e5e7eb; */ /* Will be set by dark mode or default */
        }
        .kanban-column-body {
            /* background-color: #f9fafb; */ /* Overridden by specific column bg */
            min-height: 400px; /* Increased min-height */
        }
        .task-content-scrollable {
            max-height: 120px; /* Slightly increased */
            overflow-y: auto;
            scrollbar-width: thin;
            /* scrollbar-color: #9CA3AF #E5E7EB; */ /* Adjust for dark mode if needed */
            /* background-color: #f3f4f6; */ /* Handled by dark mode */
            padding: 0.5rem;
            border-radius: 0.25rem;
        }
        .dark .task-content-scrollable {
            scrollbar-color: #4B5563 #374151;
        }
        .task-content-scrollable::-webkit-scrollbar { width: 8px; }
        .task-content-scrollable::-webkit-scrollbar-track { background: #E5E7EB; border-radius: 10px; }
        .dark .task-content-scrollable::-webkit-scrollbar-track { background: #374151; }
        .task-content-scrollable::-webkit-scrollbar-thumb { background-color: #9CA3AF; border-radius: 10px; border: 2px solid #E5E7EB; }
        .dark .task-content-scrollable::-webkit-scrollbar-thumb { background-color: #4B5563; border: 2px solid #374151; }

        .card-header-label {
            font-size: 0.75rem; /* 12px */
            /* color: #6B7280; */ /* Handled by dark mode */
            display: block;
            margin-bottom: 0.125rem;
        }
        .card-header-value {
            font-size: 0.875rem; /* 14px */
            /* color: #1F2937; */ /* Handled by dark mode */
            font-weight: 500;
        }
        .status-badge {
            font-size: 0.7rem; /* Slightly smaller */
            padding: 0.2rem 0.5rem;
            border-radius: 0.375rem;
            font-weight: 600;
            text-transform: uppercase;
            line-height: 1; /* Ensure consistent height */
        }
        /* Light mode badges */
        .status-badge.status-pending_approval { background-color: #FEF3C7 !important; color: #92400E !important;} /* amber-200, amber-800 */
        .status-badge.status-open { background-color: #DBEAFE !important; color: #1D4ED8 !important;} /* blue-100, blue-700 */
        .status-badge.status-completed { background-color: #D1FAE5 !important; color: #047857 !important;} /* green-100, green-700 */
        .status-badge.status-cancelled { background-color: #FFEDD5 !important; color: #9A3412 !important;} /* orange-100, orange-700 */
        .status-badge.status-rejected { background-color: #FEE2E2 !important; color: #991B1B !important;} /* red-100, red-700 */
        .status-badge.status-closed { background-color: #E5E7EB !important; color: #374151 !important;} /* gray-200, gray-700 */

        /* Dark mode badges (Example - adjust colors as needed) */
        .dark .status-badge.status-pending_approval { background-color: #78350F !important; color: #FEF3C7 !important;} /* amber-800, amber-200 */
        .dark .status-badge.status-open { background-color: #1E40AF !important; color: #DBEAFE !important;}
        .dark .status-badge.status-completed { background-color: #065F46 !important; color: #D1FAE5 !important;}
        .dark .status-badge.status-cancelled { background-color: #7C2D12 !important; color: #FFEDD5 !important;}
        .dark .status-badge.status-rejected { background-color: #7F1D1D !important; color: #FEE2E2 !important;}
        .dark .status-badge.status-closed { background-color: #4B5563 !important; color: #E5E7EB !important;}

        .task-content-scrollable pre {
            /* color: #1f2937; */ /* Handled by dark mode */
            white-space: pre-wrap;
            word-break: break-word;
        }
        .dark .text-gray-700 { color: #D1D5DB; /* gray-300 */ }
        .dark .text-gray-800 { color: #E5E7EB; /* gray-200 */ }
        .dark .bg-gray-100 { background-color: #374151; /* gray-700 */ }
        .dark .border-gray-300 { border-color: #4B5563; /* gray-600 */ }
        .dark .border-gray-200 { border-color: #4B5563; /* gray-600 */ }
        .dark .card-header-label { color: #9CA3AF; /* gray-400 */ }
        .dark .card-header-value { color: #F3F4F6; /* gray-100 */ }
        .dark .task-card { background-color: #1F2937; border-color: #374151; /* gray-800, gray-700 */ }

        /* Department Badge Colors - Light Mode */
        .dept-badge.dept-engineering-maintainance { background-color: #FECACA; color: #991B1B; } /* red-200, red-700 */
        .dept-badge.dept-finance-admin { background-color: #FEF08A; color: #854D0E; } /* yellow-200, yellow-700 */
        .dept-badge.dept-hcd { background-color: #A7F3D0; color: #047857; } /* green-200, green-700 */
        .dept-badge.dept-manufacturing { background-color: #BFDBFE; color: #1D4ED8; } /* blue-200, blue-700 */
        .dept-badge.dept-qm-hse { background-color: #C7D2FE; color: #4338CA; } /* indigo-200, indigo-700 */
        .dept-badge.dept-rd { background-color: #DDD6FE; color: #6D28D9; } /* violet-200, violet-700 */
        .dept-badge.dept-sales-marketing { background-color: #FBCFE8; color: #9D174D; } /* pink-200, pink-700 */
        .dept-badge.dept-supply-chain { background-color: #A5F3FC; color: #0E7490; } /* cyan-200, cyan-700 */
        .dept-badge.dept-secret { background-color: #D1D5DB; color: #374151; } /* gray-300, gray-700 */
        .dept-badge.dept-default { background-color: #E5E7EB; color: #4B5563; } /* gray-200, gray-600 */

        /* Department Badge Colors - Dark Mode */
        .dark .dept-badge.dept-engineering-maintainance { background-color: #7F1D1D; color: #FECACA; }
        .dark .dept-badge.dept-finance-admin { background-color: #713F12; color: #FEF08A; }
        .dark .dept-badge.dept-hcd { background-color: #065F46; color: #A7F3D0; }
        .dark .dept-badge.dept-manufacturing { background-color: #1E40AF; color: #BFDBFE; }
        .dark .dept-badge.dept-qm-hse { background-color: #3730A3; color: #C7D2FE; }
        .dark .dept-badge.dept-rd { background-color: #5B21B6; color: #DDD6FE; }
        .dark .dept-badge.dept-sales-marketing { background-color: #831843; color: #FBCFE8; }
        .dark .dept-badge.dept-supply-chain { background-color: #155E75; color: #A5F3FC; }
        .dark .dept-badge.dept-secret { background-color: #4B5563; color: #D1D5DB; }
        .dark .dept-badge.dept-default { background-color: #374151; color: #9CA3AF; }

    </style>
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const taskModal = document.getElementById('taskModal');
        const openTaskModalBtn = document.getElementById('openTaskModalBtn');
        const closeTaskModalBtn = document.getElementById('closeTaskModalBtn');
        const cancelTaskFormBtn = document.getElementById('cancelTaskFormBtn');
        const taskForm = document.getElementById('taskForm');

        const pendingApprovalTasksContainer = document.getElementById('pending-approval-tasks');
        const openTasksContainer = document.getElementById('open-tasks');
        const completedTasksContainer = document.getElementById('completed-tasks');
        const cancelledTasksContainer = document.getElementById('cancelled-tasks');
        const rejectedTasksContainer = document.getElementById('rejected-tasks');
        const closedTasksContainer = document.getElementById('closed-tasks');

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const currentUser = @json($user); // Assuming $user is passed and has id, isSuperAdmin(), isAdminProject() methods available via User model

        if (openTaskModalBtn && taskModal) {
            openTaskModalBtn.addEventListener('click', () => {
                taskModal.classList.remove('hidden');
                if(taskForm) taskForm.reset();
                document.getElementById('id_job_modal').focus();
            });
        }
        if (closeTaskModalBtn && taskModal) {
            closeTaskModalBtn.addEventListener('click', () => taskModal.classList.add('hidden'));
        }
        if (cancelTaskFormBtn && taskModal) {
            cancelTaskFormBtn.addEventListener('click', () => taskModal.classList.add('hidden'));
        }
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && taskModal && !taskModal.classList.contains('hidden')) {
                taskModal.classList.add('hidden');
            }
        });

        function formatDate(dateString, includeTime = false) {
            if (!dateString) return '---';
            try {
                const date = new Date(dateString);
                if (isNaN(date.getTime())) return 'Invalid Date';
                const options = { day: '2-digit', month: 'short', year: 'numeric' };
                if (includeTime) {
                    options.hour = '2-digit';
                    options.minute = '2-digit';
                    options.hour12 = false; // Use 24-hour format for consistency
                }
                return date.toLocaleDateString('id-ID', options);
            } catch (e) {
                console.error("Error formatting date:", dateString, e);
                return 'Error Date';
            }
        }

        function getStatusText(status) {
            if (!status) return 'UNKNOWN';
            return status.replace(/_/g, ' ').toUpperCase();
        }

        function getDepartmentSlug(departmentName) {
            if (!departmentName) return 'default';
            return departmentName.toLowerCase().replace(/\s*&\s*|\s+/g, '-').replace(/[^a-z0-9-]/g, '');
        }


        function generateButtonsHTML(task, loggedInUser) {
            let buttonsHtml = '';
            const commonButtonClass = "text-xs px-3 py-1.5 rounded-md font-semibold focus:outline-none focus:ring-2 focus:ring-offset-1 whitespace-nowrap";
            // These checks should ideally come from the User model methods if available in JS context,
            // or be simplified if currentUser object has these flags directly.
            const isSuperAdmin = loggedInUser && loggedInUser.is_super_admin; // Assuming you add this to the $user json
            const isAdminProject = loggedInUser && loggedInUser.is_admin_project; // Assuming you add this
            const isPengaju = loggedInUser && task.pengaju_id && loggedInUser.id === task.pengaju_id;
            const isTargetDepartmentMember = loggedInUser && loggedInUser.department_id === task.department_id; // For approval

            if (task.status === 'pending_approval') {
                // Approval/Rejection usually via email. UI buttons could be added for specific roles.
                // Example: if (isTargetDepartmentMember || isAdminProject || isSuperAdmin) {
                //  buttonsHtml += `<button data-action="approve_from_card" data-task-id="${task.id}" class="${commonButtonClass} bg-sky-500 hover:bg-sky-600 text-white focus:ring-sky-400">Approve</button>`;
                //  buttonsHtml += `<button data-action="reject_from_card" data-task-id="${task.id}" class="${commonButtonClass} bg-pink-500 hover:bg-pink-600 text-white focus:ring-pink-400">Reject</button>`;
                // }
            } else if (task.status === 'open') {
                if (isPengaju || isAdminProject || isSuperAdmin) {
                    buttonsHtml += `<button data-action="complete" data-task-id="${task.id}" class="${commonButtonClass} bg-green-500 hover:bg-green-600 text-white focus:ring-green-400">Complete</button>`;
                }
                if (isPengaju) { // Only requester can cancel
                    buttonsHtml += `<button data-action="cancel" data-task-id="${task.id}" class="${commonButtonClass} bg-orange-500 hover:bg-orange-600 text-white focus:ring-orange-400">Cancel</button>`;
                }
            } else if (task.status === 'completed') {
                if (isPengaju) { // Only requester can archive
                    buttonsHtml += `<button data-action="archive" data-task-id="${task.id}" class="${commonButtonClass} bg-gray-500 hover:bg-gray-600 text-white focus:ring-gray-400">Archive</button>`;
                }
                if (isAdminProject || isSuperAdmin) {
                     buttonsHtml += `<button data-action="reopen" data-task-id="${task.id}" class="${commonButtonClass} bg-blue-500 hover:bg-blue-600 text-white focus:ring-blue-400">Re-Open</button>`;
                }
            } else if (task.status === 'rejected' || task.status === 'cancelled') {
                 if (isAdminProject || isSuperAdmin) {
                    buttonsHtml += `<button data-action="reopen" data-task-id="${task.id}" class="${commonButtonClass} bg-blue-500 hover:bg-blue-600 text-white focus:ring-blue-400">Re-Open</button>`;
                    buttonsHtml += `<button data-action="delete_permanently" data-task-id="${task.id}" class="${commonButtonClass} bg-red-700 hover:bg-red-800 text-white focus:ring-red-600">Delete</button>`;
                }
            }
            return buttonsHtml;
        }

        function createTaskCardHTML(task) {
            const pengajuName = task.pengaju ? task.pengaju.name : 'N/A';
            const departmentName = task.department ? task.department.department_name : 'N/A';
            const departmentSlug = getDepartmentSlug(departmentName);
            const penutupName = task.penutup ? task.penutup.name : 'N/A';
            const approverName = task.approver ? task.approver.name : 'N/A';

            // Dynamic header background based on status
            let headerBgClass = 'bg-gray-400 dark:bg-gray-600'; // Default
            if (task.status === 'pending_approval') headerBgClass = 'bg-yellow-500 dark:bg-yellow-600';
            else if (task.status === 'open') headerBgClass = 'bg-blue-500 dark:bg-blue-600';
            else if (task.status === 'completed') headerBgClass = 'bg-green-500 dark:bg-green-600';
            else if (task.status === 'cancelled') headerBgClass = 'bg-orange-500 dark:bg-orange-600';
            else if (task.status === 'rejected') headerBgClass = 'bg-red-600 dark:bg-red-700';
            else if (task.status === 'closed') headerBgClass = 'bg-gray-500 dark:bg-gray-700';


            let cardHTML = `
                <div class="task-card bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg shadow-md flex flex-col h-full" data-task-id="${task.id}">
                    <div class="flex-grow">
                        <div class="flex justify-between items-center ${headerBgClass} text-white p-3 rounded-t-lg">
                            <div>
                                <span class="block text-xs opacity-80">ID JOB</span>
                                <span class="text-sm font-semibold">${task.id_job}</span>
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
                                    <span class="dept-badge dept-${departmentSlug} text-xs font-medium px-2 py-0.5 rounded-md inline-block truncate" title="${departmentName}">${departmentName}</span>
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

            if (task.status === 'rejected' || (task.approver_id && (task.status === 'open' || task.status === 'completed'))) { // Show if approved or rejected
                cardHTML += `
                        <div class="pt-1 border-t border-gray-200 dark:border-gray-700 mt-2">
                             <span class="card-header-label text-gray-500 dark:text-gray-400">${task.status === 'rejected' ? 'Processed by (Rejected)' : 'Processed by (Approved)'}</span>
                             <span class="card-header-value text-gray-900 dark:text-gray-100">${approverName} at ${formatDate(task.approved_at, true)}</span>
                        </div>`;
                if (task.status === 'rejected' && task.rejection_reason) {
                    cardHTML += `
                        <div class_("pt-1">
                             <span class="card-header-label text-gray-500 dark:text-gray-400">Rejection Reason</span>
                             <span class="card-header-value text-red-600 dark:text-red-400 text-xs">${task.rejection_reason}</span>
                        </div>`;
                }
            }

            if (task.status === 'closed' && task.penutup) {
                cardHTML += `
                        <div class="pt-1 border-t border-gray-200 dark:border-gray-700 mt-2">
                             <span class="card-header-label text-gray-500 dark:text-gray-400">Closed by</span>
                             <span class="card-header-value text-gray-900 dark:text-gray-100">${penutupName} at ${formatDate(task.closed_at, true)}</span>
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
                                <pre class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap break-words">${task.list_job}</pre>
                            </div>
                        </div>
                    </div>
                    <div class="mt-auto p-3 border-t border-gray-200 dark:border-gray-600 flex flex-wrap gap-2 justify-end">
                        ${generateButtonsHTML(task, currentUser)}
                    </div>
                </div>`;
            return cardHTML;
        }


        function reRenderTask(updatedTask) {
            const oldCard = document.querySelector(`.task-card[data-task-id="${updatedTask.id}"]`);
            if (oldCard) oldCard.remove();

            const newCardHTML = createTaskCardHTML(updatedTask);
            let targetContainer;
            if (updatedTask.status === 'pending_approval') targetContainer = pendingApprovalTasksContainer;
            else if (updatedTask.status === 'open') targetContainer = openTasksContainer;
            else if (updatedTask.status === 'completed') targetContainer = completedTasksContainer;
            else if (updatedTask.status === 'cancelled') targetContainer = cancelledTasksContainer;
            else if (updatedTask.status === 'rejected') targetContainer = rejectedTasksContainer;
            else if (updatedTask.status === 'closed') targetContainer = closedTasksContainer;

            if (targetContainer) {
                // Insert at the beginning (top) of the column
                targetContainer.insertAdjacentHTML('afterbegin', newCardHTML);
            } else {
                console.error("Could not find target container for status:", updatedTask.status);
            }
        }

        if (taskForm) {
            taskForm.addEventListener('submit', async function(event) {
                event.preventDefault();
                const formData = new FormData(taskForm);
                const data = Object.fromEntries(formData.entries());

                // Client-side validation example (can be more extensive)
                if (!data.id_job || !data.department_id || !data.area || !data.list_job) {
                    Swal.fire('Error', 'ID JOB, Department, Location, and Description are required.', 'error');
                    return;
                }

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
                        let htmlMessage = 'Failed to save task:<br>';
                        if (responseData.errors) {
                            htmlMessage += '<ul class="text-left list-disc list-inside">';
                            for (const field in responseData.errors) {
                                htmlMessage += `<li>${responseData.errors[field].join(', ')}</li>`;
                            }
                            htmlMessage += '</ul>';
                        } else if (responseData.message) {
                            htmlMessage += responseData.message;
                        } else {
                            htmlMessage += `Error ${response.status}: ${response.statusText}`;
                        }
                        Swal.fire({ icon: 'error', title: 'Validation Failed', html: htmlMessage });
                        return;
                    }

                    const newTask = responseData; // Assuming successful response is the task object
                    // No need to call createTaskCardHTML if reRenderTask handles it
                    reRenderTask(newTask); // This will add it to pending_approval
                    taskModal.classList.add('hidden');
                    taskForm.reset();
                    Swal.fire('Success!', 'Task submitted for approval.', 'success');

                } catch (error) {
                    console.error('Error submitting task:', error);
                    Swal.fire({ icon: 'error', title: 'Submission Error', text: 'Could not save task. Check connection.' });
                }
            });
        }

        async function handleTaskAction(taskId, action) {
            let url, method, newStatus, confirmationTitle, confirmationText, successMessage;
            let requiresConfirmation = false;
            let requestBody = {};

            switch (action) {
                case 'complete':
                    newStatus = 'completed'; successMessage = 'Task marked as COMPLETED.'; break;
                case 'archive':
                    newStatus = 'closed'; successMessage = 'Task ARCHIVED.'; break;
                case 'cancel':
                    newStatus = 'cancelled'; successMessage = 'Task CANCELLED.'; break;
                case 'reopen':
                    newStatus = 'open'; successMessage = 'Task RE-OPENED.'; break;
                case 'delete_permanently':
                    method = 'DELETE';
                    confirmationTitle = 'Delete Task Permanently?';
                    confirmationText = "This action cannot be undone.";
                    requiresConfirmation = true;
                    successMessage = 'Task permanently deleted.';
                    break;
                default: console.error('Unknown action:', action); return;
            }

            if (newStatus) { // For status updates
                url = `/tasks/${taskId}/status`;
                method = 'PATCH';
                requestBody = { status: newStatus };
            } else if (method === 'DELETE') { // For deletion
                url = `/tasks/${taskId}`;
            }


            if (requiresConfirmation) {
                const result = await Swal.fire({
                    title: confirmationTitle, text: confirmationText, icon: 'warning',
                    showCancelButton: true, confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33', confirmButtonText: 'Yes, proceed!', cancelButtonText: 'No, cancel'
                });
                if (!result.isConfirmed) return;
            }

            try {
                const fetchOptions = {
                    method: method,
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                };
                if (method === 'PATCH' || method === 'POST') {
                    fetchOptions.body = JSON.stringify(requestBody);
                }

                const response = await fetch(url, fetchOptions);
                const responseData = await response.json().catch(() => ({ message: "Error processing server response."}));

                if (!response.ok) {
                    let htmlMessage = 'An error occurred:<br>';
                     if (responseData.message) htmlMessage += responseData.message;
                    else if (responseData.errors) {
                        htmlMessage += '<ul class="text-left list-disc list-inside">';
                        for (const field in responseData.errors) htmlMessage += `<li>${responseData.errors[field].join(', ')}</li>`;
                        htmlMessage += '</ul>';
                    } else htmlMessage += `Error ${response.status}: ${response.statusText}`;
                    Swal.fire({ icon: 'error', title: 'Oops...', html: htmlMessage });
                    return;
                }

                if (method === 'DELETE') {
                    const cardToRemove = document.querySelector(`.task-card[data-task-id="${taskId}"]`);
                    if (cardToRemove) cardToRemove.remove();
                } else {
                    reRenderTask(responseData); // responseData should be the updated task object
                }
                Swal.fire('Success!', successMessage, 'success');

            } catch (error) {
                console.error(`Error during action ${action} for task ${taskId}:`, error);
                Swal.fire({ icon: 'error', title: 'Network Error', text: 'Could not connect to the server.' });
            }
        }

        // Event listener for action buttons on cards
        document.addEventListener('click', function(event) {
            const target = event.target.closest('button[data-action]');
            if (!target) return;

            const action = target.dataset.action;
            const taskId = target.dataset.taskId;

            if (action && taskId) {
                handleTaskAction(taskId, action);
            }
        });
    });
    </script>
    @endpush
</x-app-layout>