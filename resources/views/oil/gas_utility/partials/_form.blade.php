<div class="mx-auto animate-fade-in">

    {{-- HEADER INFO (SAMA SEPERTI BATCH REFINERY) --}}
    <div class="rounded-2xl shadow-xl p-6 mb-8 bg-blue-700 text-white relative">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="text-3xl font-bold flex items-center gap-3">
                    <i class="mdi mdi-gas-cylinder"></i> Utility Gas Input
                </h2>
                <div class="mt-2 text-blue-100 flex items-center gap-2 text-sm">
                    @if(isset($isSupervisor) && $isSupervisor)
                        <span class="bg-indigo-500/30 px-2 py-0.5 rounded border border-indigo-400/30"><i class="mdi mdi-shield-account"></i> Supervisor</span>
                    @else
                        <span class="bg-blue-500/30 px-2 py-0.5 rounded border border-blue-400/30"><i class="mdi mdi-account"></i> Operator</span>
                    @endif
                </div>
            </div>
            <div class="bg-white/10 p-3 rounded-xl border border-white/20 backdrop-blur-sm shadow-inner min-w-[200px] text-right">
                <div class="text-xs text-blue-300 uppercase font-bold">Active Context</div>
                <div class="text-2xl font-bold leading-none">Shift {{ $context->current_shift ?? '-' }}</div>
                <div class="text-sm font-medium opacity-90 mt-1">{{ \Carbon\Carbon::parse($date)->format('l, d M Y') }}</div>
            </div>
        </div>
    </div>

   <form action="{{ route('utility.gas.store') }}" method="POST" id="gas-form">
    @csrf

    {{-- ROLE LOGIC: SUPERVISOR vs OPERATOR --}}
    @if(isset($isSupervisor) && $isSupervisor)
        <!-- SUPERVISOR VIEW: Dropdown Pemilihan Shift -->
        <div class="bg-indigo-50 border border-indigo-200 p-4 rounded-xl shadow-sm mb-6">
            <label class="block text-sm font-bold text-indigo-900 mb-2">
                <i class="mdi mdi-calendar-clock"></i> Edit Data Context (Supervisor)
            </label>
            <div class="flex flex-col md:flex-row gap-4">
                <select id="supervisor_shift_selector" class="w-full md:w-1/2 rounded-lg border-indigo-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 font-medium text-indigo-900">
                    @foreach($context->editable_list as $editCtx)
                        @php 
                            $isSelected = (isset($date) && isset($shift) && $date == $editCtx['date'] && $shift == $editCtx['shift']); 
                        @endphp
                        <option value="{{ $editCtx['date'] }}|{{ $editCtx['shift'] }}" {{ $isSelected ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::parse($editCtx['date'])->format('d M Y') }} — Shift {{ $editCtx['shift'] }}
                            @if($editCtx['date'] == $context->current_date && $editCtx['shift'] == $context->current_shift)
                                (Current)
                            @endif
                        </option>
                    @endforeach
                </select>
                <button type="button" id="btn-load-shift" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-semibold transition">
                    Load Data
                </button>
            </div>
        </div>

        <!-- Hidden input untuk disubmit form -->
        <input type="hidden" name="reading_date" id="input_reading_date" value="{{ $date ?? $context->current_date }}">
        <input type="hidden" name="shift" id="input_shift" value="{{ $shift ?? $context->current_shift }}">

    @else
        <!-- OPERATOR VIEW: Locked to current shift -->
        <input type="hidden" name="reading_date" value="{{ $context->current_date }}">
        <input type="hidden" name="shift" value="{{ $context->current_shift }}">
    @endif
    
    <!-- NOTIFIKASI STATUS DATA (NEW/EDIT) -->
    @if($existingReadings->isEmpty())
         <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg shadow-sm mb-6 flex items-center gap-3">
            <div class="bg-blue-200 p-2 rounded-full"><i class="mdi mdi-plus-circle text-blue-600 text-xl"></i></div>
            <div>
                <h3 class="font-bold text-blue-800">New Entry</h3>
                <p class="text-blue-600 text-sm">Belum ada data untuk shift ini. Silakan input.</p>
            </div>
        </div>
    @else
        <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-lg shadow-sm mb-6 flex items-center gap-3">
            <div class="bg-emerald-200 p-2 rounded-full"><i class="mdi mdi-pencil text-emerald-600 text-xl"></i></div>
            <div>
                <h3 class="font-bold text-emerald-800">Editing Data</h3>
                <p class="text-emerald-600 text-sm">Data untuk shift ini sudah ada.</p>
            </div>
        </div>
    @endif

    <div class="space-y-8 md:space-y-10 pb-10">
        @php 
            $cardDesigns = [ 
                'HYDROGEN' => ['icon' => 'mdi-fire', 'title' => 'Hydrogen', 'unit' => 'PRESSURE (BAR)'], 
                'NITROGEN' => ['icon' => 'mdi-snowflake', 'title' => 'Nitrogen', 'unit' => 'LEVEL (INCH)'], 
                'AMMONIA'  => ['icon' => 'mdi-flask', 'title' => 'Ammonia', 'unit' => 'STOCK (CYL)'] 
            ]; 
            // Cari last updater
            $lastUpdate = $existingReadings->sortByDesc('updated_at')->first();
        @endphp

        <!-- LOOPING DATA MASTER PER KATEGORI -->
        @foreach($masters as $gasType => $items)
            @if(!empty($items))
                @php 
                    $design = $cardDesigns[$gasType] ?? ['icon'=>'mdi-gas-cylinder', 'title'=>$gasType, 'unit'=>'UNIT']; 
                    $headerGradient = match($gasType) { 
                        'HYDROGEN' => 'linear-gradient(to right, #fee2e2, #ffffff)', 
                        'NITROGEN' => 'linear-gradient(to right, #dbeafe, #ffffff)', 
                        'AMMONIA'  => 'linear-gradient(to right, #d1fae5, #ffffff)', 
                        default => 'linear-gradient(to right, #f3f4f6, #ffffff)' 
                    }; 
                @endphp
                
                <div class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 md:px-8 py-5 md:py-6 flex justify-between items-center border-b border-gray-200" style="background: {{ $headerGradient }};">
                        <h3 class="text-lg md:text-xl font-semibold text-gray-900 flex items-center gap-3">
                            <i class="mdi {{ $design['icon'] }} text-xl md:text-2xl text-gray-600"></i> {{ $design['title'] }}
                        </h3>
                        <span class="text-xs md:text-sm font-semibold bg-gray-200 text-gray-700 px-3 py-1.5 rounded-lg">
                            {{ $design['unit'] }}
                        </span>
                    </div>
                    
                    <div class="px-4 md:px-6 py-4 md:py-6 bg-slate-50/50">
                        @foreach($items as $item) 
                            @include('oil.gas_utility.partials.row_input', ['item' => $item, 'existingReadings' => $existingReadings]) 
                        @endforeach
                    </div>
                    
                    @if($gasType === 'AMMONIA') 
                        <div class="bg-gray-50 px-6 md:px-8 py-5 md:py-6 border-t border-gray-200 flex justify-between items-center">
                            <span class="text-sm md:text-base font-semibold text-gray-600 uppercase tracking-wide">Total Ammonia Stock</span>
                            <span id="ammoniaTotal" class="text-3xl md:text-4xl font-extrabold text-gray-800">0</span>
                        </div> 
                    @endif
                </div>
            @endif
        @endforeach

        <!-- BUTTON SAVE -->
        <button type="button" id="submit-btn-gas" class="w-full bg-blue-600 text-white font-bold py-4 md:py-5 rounded-2xl shadow-lg hover:bg-emerald-700 transition-all active:scale-[0.98] flex justify-center items-center gap-3 text-lg md:text-xl">
            <i class="mdi mdi-content-save-all text-2xl"></i> SAVE ALL CHANGES
        </button>

        <!-- FOOTER: INFO USER -->
        @if($lastUpdate)
            <div class="text-center pt-4 border-t border-slate-200">
                <p class="text-sm text-slate-500">
                    <i class="mdi mdi-history"></i> Last Input by: 
                    <span class="font-bold text-slate-700">{{ $lastUpdate->created_by }}</span> at 
                    <span class="font-bold text-slate-700">{{ \Carbon\Carbon::parse($lastUpdate->updated_at)->format('H:i, d M Y') }}</span>
                </p>
            </div>
        @endif
    </div>
