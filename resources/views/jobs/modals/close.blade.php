<div id="closeJobModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-gray-600 bg-opacity-75 transition-opacity">
    <div class="flex items-center justify-center min-h-screen">
        <div class="relative bg-white dark:bg-gray-800 w-full max-w-md mx-auto p-6 rounded-lg shadow-xl">
            <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Confirm Close Job</h3>
            <form id="closeJobForm">
                @csrf
                <input type="hidden" id="close_job_id" name="job_id">
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    Are you sure you want to close this job? This will archive the job and no further changes can be made.
                </p>
                <div class="flex justify-end space-x-3">
                    <button type="button" class="cancel-modal-btn bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-md">Cancel</button>
                    <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-md">Yes, Close Job</button>
                </div>
            </form>
        </div>
    </div>
</div>