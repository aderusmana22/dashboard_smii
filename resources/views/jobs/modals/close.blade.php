<div id="closeJobModal"
    class="hidden fixed inset-0 z-50 overflow-y-auto backdrop-blur-xl bg-gray-900/50 transition-opacity">
    <div class="flex items-center justify-center min-h-screen p-4">

        <div class="relative bg-white w-full max-w-md mx-auto p-6 rounded-lg shadow-2xl border border-gray-100">

            <h3 class="text-xl font-bold text-gray-900 mb-4">Confirm Close Job</h3>

            <form id="closeJobForm">
                @csrf
                <input type="hidden" id="close_job_id" name="job_id">

                <p class="text-gray-600 mb-6">
                    Are you sure you want to close this job? This will archive the job and no further changes can be
                    made.
                </p>

                <div class="flex justify-end space-x-3">

                    <button type="button" onclick="document.getElementById('closeJobModal').classList.add('hidden')"
                        class="modal-cancel-button bg-gray-500 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-md transition">
                        Cancel
                    </button>

                    <button type="submit"
                        class="bg-gray-800 hover:bg-gray-900 text-white font-bold py-2 px-4 rounded-md transition shadow-lg">
                        Yes, Close Job
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>