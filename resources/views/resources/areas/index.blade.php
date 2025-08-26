<x-app-layout>
    {{-- CDN Libraries --}}
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- 
        =============================================================================
        SOLUSI MODE GELAP: BLOK CSS KUSTOM
        =============================================================================
        Karena konfigurasi Tailwind tidak dapat diubah untuk mengenali kelas `.dark-skin`,
        kita mendefinisikan aturan penimpaan (override) manual di sini.
        Aturan ini hanya aktif ketika body memiliki kelas `.dark-skin`.
        Kita menargetkan kelas-kelas Tailwind mode terang dan mengganti propertinya.
        Ini menggunakan variabel CSS Tailwind (--tw-bg-opacity, dll.) untuk kompatibilitas.
    --}}
    <style>
        .dark-skin .bg-white {
            background-color: rgb(31 41 55 / var(--tw-bg-opacity, 1)); /* ganti bg-white -> bg-gray-800 */
        }
        .dark-skin .bg-gray-50 {
            background-color: rgb(55 65 81 / var(--tw-bg-opacity, 1)); /* ganti bg-gray-50 -> bg-gray-700 */
        }
        .dark-skin .bg-gray-200:hover {
             background-color: rgb(75 85 99 / var(--tw-bg-opacity, 1)); /* ganti bg-gray-200:hover -> bg-gray-600 */
        }
        .dark-skin .divide-gray-200 > :not([hidden]) ~ :not([hidden]) {
            border-color: rgb(55 65 81 / var(--tw-border-opacity, 1)); /* ganti divide-gray-200 -> divide-gray-700 */
        }
        .dark-skin .text-gray-900 {
            color: rgb(249 250 251 / var(--tw-text-opacity, 1)); /* ganti text-gray-900 -> text-gray-100 */
        }
        .dark-skin .text-gray-800 {
            color: rgb(229 231 235 / var(--tw-text-opacity, 1)); /* ganti text-gray-800 -> text-gray-200 */
        }
        .dark-skin .text-gray-700 {
            color: rgb(209 213 219 / var(--tw-text-opacity, 1)); /* ganti text-gray-700 -> text-gray-300 */
        }
        .dark-skin .text-gray-500 {
            color: rgb(209 213 219 / var(--tw-text-opacity, 1)); /* ganti text-gray-500 -> text-gray-300 */
        }
        .dark-skin .border-gray-300 {
            border-color: rgb(75 85 99 / var(--tw-border-opacity, 1)); /* ganti border-gray-300 -> border-gray-600 */
        }
        .dark-skin .text-indigo-600 {
            color: #818cf8; /* ganti text-indigo-600 -> text-indigo-400 */
        }
        .dark-skin .text-indigo-600:hover {
            color: #a5b4fc; /* ganti hover:text-indigo-900 -> hover:text-indigo-300 */
        }
        .dark-skin .text-red-600 {
            color: #f87171; /* ganti text-red-600 -> text-red-400 */
        }
        .dark-skin .text-red-600:hover {
            color: #fca5a5; /* ganti hover:text-red-900 -> hover:text-red-300 */
        }
        /* Penyesuaian untuk Tombol Cancel di Modal */
        .dark-skin .modal-cancel-button {
             background-color: rgb(75 85 99 / var(--tw-bg-opacity, 1)); /* bg-gray-600 */
             color: rgb(229 231 235 / var(--tw-text-opacity, 1)); /* text-gray-200 */
        }
        .dark-skin .modal-cancel-button:hover {
             background-color: rgb(107 114 128 / var(--tw-bg-opacity, 1)); /* hover:bg-gray-500 */
        }
    </style>
    
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Job Areas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto sm:px-6 lg:px-8">

            {{-- Komponen Alpine utama, logika tidak berubah --}}
            <div x-data="{
                areas: {{ $areas->toJson() }}, newArea: { name: '', description: '' }, isModalOpen: false, editArea: { id: null, name: '', description: '' },
                showSuccessToast(message) { const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true, didOpen: (toast) => { toast.addEventListener('mouseenter', Swal.stopTimer); toast.addEventListener('mouseleave', Swal.resumeTimer); } }); Toast.fire({ icon: 'success', title: message }); },
                showErrorAlert(message) { Swal.fire({ icon: 'error', title: 'An Error Occurred', text: message, confirmButtonColor: '#d33' }); },
                async addArea() { const response = await fetch('{{ route('areas.store') }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify(this.newArea) }); const data = await response.json(); if (response.ok) { this.areas.unshift(data.area); this.newArea = { name: '', description: '' }; this.showSuccessToast(data.message); } else { this.showErrorAlert(data.message || 'Failed to add the area.'); } },
                openEditModal(area) { this.editArea = { ...area }; this.isModalOpen = true; },
                closeModal() { this.isModalOpen = false; },
                async updateArea() { const response = await fetch(`/areas/${this.editArea.id}`, { method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify(this.editArea) }); const data = await response.json(); if (response.ok) { const index = this.areas.findIndex(a => a.id === this.editArea.id); if (index !== -1) this.areas[index] = data.area; this.closeModal(); this.showSuccessToast(data.message); } else { this.showErrorAlert(data.message || 'Failed to update the area.'); } },
                deleteArea(areaId) { Swal.fire({ title: 'Are you sure?', text: 'This action cannot be undone!', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6', confirmButtonText: 'Yes, delete it!' }).then((result) => { if (result.isConfirmed) { fetch(`/areas/${areaId}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } }).then(response => { if (response.ok) { this.areas = this.areas.filter(a => a.id !== areaId); this.showSuccessToast('The area has been successfully deleted.'); } else { response.json().then(data => { this.showErrorAlert(data.message || 'Failed to delete the area.'); }); } }).catch(error => { console.error('Error:', error); this.showErrorAlert('An unexpected network error occurred.'); }); } }); }
            }" class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                
                <!-- Form untuk Menambah Area Baru -->
                <form @submit.prevent="addArea()" class="mb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Marsho JobBoard Area</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <div>
                            <label for="new_name" class="block text-sm font-medium text-gray-700">Area Name</label>
                            <input type="text" id="new_name" x-model="newArea.name" class="mt-1 block w-full rounded-md border-gray-300 text-gray-900 bg-white shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                        <div>
                            <label for="new_description" class="block text-sm font-medium text-gray-700">Description</label>
                            <input type="text" id="new_description" x-model="newArea.description" class="mt-1 block w-full rounded-md border-gray-300 text-gray-900 bg-white shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md transition duration-150 ease-in-out">Add Area</button>
                        </div>
                    </div>
                </form>

                <!-- Tabel untuk Menampilkan Daftar Area -->
                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-full w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-if="areas.length === 0">
                                <tr><td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">No areas have been added yet.</td></tr>
                            </template>
                            <template x-for="area in areas" :key="area.id">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="area.name"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="area.description"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                        <button @click="openEditModal(area)" class="text-indigo-600">Edit</button>
                                        <button @click="deleteArea(area.id)" class="text-red-600">Delete</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Modal untuk Edit Area -->
                <div x-show="isModalOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;">
                    <div @click.away="closeModal()" class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Area</h3>
                        <form @submit.prevent="updateArea()">
                            <div class="space-y-4">
                                <div>
                                    <label for="edit_name" class="block text-sm font-medium text-gray-700">Area Name</label>
                                    <input type="text" id="edit_name" x-model="editArea.name" class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900" required>
                                </div>
                                <div>
                                    <label for="edit_description" class="block text-sm font-medium text-gray-700">Description</label>
                                    <input type="text" id="edit_description" x-model="editArea.description" class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900">
                                </div>
                            </div>
                            <div class="mt-6 flex justify-end space-x-4">
                                <button type="button" @click="closeModal()" class="modal-cancel-button bg-gray-200 text-gray-800 font-bold py-2 px-4 rounded-md transition">Cancel</button>
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md transition">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>