</form>
</div>

<script>
    (function() {
        // --- LOGIC TOTAL AMMONIA ---
        function updateAmmoniaTotal() {
            let total = 0;
            $('#gas-form input[data-type="AMMONIA"]').each(function() { 
                total += parseInt($(this).val() || 0); 
            });
            $('#ammoniaTotal').text(total);
        }
        
        window.stepValue = function(id, step) {
            const input = document.getElementById('input_' + id); if (!input) return;
            let val = parseInt(input.value || 0), newVal = val + step, min = parseFloat(input.dataset.min), max = parseFloat(input.dataset.max);
            if (newVal >= min && newVal <= max) { 
                input.value = newVal; 
                if(input.dataset.type === 'AMMONIA') updateAmmoniaTotal(); 
            }
        };
        
        updateAmmoniaTotal(); 
        $('#gas-form input[data-type="AMMONIA"]').on('input', updateAmmoniaTotal);

        // --- SUPERVISOR LOAD SHIFT DATA ---
        $('#btn-load-shift').on('click', function() {
            let val = $('#supervisor_shift_selector').val().split('|');
            let date = val[0];
            let shift = val[1];
            // Update URL parameters dan reload agar controller merender data yang diedit
            window.location.href = window.location.pathname + "?date=" + date + "&shift=" + shift;
        });

        // Update hidden field saat dropdown supervisor berubah sebelum submit
        $('#supervisor_shift_selector').on('change', function() {
            let val = $(this).val().split('|');
            $('#input_reading_date').val(val[0]);
            $('#input_shift').val(val[1]);
        });

        // --- SUBMIT AJAX LOGIC ---
        $('#submit-btn-gas').off('click').on('click', function (e) {
            e.preventDefault();
            Swal.fire({ 
                title: 'Confirm Submission', 
                text: "Pastikan data Gas Utility sudah benar.", 
                icon: 'question', 
                showCancelButton: true, 
                confirmButtonColor: '#3085d6', 
                cancelButtonColor: '#d33', 
                confirmButtonText: 'Yes, save it!' 
            }).then((result) => { 
                if (result.isConfirmed) { 
                    
                    Swal.fire({
                        title: 'Saving Data...', 
                        allowOutsideClick: false, 
                        didOpen: () => { Swal.showLoading(); }
                    });

                    $.ajax({
                        url: $('#gas-form').attr('action'),
                        method: 'POST',
                        data: $('#gas-form').serialize(),
                        success: function(response) {
                            if(response.status === 'success') {
                                Swal.fire('Saved!', response.message, 'success').then(() => {
                                    window.location.href = response.redirect_url;
                                });
                            }
                        },
                        error: function(xhr) {
                            let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem';
                            Swal.fire('Failed!', msg, 'error');
                        }
                    });

                } 
            });
        });
    })();
</script>