<x-app-layout>
    <div class="mx-auto py-6 px-4">

        <!-- HEADER -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="mdi mdi-cog-outline text-gray-600"></i> Configuration
                </h2>
                <p class="text-sm text-gray-500">Manage gas items, units, and safety limits.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('utility.gas.input') }}"
                   class="px-4 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 font-medium">
                    &larr; Back to Input
                </a>
                <button onclick="openModal('add')"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium shadow flex items-center gap-2">
                    <i class="mdi mdi-plus"></i> Add New Item
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-4 mb-6 rounded border-l-4 border-green-500 shadow-sm flex items-center gap-2">
                <i class="mdi mdi-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        <!-- GRID -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- HYDROGEN -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-4 py-3 border-b border-red-100"
                     style="background: linear-gradient(to right, #fee2e2, #ffffff);">
                    <h3 class="font-bold text-red-800">
                        <i class="mdi mdi-fire"></i> HYDROGEN
                    </h3>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse($masters['HYDROGEN'] ?? [] as $item)
                        @include('oil.gas_utility.partials.config_row', ['item' => $item])
                    @empty
                        <p class="p-4 text-center text-gray-400 text-sm">No items configured.</p>
                    @endforelse
                </div>
            </div>

            <!-- NITROGEN -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-4 py-3 border-b border-blue-100"
                     style="background: linear-gradient(to right, #dbeafe, #ffffff);">
                    <h3 class="font-bold text-blue-800">
                        <i class="mdi mdi-snowflake"></i> NITROGEN
                    </h3>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse($masters['NITROGEN'] ?? [] as $item)
                        @include('oil.gas_utility.partials.config_row', ['item' => $item])
                    @empty
                        <p class="p-4 text-center text-gray-400 text-sm">No items configured.</p>
                    @endforelse
                </div>
            </div>

            <!-- AMMONIA -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-4 py-3 border-b border-emerald-100"
                     style="background: linear-gradient(to right, #d1fae5, #ffffff);">
                    <h3 class="font-bold text-emerald-800">
                        <i class="mdi mdi-flask"></i> AMMONIA
                    </h3>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse($masters['AMMONIA'] ?? [] as $item)
                        @include('oil.gas_utility.partials.config_row', ['item' => $item])
                    @empty
                        <p class="p-4 text-center text-gray-400 text-sm">No items configured.</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    <!-- MODAL OVERLAY (FIXED CENTER) -->
    <div id="configModal"
         class="fixed inset-0 z-50 hidden flex items-center justify-center">

        <!-- OVERLAY -->
        <div class="absolute inset-0 bg-black/50" onclick="closeModal()"></div>

        <!-- MODAL CARD -->
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4">
            <form id="configForm" method="POST" action="">
                @csrf
                <input type="hidden" name="_method" id="methodField" value="POST">

                <div class="px-6 py-5 border-b border-gray-200">
                    <h3 id="modalTitle" class="text-lg font-semibold text-gray-900">
                        Add New Item
                    </h3>
                </div>

                <div class="px-6 py-5 space-y-4">

                    <div id="gasTypeDiv">
                        <label class="block text-sm font-medium text-gray-700">Gas Type</label>
                        <select name="gas_type"
                                class="mt-1 w-full border-gray-300 rounded-md shadow-sm">
                            <option value="HYDROGEN">Hydrogen</option>
                            <option value="NITROGEN">Nitrogen</option>
                            <option value="AMMONIA">Ammonia</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Item Name</label>
                        <input type="text" name="name" id="inputName"
                               class="mt-1 w-full border-gray-300 rounded-md shadow-sm">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <input type="text" name="unit" id="inputUnit"
                               placeholder="Unit"
                               class="border-gray-300 rounded-md shadow-sm">
                        <select name="input_type" id="inputType"
                                class="border-gray-300 rounded-md shadow-sm">
                            <option value="number">Number</option>
                            <option value="stepper">Stepper</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <input type="number" step="0.01" name="min_limit" id="inputMin"
                               placeholder="Min Limit"
                               class="border-gray-300 rounded-md shadow-sm">
                        <input type="number" step="0.01" name="max_limit" id="inputMax"
                               placeholder="Max Limit"
                               class="border-gray-300 rounded-md shadow-sm">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <input type="number" name="sort_order" id="inputSort"
                               class="border-gray-300 rounded-md shadow-sm">
                        <div id="statusDiv" class="hidden">
                            <select name="is_active" id="inputActive"
                                    class="border-gray-300 rounded-md shadow-sm">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>

                </div>

                <div class="px-6 py-4 bg-gray-50 flex justify-end gap-2 rounded-b-xl">
                    <button type="button" onclick="closeModal()"
                            class="px-4 py-2 bg-white border rounded">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- JS -->
    <script>
        const modal = document.getElementById('configModal');
        const form = document.getElementById('configForm');
        const methodField = document.getElementById('methodField');
        const modalTitle = document.getElementById('modalTitle');
        const gasTypeDiv = document.getElementById('gasTypeDiv');
        const statusDiv = document.getElementById('statusDiv');

        function openModal(type, data = null) {
            modal.classList.remove('hidden');

            if (type === 'add') {
                modalTitle.innerText = 'Add New Item';
                form.action = "{{ route('utility.gas.config.store') }}";
                methodField.value = "POST";
                gasTypeDiv.classList.remove('hidden');
                statusDiv.classList.add('hidden');
                form.reset();
            }  else {
                modalTitle.innerText = 'Edit Item';
                form.action = "{{ url('oil/utility-gas/config') }}/" + data.id;
                methodField.value = "PUT";
                
                // Meskipun hidden, value harus tetap diset sesuai data asli
                // Agar jika validasi controller lolos, datanya tetap benar
                gasTypeDiv.classList.add('hidden');
                document.querySelector('select[name="gas_type"]').value = data.gas_type; 

                document.getElementById('inputName').value = data.name;
                statusDiv.classList.remove('hidden');

                inputName.value = data.name;
                inputUnit.value = data.unit;
                inputType.value = data.input_type;
                inputMin.value = data.min_limit;
                inputMax.value = data.max_limit;
                inputSort.value = data.sort_order;
                inputActive.value = data.is_active;
            }
        }

        function closeModal() {
            modal.classList.add('hidden');
        }
    </script>
</x-app-layout>
