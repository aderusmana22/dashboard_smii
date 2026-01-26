<div id="cancelJobModal"
    class="hidden fixed inset-0 z-50 overflow-y-auto backdrop-blur-xl bg-gray-900/50 transition-opacity">

    <div class="flex items-center justify-center min-h-screen p-4">

        <div class="relative bg-white w-full max-w-md mx-auto p-6 rounded-lg shadow-2xl border-t-4 border-orange-500">

            <h3 class="text-xl font-bold text-gray-900 mb-2">Cancel Job</h3>

            <p class="text-sm text-gray-500 mb-4">Are you sure? This will stop the process and notify the current
                department.</p>

            <form id="cancelJobForm">
                @csrf
                @method('PATCH')
                <input type="hidden" id="cancel_job_id" name="job_id">

                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Reason for Cancellation</label>

                    <textarea name="reason" rows="3"
                        class="w-full rounded-md border-gray-300 focus:ring-orange-500 focus:border-orange-500 shadow-sm"
                        required placeholder="Why is this job being cancelled?"></textarea>
                </div>

                <div class="flex justify-end space-x-3">

                    <button type="button" onclick="document.getElementById('cancelJobModal').classList.add('hidden')"
                        class="modal-cancel-button px-4 py-2 bg-gray-500 text-gray-800 rounded hover:bg-gray-300 font-medium transition">
                        Keep Job
                    </button>

                    <button type="submit"
                        class="px-4 py-2 bg-red-500  text-white rounded hover:bg-orange-700 font-bold transition shadow-lg">
                        Yes, Cancel It
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>