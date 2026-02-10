<x-app-layout>
    @section('title')
        Data Input Station
    @endsection

    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">

        {{-- INPUT SELECTION SECTION (AUTO DROPDOWN) --}}
        <div class="bg-white p-6 rounded-2xl shadow-md border border-slate-100 mb-8 sticky top-4 z-20">
            <form action="{{ route('oil.input_station.index') }}" method="GET" id="typeSelectorForm">
                <label for="type" class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide">
                    Select Data Input Area:
                </label>

                <div class="relative">
                    <select
                        name="type"
                        id="type"
                        onchange="document.getElementById('typeSelectorForm').submit()"
                        class="block w-full text-lg font-semibold py-3 pl-4 pr-10 border-slate-300 focus:ring-blue-500 focus:border-blue-500 rounded-xl shadow-sm appearance-none cursor-pointer bg-slate-50 hover:bg-slate-100 transition text-slate-700"
                    >
                        <option value="" disabled {{ is_null($type) ? 'selected' : '' }}>
                            -- Click Here to Select --
                        </option>

                        <option value="batch_refinery" {{ $type === 'batch_refinery' ? 'selected' : '' }}>
                            🏭 Batch Refinery (Tank Input)
                        </option>

                        <option value="utility_gas" {{ $type === 'utility_gas' ? 'selected' : '' }}>
                            ⛽ Utility Gas (Gas Input)
                        </option>
                    </select>
                </div>
            </form>
        </div>

        {{-- SUCCESS MESSAGE --}}
        @if(session('success'))
            <div class="bg-emerald-50 text-emerald-800 p-4 mb-6 rounded-xl border-l-4 border-emerald-500 shadow-sm flex items-center gap-3">
                <i class="mdi mdi-check-circle text-2xl"></i>
                <span class="font-bold text-lg">{{ session('success') }}</span>
            </div>
        @endif

        {{-- ERROR MESSAGE --}}
        @if(session('error'))
            <div class="bg-red-50 text-red-800 p-4 mb-6 rounded-xl border-l-4 border-red-500 shadow-sm flex items-center gap-3">
                <i class="mdi mdi-alert-circle text-2xl"></i>
                <span class="font-bold text-lg">{{ session('error') }}</span>
            </div>
        @endif

        {{-- DYNAMIC CONTENT BASED ON SELECTION --}}
        @if($type === 'utility_gas')

            <div class="animate-fade-in-up">
                {{-- Utility Gas Form --}}
                @include('oil.gas_utility.partials._form', $data)
            </div>

        @elseif($type === 'batch_refinery')

            <div class="animate-fade-in-up">
                {{-- Batch Refinery Full Page Form --}}
                @include('oil.batch_refinery.partials._form_full', $data)
            </div>

        @else
            {{-- INITIAL EMPTY STATE --}}
            <div class="flex flex-col items-center justify-center py-16 opacity-60">
                <div class="bg-slate-100 p-6 rounded-full mb-4">
                    <i class="mdi mdi-gesture-tap text-6xl text-slate-400"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-600">
                    No Form Selected
                </h3>
                <p class="text-slate-500 mt-2">
                    Please select an input type from the menu above.
                </p>
            </div>
        @endif

    </div>
</x-app-layout>
