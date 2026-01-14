<div id="moveStageModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-gray-600 bg-opacity-75 transition-opacity">
    <div class="flex items-center justify-center min-h-screen">
        <div class="relative bg-white dark:bg-gray-800 w-full max-w-lg mx-auto p-6 rounded-lg shadow-xl">
            <h3 id="moveStageTitle" class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Move Stage</h3>
            <form id="moveStageForm" class="space-y-4" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <input type="hidden" id="move_job_id" name="job_id">
                <input type="hidden" id="move_target_status" name="status">
                
                <div class="bg-blue-50 dark:bg-blue-900/30 p-3 rounded-md text-sm text-blue-800 dark:text-blue-200 mb-4">
                    <p>Moving to the next stage requires documentation. The 3-day timer will reset upon submission.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Note / Progress Report <span class="text-red-500">*</span></label>
                    <textarea name="note" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm" required placeholder="Describe work done or readiness..."></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Evidence Photo <span class="text-red-500">*</span></label>
                    <input type="file" name="attachments[]" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" accept="image/*" multiple required>
                    <p class="text-xs text-gray-500 mt-1">Proof of current stage completion is required to proceed.</p>
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" class="cancel-modal-btn bg-gray-200 hover:bg-gray-300 text-gray-800 dark:bg-gray-600 dark:text-white font-medium py-2 px-4 rounded-md">Cancel</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md">Confirm Move</button>
                </div>
            </form>
        </div>
    </div>
</div>