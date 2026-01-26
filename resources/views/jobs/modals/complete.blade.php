<div id="completeJobModal"
    class="hidden fixed inset-0 z-50 overflow-y-auto backdrop-blur-xl bg-gray-900/50 transition-opacity">
    <div class="flex items-center justify-center min-h-screen p-4">

        <div class="relative bg-white w-full max-w-lg mx-auto p-6 rounded-lg shadow-2xl border border-gray-100">

            <h3 class="text-xl font-bold text-gray-900 mb-4">Complete Job</h3>

            <form id="completeJobForm" class="space-y-4" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="complete_job_id" name="job_id">

                <div>

                    <label class="block text-sm font-medium text-gray-700">Final Notes</label>

                    <textarea name="note"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500"
                        rows="3" required placeholder="Completion report..."></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Final Evidence (Required)</label>

                    <input type="file" name="attachments[]" class="block w-full text-sm text-gray-500 mt-1" multiple
                        required>
                </div>

                <div class="flex justify-end space-x-3 pt-4">

                    <button type="button" onclick="document.getElementById('completeJobModal').classList.add('hidden')"
                        class="modal-cancel-button bg-gray-500 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-md transition">
                        Cancel
                    </button>

                    <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-md transition shadow-lg">
                        Mark as Completed
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>