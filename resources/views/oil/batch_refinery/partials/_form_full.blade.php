<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

{{-- CUSTOM CSS UNTUK LAYOUT YANG PASTI --}}
<style>
    /* 1. Base Layout (Mobile First) - Vertikal */
    .refinery-row-grid {
        display: grid;
        grid-template-columns: 1fr; /* 1 Kolom ke bawah */
        gap: 1rem; /* Jarak antar elemen vertikal */
        position: relative;
    }

    /* 2. Container Input & Label - Label selalu di atas */
    .input-wrapper {
        display: flex;
        flex-direction: column;
        gap: 6px; /* Jarak antara Label dan Input */
        width: 100%;
    }

    /* 3. Desktop Layout (Layar > 1024px / Laptop & PC) - Horizontal */
    @media (min-width: 1024px) {
        .refinery-row-grid {
            /* Definisi Kolom: [Tank] [Status] [Code] [Desc] [Gauge] [Temp] [Value] */
            grid-template-columns: 180px 130px 140px 1fr 90px 90px 150px;
            gap: 12px; /* Jarak antar kolom horizontal */
            align-items: flex-start;
        }
        
        /* Align text input angka ke kanan khusus desktop */
        .input-wrapper input.text-numeric {
            text-align: right;
        }
        
        .input-wrapper label.label-numeric {
            text-align: right; /* Label angka ikut ke kanan di desktop? Opsional, saya set left dulu agar rapi */
            text-align: left; 
        }
    }
</style>

