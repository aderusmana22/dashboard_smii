{{-- File: resources/views/page/kanban/reject_task_form.blade.php --}}
@php
// --- Initialize all variables to defaults FIRST ---
$loggedInUser = Auth::user();

// Task specific properties (primarily for when rendered by controller for email rejection link)
$taskId = null;
$taskIdJob = null;
$taskStatus = null; // Will be pending_approval in email link context

// These are set by the controller when handling GET /tasks/approval/{token}?action=reject
// For the JS-driven modal, $task, $token, $approvalDetail will be null/unset.
if (isset($task) && $task !== null) {
$taskId = $task->id;
$taskIdJob = $task->id_job;
$taskStatus = $task->status;
}
@endphp

@if (isset($task) && $task !== null && isset($token) && isset($approvalDetail))
{{-- This part is for when the user is rejecting via the email link (controller renders this view) --}}
{{-- Styled to match the JS-driven modal structure --}}
<div class="fixed inset-0 z-[100] overflow-y-auto bg-gray-600 bg-opacity-75 transition-opacity duration-300 ease-in-out" aria-labelledby="rejectTaskModalTitle" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        {{-- Background overlay --}}
        <div class="fixed inset-0" aria-hidden="true">
            <div class="absolute inset-0"></div> {{-- This div could be styled if needed, e.g. for a separate opacity layer --}}
        </div>

        {{-- Centering trick: This span is to trick the browser into centering the modal contents. --}}
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">​</span>

        {{-- Modal panel --}}
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form method="POST" action="{{ route('tasks.handle_approval', ['token' => $token, 'action' => 'reject']) }}">
                @csrf
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-800 sm:mx-0 sm:h-10 sm:w-10">
                            {{-- Icon for Rejection (e.g., ExclamationTriangle or similar, themed red) --}}
                            <svg class="h-6 w-6 text-red-600 dark:text-red-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="rejectTaskModalTitle">
                                Tolak Tugas: {{ $taskIdJob ?? 'N/A' }}
                            </h3>
                            <div class="mt-4">
                                <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Alasan Penolakan (Wajib Diisi):
                                </label>
                                <textarea id="notes" name="notes" rows="4" required autofocus
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2"
                                    placeholder="Berikan alasan mengapa tugas ini ditolak...">{{ old('notes') }}</textarea>
                                @error('notes')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-gray-800 sm:ml-3 sm:w-auto sm:text-sm">
                        Tolak Tugas Ini
                    </button>
                    <a href="{{ url('/') }}" {{-- Or a more appropriate "cancel" URL like the dashboard --}}
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@else
{{-- This part is the modal structure for JS-driven "cancel task" from Kanban board (index.blade.php) --}}
<div id="cancelTaskModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-gray-600 bg-opacity-75 transition-opacity duration-300 ease-in-out" aria-labelledby="cancelTaskModalTitle" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        {{-- Background overlay --}}
        <div class="fixed inset-0" aria-hidden="true">
            <div class="absolute inset-0"></div>
        </div>

        {{-- Centering trick --}}
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">​</span>

        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="cancelTaskForm"> {{-- JS will handle this form submission --}}
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-orange-100 dark:bg-orange-800 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-orange-600 dark:text-orange-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="cancelTaskModalTitle">
                                Batalkan Tugas: <span id="cancel_task_id_job_display" class="font-bold"></span>
                            </h3>
                            <input type="hidden" id="cancel_task_id_modal" name="cancel_task_id_modal">
                            <div class="mt-4">
                                <label for="cancel_reason_modal" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alasan Pembatalan (Wajib)</label>
                                <textarea id="cancel_reason_modal" name="cancel_reason_modal" rows="3" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2"
                                    placeholder="Jelaskan mengapa tugas ini dibatalkan..."></textarea>
                            </div>
                            <div class="mt-4">
                                <div class="flex items-center">
                                    <input id="requester_confirmation_cancel_modal" name="requester_confirmation_cancel" type="checkbox" required value="1"
                                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded">
                                    <label for="requester_confirmation_cancel_modal" class="ml-2 block text-sm text-gray-900 dark:text-gray-200">
                                        Saya mengkonfirmasi pembatalan tugas ini.
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-orange-600 text-base font-medium text-white hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 dark:focus:ring-offset-gray-800 sm:ml-3 sm:w-auto sm:text-sm">
                        Ya, Batalkan Tugas
                    </button>
                    <button type="button" id="closeCancelTaskModalBtn"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Tidak
                    </button>
                    {{-- This button is used internally by JS to trigger closing logic without submitting --}}
                    <button type="button" id="cancelCancelTaskFormBtn" class="hidden">Internal Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif