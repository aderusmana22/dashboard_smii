<x-app-layout>
    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="mb-10 text-center">
            <h1 class="text-3xl font-extrabold text-gray-900 flex justify-center items-center gap-3">
                <i class="mdi mdi-cogs text-blue-600"></i> Configuration Center
            </h1>
            <p class="mt-2 text-gray-500">Centralized management for Oil Factory settings, limits, and schedules.</p>
        </div>

        @if(session('success'))
            <div
                class="mb-6 mx-auto max-w-3xl bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm flex items-center gap-2">
                <i class="mdi mdi-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        <!-- Grid Menu -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">

            <!-- 1. SHIFT SCHEDULE -->
            <a href="{{ route('oil.config.shifts') }}"
                class="group relative bg-white p-6 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-200 hover:border-purple-300 block transform hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <div
                        class="p-3 bg-purple-100 text-purple-600 rounded-lg group-hover:bg-purple-600 group-hover:text-white transition-colors">
                        <i class="mdi mdi-clock-time-four-outline text-3xl"></i>
                    </div>
                    <i class="mdi mdi-arrow-right text-gray-300 group-hover:text-purple-600 transition-colors"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Shift Schedules</h3>
                <p class="text-gray-500 text-sm">Manage operational hours for Shift 1, 2, and 3 used in reporting.</p>
            </a>

            <!-- 2. UTILITY GAS -->
            <a href="{{ route('oil.config.utility_gas.index') }}"
                class="group relative bg-white p-6 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-200 hover:border-blue-300 block transform hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <div
                        class="p-3 bg-blue-100 text-blue-600 rounded-lg group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        <i class="mdi mdi-gas-cylinder text-3xl"></i>
                    </div>
                    <i class="mdi mdi-arrow-right text-gray-300 group-hover:text-blue-600 transition-colors"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Utility Gas</h3>
                <p class="text-gray-500 text-sm">Configure Hydrogen, Nitrogen, & Ammonia units and safety limits.</p>
            </a>

            <!-- 3. BATCH REFINERY -->
            <a href="{{ route('oil.config.batch_refinery.index') }}"
                class="group relative bg-white p-6 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-200 hover:border-orange-300 block transform hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <div
                        class="p-3 bg-orange-100 text-orange-600 rounded-lg group-hover:bg-orange-600 group-hover:text-white transition-colors">
                        <i class="mdi mdi-factory text-3xl"></i>
                    </div>
                    <i class="mdi mdi-arrow-right text-gray-300 group-hover:text-orange-600 transition-colors"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Batch Refinery</h3>
                <p class="text-gray-500 text-sm">Manage tanks (Hydro, NWB, Deodorizer), capacities, and grouping.</p>
            </a>

            <!-- 4. FUTURE PLACEHOLDER: TANK YARD -->
            <div class="relative bg-gray-50 p-6 rounded-2xl border border-gray-200 opacity-60">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-gray-200 text-gray-400 rounded-lg">
                        <i class="mdi mdi-silo text-3xl"></i>
                    </div>
                </div>
                <h3 class="text-xl font-bold text-gray-400 mb-2">Tank Yard (Soon)</h3>
                <p class="text-gray-400 text-sm">Config for 80T & 1T Yard stock.</p>
            </div>

            <!-- 5. FUTURE PLACEHOLDER: PACKING -->
            <div class="relative bg-gray-50 p-6 rounded-2xl border border-gray-200 opacity-60">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-gray-200 text-gray-400 rounded-lg">
                        <i class="mdi mdi-package-variant-closed text-3xl"></i>
                    </div>
                </div>
                <h3 class="text-xl font-bold text-gray-400 mb-2">Packing (Soon)</h3>
                <p class="text-gray-400 text-sm">Config for Packing tanks limits.</p>
            </div>

        </div>

        <div class="mt-12 text-center">
            <a href="{{ route('oil.input_station.index') }}"
                class="text-gray-500 hover:text-gray-800 text-sm font-medium underline">
                &larr; Back to Input Station
            </a>
        </div>
    </div>
</x-app-layout>