<div class="animate-fade-in pb-20">
    <!-- HEADER (Tidak berubah) -->
    <div class="rounded-2xl shadow-xl p-6 mb-8 bg-blue-700 relative overflow-visible">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="text-3xl font-bold text-white flex items-center gap-3">
                    <i class="mdi mdi-barrel"></i> Batch Refinery Input
                </h2>
                <div class="mt-2 text-blue-100 flex items-center gap-2 text-sm">
                    @if($isSupervisor)
                        <span class="bg-indigo-500/30 px-2 py-0.5 rounded border border-indigo-400/30"><i class="mdi mdi-shield-account"></i> Supervisor</span>
                    @else
                        <span class="bg-blue-500/30 px-2 py-0.5 rounded border border-blue-400/30"><i class="mdi mdi-account"></i> Operator</span>
                    @endif
                </div>
            </div>
             <div class="bg-white/10 p-2 rounded-xl border border-white/20 backdrop-blur-sm shadow-inner min-w-[300px]">
                @if($isSupervisor)
                    <form action="{{ route('oil.input_station.index') }}" method="GET" id="shiftSwitchForm">
                        <input type="hidden" name="type" value="batch_refinery">
                        <label class="block text-xs font-bold text-blue-200 uppercase mb-1 ml-1">Select Shift to Edit:</label>
                        <div class="relative">
                            <select name="target_key" onchange="document.getElementById('shiftSwitchForm').submit()"
                                class="appearance-none w-full bg-blue-900 text-white font-bold py-2.5 pl-4 pr-10 rounded-lg border border-blue-400/50 focus:ring-2 focus:ring-white/50 cursor-pointer text-sm shadow-sm hover:bg-blue-800 transition">
                                @foreach($context->editable_list as $item)
                                    @php $key = $item['date'] . '|' . $item['shift']; $isSelected = ($targetDate == $item['date'] && $targetShift == $item['shift']); @endphp
                                    <option value="{{ $key }}" {{ $isSelected ? 'selected' : '' }}>@if($isSelected) 👉 @endif {{ $item['label'] }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-white"><i class="mdi mdi-chevron-down"></i></div>
                        </div>
                    </form>
                @else
                    <div class="flex items-center justify-between px-4 py-2 text-white">
                        <div class="text-right"><div class="text-xs text-blue-300 uppercase font-bold">Active Shift</div><div class="text-2xl font-bold leading-none">Shift {{ $targetShift }}</div></div>
                        <div class="h-8 w-[1px] bg-white/20 mx-4"></div>
                        <div class="text-sm font-medium opacity-90">{{ \Carbon\Carbon::parse($targetDate)->format('l, d M Y') }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- FORM UTAMA --}}
    <form action="{{ route('oil.batch_refinery.input.store_full') }}" method="POST" autocomplete="off" id="refinery-form">
        @csrf
        <input type="hidden" name="reading_date" value="{{ $targetDate }}">
        <input type="hidden" name="shift" value="{{ $targetShift }}">

        @if($isLocked)
            <div class="bg-blue-100 border-l-4 border-slate-500 p-4 rounded-r-lg shadow-sm mb-6 flex items-center gap-3">
                <div class="bg-slate-200 p-2 rounded-full"><i class="mdi mdi-lock text-slate-600 text-xl"></i></div>
                <div><h3 class="font-bold text-slate-800">Input Locked</h3><p class="text-slate-600 text-sm">{{ $lockMessage }}</p></div>
            </div>
        @elseif($isSupervisor && $existingReadings->isNotEmpty())
            <div class="bg-blue-100 border-l-4 border-amber-500 p-4 rounded-r-lg shadow-sm mb-6 flex items-center gap-3 animate-pulse">
                <div class="bg-amber-100 p-2 rounded-full"><i class="mdi mdi-pencil text-amber-600 text-xl"></i></div>
                <div><h3 class="font-bold text-amber-800">Supervisor Mode: Editing</h3><p class="text-amber-700 text-sm">Editing Shift {{ $targetShift }} data.</p></div>
            </div>
        @endif

        <!-- LOOP GROUP -->
        <div class="space-y-8 {{ $isLocked && !$isSupervisor ? 'opacity-60 pointer-events-none grayscale-[0.8]' : '' }}"> 
            @php $globalIndex = 0; @endphp
            @foreach($groupedTanks as $groupName => $tanks)
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-visible"> 
                    <!-- GROUP TITLE -->
                    <div class="px-6 py-4 border-b border-slate-200 flex items-center gap-3 bg-blue-50 rounded-t-2xl">
                        <div class="bg-blue-600 h-5 w-1.5 rounded-full"></div>
                        <h3 class="text-base font-bold text-slate-700 uppercase tracking-wide">{{ $groupName }}</h3>
                    </div>
                    
                    <div class="p-4 space-y-4">
                        @foreach($tanks as $tank)
                            @php 
                                $currentVal = $existingReadings[$tank->id] ?? null;
                                $defaultStatus = $currentVal ? $currentVal->status : 'Process';
                                $statusClass = match($defaultStatus) { 'Process' => 'text-blue-600 bg-blue-50', 'Hold' => 'text-amber-600 bg-amber-50', 'Release' => 'text-emerald-600 bg-emerald-50', 'Reject' => 'text-red-600 bg-red-50', default => 'text-gray-600 bg-gray-50' };
                            @endphp

                            <!-- 
                                CUSTOM CLASS APPLIED HERE: refinery-row-grid 
                                Ini akan memaksa layout sesuai CSS di atas, mengabaikan grid tailwind yang mungkin bermasalah.
                            -->
                            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow relative refinery-row-grid">
                                
                                <input type="hidden" name="readings[{{$globalIndex}}][tank_id]" value="{{ $tank->id }}">

                                {{-- 1. TANK NAME --}}
                                <div class="input-wrapper justify-center"> <!-- justify-center agar teks vertikal center -->
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tank Name</label>
                                    <div>
                                        <div class="font-bold text-slate-700 text-sm leading-tight">{{ $tank->name }}</div>
                                        <div class="text-[10px] text-slate-400">Max: {{ number_format($tank->capacity_kg) }}</div>
                                    </div>
                                </div>

                                {{-- 2. STATUS --}}
                                <div class="input-wrapper">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Status</label>
                                    <select name="readings[{{$globalIndex}}][status]" {{ $isLocked && !$isSupervisor ? 'disabled' : '' }} 
                                            class="w-full h-[40px] text-xs font-bold rounded-lg border-slate-300 px-2 cursor-pointer focus:ring-blue-500 focus:border-blue-500 {{ $statusClass }}">
                                        <option value="Process" {{ $defaultStatus == 'Process' ? 'selected' : '' }}>Process</option>
                                        <option value="Hold" {{ $defaultStatus == 'Hold' ? 'selected' : '' }}>Hold</option>
                                        <option value="Release" {{ $defaultStatus == 'Release' ? 'selected' : '' }}>Release</option>
                                        <option value="Reject" {{ $defaultStatus == 'Reject' ? 'selected' : '' }}>Reject</option>
                                    </select>
                                </div>

                                {{-- 3. OIL CODE --}}
                                <div class="input-wrapper relative z-20">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Oil Code</label>
                                    <input type="text" name="readings[{{$globalIndex}}][oil_code]" id="code_input_{{$globalIndex}}" 
                                           value="{{ $currentVal ? $currentVal->oil_code : '' }}" 
                                           {{ $isLocked && !$isSupervisor ? 'disabled' : '' }} 
                                           oninput="handleInputSearch(this, {{$globalIndex}})" 
                                           onfocus="handleInputSearch(this, {{$globalIndex}})" 
                                           class="w-full h-[40px] text-sm rounded-lg border-slate-300 focus:ring-blue-500 focus:border-blue-500 uppercase font-bold text-slate-700 placeholder-slate-300 px-3" 
                                           placeholder="CODE" autocomplete="off">
                                    
                                    <ul id="suggestion_box_{{$globalIndex}}" class="hidden absolute top-[65px] left-0 w-[250px] bg-white border border-slate-200 rounded-lg shadow-2xl overflow-y-auto max-h-48 z-[100]"></ul>
                                </div>

                                {{-- 4. DESCRIPTION --}}
                                <div class="input-wrapper">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Description</label>
                                    <input type="text" name="readings[{{$globalIndex}}][description]" id="desc_input_{{$globalIndex}}" 
                                           value="{{ $currentVal ? $currentVal->description : '' }}" 
                                           class="w-full h-[40px] text-xs rounded-lg border-slate-200 bg-slate-50 text-slate-500 truncate px-3" 
                                           readonly tabindex="-1" placeholder="Auto Description...">
                                </div>

                                {{-- 5. GAUGE --}}
                                <div class="input-wrapper">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider label-numeric">Gauge</label>
                                    <input type="text" name="readings[{{$globalIndex}}][gauge_board]" 
                                           value="{{ $currentVal ? $currentVal->gauge_board : '' }}"
                                           {{ $isLocked && !$isSupervisor ? 'disabled' : '' }}
                                           class="w-full h-[40px] text-sm font-semibold text-slate-700 rounded-lg border-slate-300 focus:ring-blue-500 focus:border-blue-500 px-3 text-numeric" 
                                           placeholder="-">
                                </div>

                                {{-- 6. TEMP --}}
                                <div class="input-wrapper">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider label-numeric">Temp</label>
                                    <input type="number" step="0.1" name="readings[{{$globalIndex}}][temperature]" 
                                           value="{{ $currentVal ? $currentVal->temperature : '' }}"
                                           {{ $isLocked && !$isSupervisor ? 'disabled' : '' }}
                                           class="w-full h-[40px] text-sm font-semibold text-slate-700 rounded-lg border-slate-300 focus:ring-blue-500 focus:border-blue-500 px-3 text-numeric" 
                                           placeholder="0">
                                </div>

                                {{-- 7. VALUE KG --}}
                                <div class="input-wrapper">
                                    <label class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider label-numeric">Value (Kg)</label>
                                    <input type="number" step="0.01" name="readings[{{$globalIndex}}][current_value_kg]" 
                                           value="{{ $currentVal ? $currentVal->current_value_kg : '' }}" 
                                           {{ $isLocked && !$isSupervisor ? 'disabled' : '' }} 
                                           class="w-full h-[40px] text-sm font-bold text-slate-800 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500 bg-emerald-50/50 px-3 text-numeric" 
                                           placeholder="0">
                                </div>

                            </div>
                            @php $globalIndex++; @endphp
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        @if(!$isLocked || $isSupervisor)
            <div class="mt-8 pt-6 border-t border-slate-200 pb-10">
                <button type="button" id="submit-btn-refinery" class="w-full px-8 bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl shadow-lg transform transition active:scale-[0.99] flex justify-center items-center gap-2 text-lg">
                    <i class="mdi mdi-content-save-all text-2xl"></i>
                    @if($isSupervisor && $existingReadings->isNotEmpty()) UPDATE DATA (SHIFT {{ $targetShift }}) @else SAVE DATA (SHIFT {{ $targetShift }}) @endif
                </button>
            </div>
        @endif
    </form>
