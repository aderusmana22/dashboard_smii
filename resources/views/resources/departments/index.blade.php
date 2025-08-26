<x-app-layout>
    {{-- CDN Libraries --}}
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- 
        =============================================================================
        SOLUSI MODE GELAP: BLOK CSS KUSTOM
        =============================================================================
        Aturan ini secara manual menimpa gaya mode terang setiap kali body 
        memiliki kelas `.dark-skin`, memastikan kompatibilitas tanpa mengubah
        konfigurasi Tailwind atau JavaScript produksi.
    --}}
    <style>
        .dark-skin .bg-white { background-color: rgb(31 41 55 / 1); }
        .dark-skin .bg-gray-50 { background-color: rgb(55 65 81 / 1); }
        .dark-skin .bg-gray-100 { background-color: rgb(55 65 81 / 1); }
        .dark-skin .divide-gray-200 > :not([hidden]) ~ :not([hidden]) { border-color: rgb(55 65 81 / 1); }
        .dark-skin .text-gray-900 { color: rgb(249 250 251 / 1); }
        .dark-skin .text-gray-800 { color: rgb(229 231 235 / 1); }
        .dark-skin .text-gray-700 { color: rgb(209 213 219 / 1); }
        .dark-skin .text-gray-500 { color: rgb(209 213 219 / 1); }
        .dark-skin .border-gray-300 { border-color: rgb(75 85 99 / 1); }
        .dark-skin .text-indigo-600 { color: #818cf8; }
        .dark-skin .text-indigo-600:hover { color: #a5b4fc; }
        .dark-skin .text-red-600 { color: #f87171; }
        .dark-skin .text-red-600:hover { color: #fca5a5; }
        .dark-skin .modal-cancel-button { background-color: rgb(75 85 99 / 1); color: rgb(229 231 235 / 1); }
        .dark-skin .modal-cancel-button:hover { background-color: rgb(107 114 128 / 1); }
    </style>
    
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Marsho Departments') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto sm:px-6 lg:px-8">
            {{-- Komponen Alpine utama: otak dari seluruh interaktivitas halaman --}}
            <div x-data="{
                // 1. STATE MANAGEMENT
                departments: {{ $departments->toJson() }},
                newDepartment: { department_name: '' },
                isModalOpen: false,
                editDepartment: { id: null, department_name: '' },

                // 2. METHODS / LOGIC
                showSuccessToast(message) {
                    const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true, didOpen: (toast) => { toast.addEventListener('mouseenter', Swal.stopTimer); toast.addEventListener('mouseleave', Swal.resumeTimer); } });
                    Toast.fire({ icon: 'success', title: message });
                },
                showErrorAlert(message) {
                    Swal.fire({ icon: 'error', title: 'An Error Occurred', text: message, confirmButtonColor: '#d33' });
                },
                
                // CREATE Logic
                async addDepartment() {
                    const response = await fetch('{{ route('marsho-departments.store') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify(this.newDepartment)
                    });
                    const data = await response.json();
                    if (response.ok) {
                        this.departments.unshift(data.department);
                        this.newDepartment.department_name = '';
                        this.showSuccessToast(data.message);
                    } else { this.showErrorAlert(data.message || 'Failed to add department.'); }
                },
                
                // Modal Logic
                openEditModal(department) { this.editDepartment = { ...department }; this.isModalOpen = true; },
                closeModal() { this.isModalOpen = false; },

                // UPDATE Logic
                async updateDepartment() {
                    const response = await fetch(`/marsho-departments/${this.editDepartment.id}`, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify(this.editDepartment)
                    });
                    const data = await response.json();
                    if (response.ok) {
                        const index = this.departments.findIndex(d => d.id === this.editDepartment.id);
                        if (index !== -1) this.departments[index] = data.department;
                        this.closeModal();
                        this.showSuccessToast(data.message);
                    } else { this.showErrorAlert(data.message || 'Failed to update department.'); }
                },

                // DELETE Logic
                deleteDepartment(departmentId) {
                    Swal.fire({ title: 'Are you sure?', text: 'This action cannot be undone!', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6', confirmButtonText: 'Yes, delete it!' })
                    .then((result) => {
                        if (result.isConfirmed) {
                            fetch(`/marsho-departments/${departmentId}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
                            .then(response => {
                                if (response.ok) {
                                    this.departments = this.departments.filter(d => d.id !== departmentId);
                                    this.showSuccessToast('Department deleted successfully.');
                                } else {
                                    response.json().then(data => { this.showErrorAlert(data.message); });
                                }
                            });
                        }
                    });
                }
            }" class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                
                <!-- Form Add New (dikontrol oleh Alpine) -->
                <form @submit.prevent="addDepartment()" class="mb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Marsho JobBoard Departments</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <div class="md:col-span-2">
                            <label for="department_name" class="block text-sm font-medium text-gray-700">Department Name</label>
                            <input type="text" x-model="newDepartment.department_name" id="department_name" class="mt-1 block w-full rounded-md border-gray-300 text-gray-900 bg-white shadow-sm" required>
                        </div>
                        <div>
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md transition">Add Department</button>
                        </div>
                    </div>
                </form>

                <!-- Tabel Data (dirender oleh Alpine) -->
                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-full w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Users</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-if="departments.length === 0">
                                <tr><td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">No departments found.</td></tr>
                            </template>
                            <template x-for="department in departments" :key="department.id">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="department.department_name"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="department.marsho_users_count"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                        <button @click="openEditModal(department)" type="button" class="text-indigo-600 hover:text-indigo-900">Edit</button>
                                        <button @click="deleteDepartment(department.id)" type="button" class="text-red-600 hover:text-red-900">Delete</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                {{-- Pagination dihapus karena tidak lagi relevan dalam arsitektur AJAX ini --}}

                <!-- Modal Edit (dikontrol oleh Alpine) -->
                <div x-show="isModalOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;">
                    <div @click.away="closeModal()" class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Department</h3>
                        <form @submit.prevent="updateDepartment()">
                            <div>
                                <label for="edit_department_name" class="block text-sm font-medium text-gray-700">Department Name</label>
                                <input type="text" id="edit_department_name" x-model="editDepartment.department_name" class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900" required>
                            </div>
                            <div class="mt-6 flex justify-end space-x-4">
                                <button type="button" @click="closeModal()" class="modal-cancel-button bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-md transition">Cancel</button>
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md transition">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>