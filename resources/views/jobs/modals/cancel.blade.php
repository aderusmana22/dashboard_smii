<div id="cancelJobModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-gray-900 bg-opacity-90 transition-opacity">
    <div class="flex items-center justify-center min-h-screen">
        <div class="relative bg-white dark:bg-gray-800 w-full max-w-md mx-auto p-6 rounded-lg shadow-xl border-t-4 border-orange-500">
            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">Cancel Job</h3>
            <p class="text-sm text-gray-500 mb-4">Are you sure? This will stop the process and notify the current department.</p>
            
            <form id="cancelJobForm">
                @csrf
                @method('PATCH')
                <input type="hidden" id="cancel_job_id" name="job_id">
                
                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Reason for Cancellation</label>
                    <textarea name="reason" rows="3" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 focus:ring-orange-500 focus:border-orange-500" required placeholder="Why is this job being cancelled?"></textarea>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" class="cancel-modal-btn px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 font-medium">Keep Job</button>
                    <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded hover:bg-orange-700 font-bold">Yes, Cancel It</button>
                </div>
            </form>
        </div>
    </div>
</div>