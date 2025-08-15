<!-- Create Job Modal -->
<div id="createJobModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-gray-600 bg-opacity-75 transition-opacity">
    <div class="flex items-center justify-center min-h-screen">
        <div class="relative bg-white dark:bg-gray-800 w-full max-w-lg mx-auto p-6 rounded-lg shadow-xl">
            <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Create New Job</h3>
            <form id="createJobForm" class="space-y-4" enctype="multipart/form-data">
                @csrf
                <div>
                    <label for="area_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Area</label>
                    <select name="area_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm" required @if($areas->isEmpty()) disabled @endif>
                        <option value="" selected disabled>-- Select Area --</option>
                        @forelse($areas as $id => $name) <option value="{{ $id }}">{{ $name }}</option> @empty <option value="" disabled>No areas available</option> @endforelse
                    </select>
                    @if($areas->isEmpty()) <p class="text-sm text-red-500 mt-1">Cannot create a job: No areas have been configured.</p> @endif
                </div>
                <div>
                    <label for="list_job" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Job Description</label>
                    <textarea name="list_job" rows="4" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm" required></textarea>
                </div>
                <div>
                    <label for="to_department_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Initial Department</label>
                    <select name="to_department_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm" required @if($departments->isEmpty()) disabled @endif>
                        <option value="" selected disabled>-- Select Department --</option>
                        @forelse($departments as $id => $name) <option value="{{ $id }}">{{ $name }}</option> @empty <option value="" disabled>No departments available</option> @endforelse
                    </select>
                    @if($departments->isEmpty()) <p class="text-sm text-red-500 mt-1">Cannot create a job: No departments have been configured.</p> @endif
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Attachments (Optional, Max 3)</label>
                    <input type="file" name="attachments[]" class="hidden file-input" multiple accept="image/*,application/pdf,.doc,.docx">
                    <button type="button" class="trigger-file-input mt-2 w-full bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold py-2 px-4 border border-blue-200 rounded-md">Choose or Add Files...</button>
                    <div class="file-preview-container mt-3 space-y-2"></div>
                </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" class="cancel-modal-btn bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-md">Cancel</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md" @if($areas->isEmpty() || $departments->isEmpty()) disabled @endif>Save Job</button>
                </div>
            </form>
        </div>
    </div>
</div>