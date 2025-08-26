<x-app-layout>
    {{-- Libraries & Custom CSS for Dark Mode --}}
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .dark-skin .bg-white { background-color: rgb(31 41 55 / 1); }
        .dark-skin .bg-gray-50 { background-color: rgb(55 65 81 / 1); }
        .dark-skin .divide-gray-200 > :not([hidden]) ~ :not([hidden]) { border-color: rgb(55 65 81 / 1); }
        .dark-skin .text-gray-900 { color: rgb(249 250 251 / 1); }
        .dark-skin .text-gray-800 { color: rgb(229 231 235 / 1); }
        .dark-skin .text-gray-700 { color: rgb(209 213 219 / 1); }
        .dark-skin .text-gray-500 { color: rgb(209 213 219 / 1); }
        .dark-skin .border-gray-300 { border-color: rgb(75 85 99 / 1); }
        .dark-skin .bg-blue-100 { background-color: rgb(30 64 175 / 1); }
        .dark-skin .text-blue-800 { color: rgb(191 219 254 / 1); }
        .dark-skin .bg-gray-100 { background-color: rgb(75 85 99 / 1); }
    </style>
    
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Marsho User Management') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto sm:px-6 lg:px-8">
            <div x-data="{
                // 1. STATE MANAGEMENT
                searchQuery: '{{ request('search', '') }}',
                paginationData: {{ $users->toJson() }},
                departments: {{ $marshoDepartments->toJson() }},
                
                // 2. METHODS / LOGIC
                async fetchUsers(page = 1) {
                    if (page === 'search') { page = 1; }
                    let url = new URL('{{ route('marsho-users.index') }}');
                    url.searchParams.append('page', page);
                    if (this.searchQuery.length > 2 || this.searchQuery.length === 0) {
                        url.searchParams.append('search', this.searchQuery);
                    }
                    try {
                        const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
                        this.paginationData = await response.json();
                    } catch (error) { this.showErrorAlert('Failed to load user data.'); }
                },

                async assignDepartment(userId, departmentId) {
                    const response = await fetch('{{ route('marsho-users.store') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ user_id: userId, marsho_department_id: departmentId })
                    });
                    const data = await response.json();
                    if (response.ok) {
                        // Perbarui data pengguna di dalam array secara reaktif
                        const index = this.paginationData.data.findIndex(u => u.id === userId);
                        if (index !== -1) {
                            this.paginationData.data[index].marsho_profile = data.user.marsho_profile;
                        }
                        this.showSuccessToast(data.message);
                    } else { this.showErrorAlert(data.message || 'Failed to update department.'); }
                },

                getPageFromUrl(url) { if (!url) return null; return new URL(url).searchParams.get('page'); },
                showSuccessToast(message) { const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true, didOpen: (toast) => { toast.addEventListener('mouseenter', Swal.stopTimer); toast.addEventListener('mouseleave', Swal.resumeTimer); } }); Toast.fire({ icon: 'success', title: message }); },
                showErrorAlert(message) { Swal.fire({ icon: 'error', title: 'An Error Occurred', text: message }); }

            }" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <!-- Search Bar (dikontrol Alpine) -->
                    <div class="mb-4">
                        <input type="search" x-model.debounce.500ms="searchQuery" @input="fetchUsers('search')"
                               placeholder="Search by name or email..."
                               class="block w-full md:w-1/3 rounded-md border-gray-300 bg-white text-gray-900 shadow-sm">
                    </div>

                    <!-- Tabel Data (dirender Alpine) -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Current Marsho Department</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Assign New Department</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-if="!paginationData.data || paginationData.data.length === 0">
                                    <tr><td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">No users found.</td></tr>
                                </template>
                                <template x-for="user in paginationData.data" :key="user.id">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900" x-text="user.name"></div>
                                            <div class="text-sm text-gray-500" x-text="user.email"></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <template x-if="user.marsho_profile && user.marsho_profile.department">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800" x-text="user.marsho_profile.department.department_name"></span>
                                            </template>
                                            <template x-if="!user.marsho_profile || !user.marsho_profile.department">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Not Assigned</span>
                                            </template>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center space-x-2">
                                                <select :name="`department_for_${user.id}`" 
                                                        :id="`department_for_${user.id}`"
                                                        class="block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm"
                                                        @change="assignDepartment(user.id, $event.target.value)">
                                                    <option value="">-- Unassign --</option>
                                                    <template x-for="dept in departments" :key="dept.id">
                                                        <option :value="dept.id" 
                                                                :selected="user.marsho_profile && user.marsho_profile.marsho_department_id == dept.id"
                                                                x-text="dept.department_name">
                                                        </option>
                                                    </template>
                                                </select>
                                                {{-- Tombol "Save" kini tidak diperlukan, aksi terjadi saat @change --}}
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginasi Dinamis (dirender Alpine) -->
                    <div class="mt-4 flex justify-between items-center" x-show="paginationData.total > paginationData.per_page">
                        <span class="text-sm text-gray-700" x-text="`Showing ${paginationData.from} to ${paginationData.to} of ${paginationData.total} results`"></span>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                            <template x-for="(link, index) in paginationData.links" :key="index">
                                <button @click.prevent="fetchUsers(getPageFromUrl(link.url))" :disabled="!link.url"
                                    class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                                    :class="{
                                        'z-10 bg-indigo-50 border-indigo-500 text-indigo-600': link.active,
                                        'bg-white border-gray-300 text-gray-500 hover:bg-gray-50': !link.active,
                                        'cursor-not-allowed opacity-50': !link.active
                                    }"
                                    x-html="link.label">
                                </button>
                            </template>
                        </nav>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>