</div>

<script>
    (function() {
        const masterItems = @json($items->map(function($item){ return [ 'code' => $item->pt_part, 'desc' => $item->pt_desc1 ]; }));

        window.handleInputSearch = function(inputElement, index) {
            const query = inputElement.value.toUpperCase();
            const box = document.getElementById(`suggestion_box_${index}`);
            if (!box) return;
            if (query.length === 0) { box.classList.add('hidden'); box.innerHTML = ''; return; }
            const filtered = masterItems.filter(item => { return item.code.includes(query) || (item.desc && item.desc.includes(query)); }).slice(0, 10);
            if (filtered.length === 0) { box.classList.add('hidden'); return; }
            let html = '';
            filtered.forEach(item => {
                const safeDesc = item.desc ? item.desc.replace(/'/g, "\\'") : '';
                html += `<li onclick="selectItem('${item.code}', '${safeDesc}', ${index})" class="px-4 py-2 hover:bg-blue-50 cursor-pointer border-b border-slate-100 last:border-0 transition-colors"><div class="font-bold text-slate-800 text-sm">${item.code}</div><div class="text-xs text-slate-500 truncate">${item.desc}</div></li>`;
            });
            box.innerHTML = html; box.classList.remove('hidden');
        };

        window.selectItem = function(code, desc, index) {
            document.getElementById(`code_input_${index}`).value = code;
            const descInput = document.getElementById(`desc_input_${index}`);
            descInput.value = desc;
            descInput.classList.remove('bg-slate-50'); descInput.classList.add('bg-emerald-100', 'text-emerald-800');
            setTimeout(() => { descInput.classList.remove('bg-emerald-100', 'text-emerald-800'); descInput.classList.add('bg-slate-50'); }, 600);
            document.getElementById(`suggestion_box_${index}`).classList.add('hidden');
        };

        $(document).on('click', function(e) {
            if (!$(e.target).closest('ul[id^="suggestion_box_"]').length && !$(e.target).closest('input[id^="code_input_"]').length) { $('ul[id^="suggestion_box_"]').addClass('hidden'); }
        });

        $('#submit-btn-refinery').off('click').on('click', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Confirm Submission', text: "Pastikan data sudah benar.", icon: 'question',
                showCancelButton: true, confirmButtonColor: '#2563eb', cancelButtonColor: '#ef4444', confirmButtonText: 'Yes, Save it!'
            }).then((result) => { if (result.isConfirmed) { $('#refinery-form').submit(); } });
        });
    })();
</script>