<x-app-layout>
    {{-- Alpine.js CDN --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Manage Job Areas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        {{-- Menghapus 'max-w-7xl' untuk membuat layout lebih lebar --}}
        <div class="mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            {{-- Inline Alpine.js component --}}
            <div x-data="{
                isModalOpen: false,
                editArea: { id: null, name: '', description: '' },
                
                openEditModal(area) {
                    this.editArea = { ...area };
                    this.isModalOpen = true;
                },
                
                closeModal() {
                    this.isModalOpen = false;
                    this.editArea = { id: null, name: '', description: '' };
                }
            }" class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                
                <!-- Form untuk Menambah Area Baru -->
                <form action="{{ route('areas.store') }}" method="POST" class="mb-8">
                    @csrf
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Add New Area</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <div class="col-span-1">
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Area Name</label>
                            <input type="text" name="name" id="name" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                        </div>
                        <div class="col-span-1">
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                            <input type="text" name="description" id="description" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div class="col-span-1">
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md transition">Add Area</button>
                        </div>
                    </div>
                </form>

                <!-- Tabel untuk Menampilkan Daftar Area -->
                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-full w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($areas as $area)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $area->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $area->description }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-4">
                                    
                                    {{-- Tombol Edit: Memanggil fungsi Alpine 'openEditModal' --}}
                                    <button @click="openEditModal({{ json_encode($area) }})" class="text-indigo-600 hover:text-indigo-900 cursor-pointer">Edit</button>

                                    {{-- Form Hapus --}}
                                    <form action="{{ route('areas.destroy', $area->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this area?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">No areas found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Link Pagination (if you have pagination) -->
                @if(method_exists($areas, 'links'))
                <div class="mt-4">
                    {{ $areas->links() }}
                </div>
                @endif

                <!-- Modal untuk Edit Area -->
                <div 
                    x-show="isModalOpen" 
                    x-transition:enter="transition ease-out duration-300" 
                    x-transition:enter-start="opacity-0" 
                    x-transition:enter-end="opacity-100" 
                    x-transition:leave="transition ease-in duration-200" 
                    x-transition:leave-start="opacity-100" 
                    x-transition:leave-end="opacity-0" 
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" 
                    style="display: none;"
                >
                    <div @click.away="closeModal()" class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-md">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Edit Area</h3>
                        
                        {{-- Fixed form with template wrapper --}}
                        <template x-if="editArea.id">
                            <form :action="`{{ route('areas.index') }}/${editArea.id}`" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <div class="space-y-4">
                                    <div>
                                        <label for="edit_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Area Name</label>
                                        <input type="text" name="name" id="edit_name" x-model="editArea.name" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                    </div>
                                    <div>
                                        <label for="edit_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                                        <input type="text" name="description" id="edit_description" x-model="editArea.description" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                </div>

                                <div class="mt-6 flex justify-end space-x-4">
                                    <button type="button" @click="closeModal()" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-md transition">Cancel</button>
                                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md transition">Save Changes</button>
                                </div>
                            </form>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>