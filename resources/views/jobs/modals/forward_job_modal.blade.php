<div id="forwardJobModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-gray-600 bg-opacity-75 transition-opacity">
    <div class="flex items-center justify-center min-h-screen">
        <div class="relative bg-white dark:bg-gray-800 w-full max-w-lg mx-auto p-6 rounded-lg shadow-xl">
            <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Forward Job</h3>
            <form id="forwardJobForm" class="space-y-4">
                <input type="hidden" id="forward_job_id" name="job_id">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Forward to Department</label>
                    <select name="to_department_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm" required>
                         @foreach($departments as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                    <textarea name="note" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm" rows="3" required></textarea>
                </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" class="cancel-modal-btn bg-gray-200 hover:bg-gray-300 text-gray-800 dark:bg-gray-600 dark:hover:bg-gray-500 dark:text-gray-200 font-medium py-2 px-4 rounded-md">Cancel</button>
                    <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded-md">Forward</button>
                </div>
            </form>
        </div>
    </div>
</div>