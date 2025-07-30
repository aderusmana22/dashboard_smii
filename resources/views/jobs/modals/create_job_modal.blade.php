<div id="createJobModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-gray-600 bg-opacity-75 transition-opacity">
<div class="flex items-center justify-center min-h-screen">
<div class="relative bg-white dark:bg-gray-800 w-full max-w-lg mx-auto p-6 rounded-lg shadow-xl">
<h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Create New Job</h3>
<form id="createJobForm" class="space-y-4">
<div>
<label for="area" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Area</label>
<input type="text" name="area" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
</div>
<div>
<label for="list_job" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Job Description</label>
<textarea name="list_job" rows="4" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required></textarea>
</div>
<div>
<label for="to_department_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Initial Department</label>
<select name="to_department_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
<option value="" selected disabled>-- Select Department --</option>
@foreach($departments as $id => $name)
<option value="{{ $id }}">{{ $name }}</option>
@endforeach
</select>
</div>
<div class="flex justify-end space-x-3 pt-4">
<button type="button" class="cancel-modal-btn bg-gray-200 hover:bg-gray-300 text-gray-800 dark:bg-gray-600 dark:hover:bg-gray-500 dark:text-gray-200 font-medium py-2 px-4 rounded-md">Cancel</button>
<button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md">Save Job</button>
</div>
</form>
</div>
</div>
</div>