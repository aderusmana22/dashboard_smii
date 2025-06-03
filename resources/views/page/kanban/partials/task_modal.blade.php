<div id="taskModal" class="fixed inset-0 bg-gray-800 bg-opacity-75 overflow-y-auto h-full w-full flex items-center justify-center hidden z-50 p-4">
    <div class="relative bg-white w-full max-w-lg mx-auto p-6 rounded-lg shadow-xl">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-700">Tambah Tugas Baru</h2>
            <button id="closeTaskModalBtn" class="text-gray-500 hover:text-gray-700 text-2xl" aria-label="Close modal">×</button>
        </div>
        <form id="taskForm" class="space-y-4">
            <div>
                <label for="id_job_modal" class="block text-sm font-medium text-gray-700">ID JOB <span class="text-red-500">*</span></label>
                <input type="text" id="id_job_modal" name="id_job" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2">
            </div>
            <div>
                <label for="department_id_modal" class="block text-sm font-medium text-gray-700">To Departement<span class="text-red-500">*</span></label>
                <select id="department_id_modal" name="department_id" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2">
                    <option value="">Pilih Departemen</option>
                    @if(isset($departments) && $departments->count() > 0)
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div>
                <label for="area_modal" class="block text-sm font-medium text-gray-700">Location <span class="text-red-500">*</span></label>
                <input type="text" id="area_modal" name="area" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2">
            </div>
            <div>
                <label for="tanggal_job_mulai_modal" class="block text-sm font-medium text-gray-700">Start (Otomatis hari ini jika kosong)</label>
                <input type="date" id="tanggal_job_mulai_modal" name="tanggal_job_mulai"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2">
            </div>
            <div>
                <label for="list_job_modal" class="block text-sm font-medium text-gray-700">Description<span class="text-red-500">*</span></label>
                <textarea id="list_job_modal" name="list_job" rows="4" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2"></textarea>
            </div>
            <div class="flex justify-end space-x-3 pt-4">
                <button type="button" id="cancelTaskFormBtn"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2">
                    Batal
                </button>
                <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Simpan Tugas
                </button>
            </div>
        </form>
    </div>
</div>
