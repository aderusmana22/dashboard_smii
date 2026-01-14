<div id="createJobModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-gray-600 bg-opacity-75 transition-opacity">
    <div class="flex items-center justify-center min-h-screen">
        <div class="relative bg-white dark:bg-gray-800 w-full max-w-lg mx-auto p-6 rounded-lg shadow-xl">
            <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Create New Job</h3>
            <form id="createJobForm" class="space-y-4" enctype="multipart/form-data">
                @csrf
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Area</label>
                        <select name="area_id" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 shadow-sm" required>
                            <option value="" disabled selected>Select Area</option>
                            @foreach($areas as $id => $name) <option value="{{ $id }}">{{ $name }}</option> @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Initial Dept</label>
                        <select name="to_department_id" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 shadow-sm" required>
                            <option value="" disabled selected>Select Dept</option>
                            @foreach($departments as $id => $name) <option value="{{ $id }}">{{ $name }}</option> @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Date</label>
                        <input type="date" name="start_date" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 shadow-sm" required value="{{ date('Y-m-d') }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Deadline</label>
                        <input type="date" name="deadline" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 shadow-sm" required>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Job Description</label>
                    <textarea name="list_job" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 shadow-sm" required></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Attachments (Optional)</label>
                    <input type="file" name="attachments[]" class="block w-full text-sm text-gray-500 mt-1" multiple>
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" class="cancel-modal-btn bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-md">Cancel</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md">Create Job</button>
                </div>
            </form>
        </div>
    </div>
</div>