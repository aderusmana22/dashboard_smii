<x-app-layout>
    @section('title')
        Start Input
    @endsection

    <div class="h-[calc(100vh-100px)] flex items-center justify-center">
        <div class="max-w-md w-full bg-white p-10 rounded-2xl shadow-xl border border-slate-100 text-center">
            <div class="mb-6 flex justify-center">
                <div class="bg-blue-100 p-4 rounded-full">
                    <i class="mdi mdi-clipboard-text-outline text-4xl text-blue-600"></i>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-slate-800 mb-2">Input Batch Refinery</h3>
            <p class="text-slate-500 mb-8">
                Pencatatan untuk tanggal: <br>
                <span class="font-bold text-slate-800 text-lg">{{ now()->format('l, d F Y') }}</span>
            </p>
            
            <form action="{{ route('oil.batch_refinery.input.start') }}" method="POST">
                @csrf
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl shadow-lg transition transform hover:scale-105 flex justify-center items-center gap-2">
                    MULAI SESI INPUT <i class="mdi mdi-arrow-right-circle text-xl"></i>
                </button>
            </form>
        </div>
    </div>
</x-app-layout>