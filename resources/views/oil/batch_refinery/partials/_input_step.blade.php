@section('title')
    Input {{ $groupName }}
@endsection

<div class="max-w-6xl mx-auto mt-6">
    <div class="mb-8">
        <div class="flex justify-between items-end mb-3">
            <div>
                <h1 class="text-3xl font-bold text-slate-800">{{ $groupName }}</h1>
                <p class="text-slate-500">Input Step {{ $step + 1 }}</p>
            </div>
            <a href="{{ route('oil.batch_refinery.input.reset') }}" onclick="return confirm('Batalkan sesi input?')" class="text-red-500 hover:bg-red-50 px-3 py-1 rounded text-sm font-semibold transition">
                <i class="mdi mdi-close"></i> Cancel Session
            </a>
        </div>
        
        <div class="w-full bg-slate-200 rounded-full h-2">
            <div class="bg-blue-600 h-2 rounded-full transition-all duration-500 shadow-[0_0_10px_rgba(37,99,235,0.5)]" style="width: {{ $progress }}%"></div>
        </div>
    </div>

    <form action="{{ route('oil.batch_refinery.input.store') }}" method="POST">
        @csrf
        <div class="bg-white rounded-xl shadow-lg border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b">
                        <tr>
                            <th class="px-6 py-4">Tank Name</th>
                            <th class="px-6 py-4 w-40">Status</th>
                            <th class="px-4 py-4">Oil Code</th>
                            <th class="px-4 py-4">Desc</th>
                            <th class="px-4 py-4 w-28">Temp (°C)</th>
                            <th class="px-4 py-4 w-28">Gauge (M)</th>
                            <th class="px-6 py-4 w-36">Value (Kg)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($tanks as $index => $tank)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-bold text-slate-700 block text-base">{{ $tank->name }}</span>
                                <span class="text-xs text-slate-400 font-mono">Max: {{ number_format($tank->capacity_kg) }} Kg</span>
                                <input type="hidden" name="readings[{{$index}}][tank_id]" value="{{ $tank->id }}">
                            </td>
                            <td class="px-6 py-4">
                                <div class="relative">
                                    <select name="readings[{{$index}}][status]" class="w-full text-sm rounded-lg border-slate-300 py-2 pl-2 pr-8 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="Process" class="text-blue-600 font-bold" selected>Process</option>
                                        <option value="Hold" class="text-yellow-600 font-bold">Hold</option>
                                        <option value="Release" class="text-green-600 font-bold">Release</option>
                                        <option value="Reject" class="text-red-600 font-bold">Reject</option>
                                    </select>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <input type="text" name="readings[{{$index}}][oil_code]" class="w-full text-sm rounded-lg border-slate-300 focus:ring-blue-500 focus:border-blue-500" placeholder="-">
                            </td>
                            <td class="px-4 py-4">
                                <input type="text" name="readings[{{$index}}][description]" class="w-full text-sm rounded-lg border-slate-300 focus:ring-blue-500 focus:border-blue-500" placeholder="-">
                            </td>
                            <td class="px-4 py-4">
                                <input type="number" step="0.1" name="readings[{{$index}}][temperature]" class="w-full text-sm rounded-lg border-slate-300 text-right focus:ring-blue-500 focus:border-blue-500" placeholder="0.0">
                            </td>
                            <td class="px-4 py-4">
                                <input type="number" step="0.01" name="readings[{{$index}}][gauge_board]" class="w-full text-sm rounded-lg border-slate-300 text-right focus:ring-blue-500 focus:border-blue-500" placeholder="0.00">
                            </td>
                            <td class="px-6 py-4">
                                <input type="number" step="0.01" name="readings[{{$index}}][current_value_kg]" class="w-full text-sm rounded-lg border-slate-300 text-right font-bold text-slate-700 focus:ring-emerald-500 focus:border-emerald-500 bg-slate-50" placeholder="0">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="p-6 bg-slate-50 border-t border-slate-200 flex justify-end items-center gap-4">
                <span class="text-slate-400 text-sm italic mr-4">Pastikan data sudah benar sebelum lanjut.</span>
                <button type="submit" class="{{ $isLastStep ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-blue-600 hover:bg-blue-700' }} text-white font-bold py-3 px-8 rounded-lg shadow-md transition transform hover:-translate-y-1 flex items-center gap-3">
                    @if($isLastStep)
                        <i class="mdi mdi-check-all text-lg"></i> SUBMIT & FINISH
                    @else
                        SUBMIT & NEXT <i class="mdi mdi-arrow-right text-lg"></i>
                    @endif
                </button>
            </div>
        </div>
    </form>
</div>