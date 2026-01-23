<div id="moveStageModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-gray-900 bg-opacity-90 transition-opacity">
    <div class="flex items-center justify-center min-h-screen">
        <div class="relative bg-white dark:bg-gray-800 w-full max-w-lg mx-auto p-6 rounded-lg shadow-xl border-t-4 border-blue-600">
            <h3 id="moveStageTitle" class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">Move Stage</h3>
            
            <form id="moveStageForm" class="space-y-4" enctype="multipart/form-data">
                @csrf
                @method('PATCH') <!-- Method PATCH sesuai route change-status -->
                <input type="hidden" id="move_job_id" name="job_id">
                <input type="hidden" id="move_target_status" name="status">
                
                <div class="bg-blue-50 dark:bg-blue-900/30 p-3 rounded-md text-sm text-blue-800 dark:text-blue-200 mb-4">
                    <p>Moving to the next stage resets the 3-day SLA timer. You can also forward this job to another department simultaneously.</p>
                </div>

                <!-- NEW: Opsi Pindah Departemen -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">
                        Assign to Department <span class="text-gray-400 font-normal">(Optional)</span>
                    </label>
                    <select name="to_department_id" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-blue-500 focus:border-blue-500">
                        <option value="" selected>— Keep in Current Department —</option>
                        @foreach($departments as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Leave empty if the job stays in the current department.</p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Note / Progress Report <span class="text-red-500">*</span></label>
                    <textarea name="note" rows="3" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-blue-500 focus:border-blue-500" required placeholder="Describe work done or instructions for the next department..."></textarea>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Evidence / Attachment <span class="text-red-500">*</span></label>
                    <input type="file" name="attachments[]" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" multiple required>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t dark:border-gray-700">
                    <button type="button" class="cancel-modal-btn px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 font-medium">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-bold shadow">Confirm Move</button>
                </div>
            </form>
        </div>
    </div>
</div>