<x-app-layout>
    @section('title')
        Stasiun Input Data
    @endsection

    <div class="py-8 px-4 sm:px-6 lg:px-8">
        {{-- PESAN SUKSES SETELAH SUBMIT --}}
        @if(session('success'))
            <div class="max-w-4xl mx-auto bg-emerald-50 text-emerald-800 p-4 mb-6 rounded-lg border-l-4 border-emerald-500 shadow-sm flex items-center gap-3">
                <i class="mdi mdi-check-circle text-xl"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        {{-- KONTEN DINAMIS --}}
        @if($type === 'utility_gas')
            {{-- Menyertakan partial form utility gas --}}
            @include('oil.gas_utility.partials._form', $__data)

        @elseif($type === 'batch_refinery')
            {{-- Menyertakan partial start atau step untuk batch refinery --}}
            @if(isset($session_active) && $session_active)
                @include('oil.batch_refinery.partials._input_step', $__data)
            @else
                 @include('oil.batch_refinery.partials._start')
            @endif

        @else
            {{-- TAMPILAN PEMILIHAN AWAL JIKA TIDAK ADA TYPE DI URL --}}
            <div class="h-[calc(100vh-200px)] flex items-center justify-center">
                <div class="max-w-md w-full bg-white p-10 rounded-2xl shadow-xl border border-slate-100 text-center">
                    <div class="mb-6 flex justify-center">
                        <div class="bg-blue-100 p-4 rounded-full">
                            <i class="mdi mdi-database-edit-outline text-4xl text-blue-600"></i>
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-800 mb-2">Pilih Jenis Input</h3>
                    <p class="text-slate-500 mb-8">
                        Silakan pilih jenis data yang akan dicatat.
                    </p>
                    
                    <form action="{{ route('oil.input_station.index') }}" method="GET">
                        <div class="mb-4">
                            <select name="type" class="w-full text-base py-3 px-4 rounded-lg border-slate-300 focus:ring-blue-500 focus:border-blue-500" required>
                                <option value="" disabled selected>-- Pilih Opsi --</option>
                                <option value="batch_refinery">Input Batch Refinery</option>
                                <option value="utility_gas">Input Utility Gas</option>
                            </select>
                        </div>
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl shadow-lg transition transform hover:scale-105 flex justify-center items-center gap-2">
                            LANJUTKAN <i class="mdi mdi-arrow-right-circle text-xl"></i>
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>