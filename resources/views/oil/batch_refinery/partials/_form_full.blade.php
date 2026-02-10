{{-- /resources/views/oil/batch_refinery/input.blade.php --}}

{{-- 
    FINAL CODE - BATCH REFINERY INPUT
    - All user-facing text is in English.
    - Suggestion box floats correctly over all elements.
    - Table area does not scroll horizontally.
    - Full-width static submit button.
    - [NEW] Uses SweetAlert2 for submission confirmation.
--}}

<div class="">

    <!-- PAGE HEADER -->
    <div class="rounded-2xl shadow-xl p-6 mb-8"
         style="background: linear-gradient(to right, #2563eb, #4f46e5);">
        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
            <div>
                <h2 class="text-3xl font-bold text-white flex items-center gap-3">
                    <i class="mdi mdi-barrel"></i>
                    Batch Refinery Input
                </h2>
                <p class="text-blue-100 mt-1">Daily data input for all tanks.</p>
            </div>
        </div>
    </div>

    <form action="{{ route('oil.batch_refinery.input.store_full') }}" method="POST" autocomplete="off" id="refinery-form">
        @csrf

        <!-- INFO BAR -->
        <div class="bg-white p-4 rounded-2xl shadow-md border border-slate-200 mb-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-4">
                <div class="bg-blue-100 p-3 rounded-lg text-blue-600">
                    <i class="mdi mdi-calendar-clock text-2xl"></i>
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase block">Reading Date</label>
                    <p class="font-bold text-xl text-slate-800">{{ \Carbon\Carbon::parse($date)->format('l, d F Y') }}</p>
                </div>
            </div>

            @if(collect($existingReadings)->isEmpty())
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                    <i class="mdi mdi-information-outline mr-1.5"></i> No data for today yet
                </span>
            @else
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                    <i class="mdi mdi-pencil mr-1.5"></i> Editing today's data
                </span>
            @endif
        </div>

        <!-- INPUT AREA -->
        <div class="space-y-8"> 
            @php $globalIndex = 0; @endphp
            
            @foreach($groupedTanks as $groupName => $tanks)
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-visible"> 
                    <div class="px-6 py-4 border-b border-slate-200 flex items-center gap-3" style="background: linear-gradient(to right, #2563eb, #4f46e5);">
                        <div class="bg-blue-600 h-6 w-1.5 rounded-full"></div>
                        <h3 class="text-lg font-bold text-white uppercase tracking-wide">{{ $groupName }}</h3>
                    </div>

                    <div>
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-slate-500 uppercase bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 min-w-[150px]">Tank Name</th>
                                    <th class="px-4 py-3 w-[140px]">Status</th>
                                    <th class="px-4 py-3 min-w-[250px]">Oil Code</th>
                                    <th class="px-4 py-3 min-w-[200px]">Description</th>
                                    <th class="px-4 py-3 w-[150px] text-right">Value (Kg)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($tanks as $tank)
                                    @php 
                                        $currentVal = $existingReadings[$tank->id] ?? null;
                                        $defaultStatus = $currentVal ? $currentVal->status : 'Process';
                                    @endphp
                                    <tr class="hover:bg-blue-50/40 transition-colors">
                                        <td class="px-4 py-3 align-middle">
                                            <div class="font-bold text-slate-700 text-base">{{ $tank->name }}</div>
                                            <div class="text-xs text-slate-400 font-mono">Max: {{ number_format($tank->capacity_kg) }}</div>
                                            <input type="hidden" name="readings[{{$globalIndex}}][tank_id]" value="{{ $tank->id }}">
                                        </td>
                                        <td class="px-4 py-3 align-middle">
                                            <select name="readings[{{$globalIndex}}][status]" 
                                                    class="w-full text-sm rounded-lg border-slate-300 py-2 pl-2 focus:ring-blue-500 focus:border-blue-500 font-bold cursor-pointer
                                                    {{ $defaultStatus == 'Process' ? 'text-blue-600 bg-blue-50' : 
                                                      ($defaultStatus == 'Hold' ? 'text-amber-600 bg-amber-50' : 
                                                      ($defaultStatus == 'Release' ? 'text-green-600 bg-green-50' : 'text-red-600 bg-red-50')) }}">
                                                <option value="Process" class="text-blue-600 font-bold" {{ $defaultStatus == 'Process' ? 'selected' : '' }}>Process</option>
                                                <option value="Hold" class="text-amber-600 font-bold" {{ $defaultStatus == 'Hold' ? 'selected' : '' }}>Hold</option>
                                                <option value="Release" class="text-green-600 font-bold" {{ $defaultStatus == 'Release' ? 'selected' : '' }}>Release</option>
                                                <option value="Reject" class="text-red-600 font-bold" {{ $defaultStatus == 'Reject' ? 'selected' : '' }}>Reject</option>
                                            </select>
                                        </td>
                                        <td class="px-4 py-3 align-middle relative">
                                            <div class="relative w-full">
                                                <input type="text" 
                                                    name="readings[{{$globalIndex}}][oil_code]" 
                                                    id="code_input_{{$globalIndex}}"
                                                    value="{{ $currentVal ? $currentVal->oil_code : '' }}"
                                                    oninput="handleInputSearch(this, {{$globalIndex}})"
                                                    onfocus="handleInputSearch(this, {{$globalIndex}})"
                                                    class="w-full text-sm rounded-lg border-slate-300 focus:ring-blue-500 focus:border-blue-500 uppercase font-bold text-slate-700" 
                                                    placeholder="Type Code..." 
                                                    autocomplete="off">
                                                
                                                <ul id="suggestion_box_{{$globalIndex}}" 
                                                    class="hidden absolute z-50 w-full bg-white border border-slate-200 rounded-lg shadow-xl mt-1 max-h-60 overflow-y-auto">
                                                </ul>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 align-middle">
                                            <input type="text" 
                                                   name="readings[{{$globalIndex}}][description]" 
                                                   id="desc_input_{{$globalIndex}}"
                                                   value="{{ $currentVal ? $currentVal->description : '' }}"
                                                   class="w-full text-sm rounded-lg border-slate-300 bg-slate-50 text-slate-600 focus:ring-blue-500 focus:border-blue-500" 
                                                   placeholder="Description" readonly>
                                        </td>
                                        <td class="px-4 py-3 align-middle">
                                            <input type="number" step="0.01" name="readings[{{$globalIndex}}][current_value_kg]" 
                                                   value="{{ $currentVal ? $currentVal->current_value_kg : '' }}"
                                                   class="w-full text-base font-bold text-slate-800 rounded-lg border-slate-300 text-right focus:ring-emerald-500 focus:border-emerald-500 bg-slate-50 shadow-sm" 
                                                   placeholder="0">
                                        </td>
                                    </tr>
                                    @php $globalIndex++; @endphp
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- SUBMIT BUTTON (STATIC & FULL-WIDTH) -->
        <div class="mt-8 pt-6 border-t border-slate-200">
            <div>
                <button type="button" id="submit-btn-refinery"
                        class="w-full px-8 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-lg transform transition active:scale-[0.98] flex justify-center items-center gap-2 text-base">
                    <i class="mdi mdi-content-save-all text-xl"></i>
                    SAVE ALL DATA
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    // --- SCRIPT FOR CUSTOM SUGGESTION ---
    const masterItems = @json($items->map(function($item){
        return [
            'code' => $item->pt_part,
            'desc' => $item->pt_desc1
        ];
    }));

    function handleInputSearch(inputElement, index) {
        const query = inputElement.value.toUpperCase();
        const box = document.getElementById(`suggestion_box_${index}`);
        if (query.length === 0) {
            box.classList.add('hidden');
            box.innerHTML = '';
            return;
        }
        const filtered = masterItems.filter(item => {
            return item.code.includes(query) || item.desc.includes(query);
        }).slice(0, 10);
        if (filtered.length === 0) {
            box.classList.add('hidden');
            return;
        }
        let html = '';
        filtered.forEach(item => {
            html += `
                <li onclick="selectItem('${item.code}', '${item.desc.replace(/'/g, "\\'")}', ${index})"
                    class="px-4 py-2 hover:bg-blue-50 cursor-pointer border-b border-slate-100 last:border-0 transition-colors">
                    <div class="font-bold text-slate-800 text-sm">${item.code}</div>
                    <div class="text-xs text-slate-500 truncate">${item.desc}</div>
                </li>
            `;
        });
        box.innerHTML = html;
        box.classList.remove('hidden');
    }

    function selectItem(code, desc, index) {
        document.getElementById(`code_input_${index}`).value = code;
        const descInput = document.getElementById(`desc_input_${index}`);
        descInput.value = desc;
        descInput.classList.add('bg-green-100', 'border-green-400');
        setTimeout(() => {
            descInput.classList.remove('bg-green-100', 'border-green-400');
        }, 800);
        document.getElementById(`suggestion_box_${index}`).classList.add('hidden');
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('ul[id^="suggestion_box_"]') && !e.target.closest('input[id^="code_input_"]')) {
            document.querySelectorAll('ul[id^="suggestion_box_"]').forEach(el => {
                el.classList.add('hidden');
            });
        }
    });

    // --- SCRIPT FOR SWEETALERT CONFIRMATION ---
    document.addEventListener('DOMContentLoaded', function () {
        const refineryForm = document.getElementById('refinery-form');
        const refinerySubmitBtn = document.getElementById('submit-btn-refinery');

        if (refinerySubmitBtn) {
            refinerySubmitBtn.addEventListener('click', function (e) {
                e.preventDefault();
                
                Swal.fire({
                    title: 'Confirm Submission',
                    text: "Please ensure all data is correct. Are you sure you want to save?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, save it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        refineryForm.submit();
                    }
                });
            });
        }
    });
</script>