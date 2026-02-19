@php
    // --- 1. AMBIL DATA RAW ---
    $rawCurrent = $existingReadings[$item->id] ?? null;
    $rawLast    = $lastReadings[$item->id] ?? null;

    // --- 2. EXTRAKSI NILAI YANG AMAN (SAFE EXTRACTION) ---
    // Cek apakah data berupa Object (Model) atau String/Int biasa
    // Jika Object -> ambil ->value. Jika bukan -> pakai langsung datanya.
    
    $currentValue = is_object($rawCurrent) ? $rawCurrent->value : $rawCurrent;
    $lastValue    = is_object($rawLast)    ? $rawLast->value    : $rawLast;
@endphp

@if($item->input_type == 'stepper')

    {{-- ======================================================================= --}}
    {{-- BAGIAN STEPPER (PLUS/MINUS)                                           --}}
    {{-- ======================================================================= --}}
    @php
        // Logic Stepper: Prioritas Existing -> Last -> 0
        $stepperValue = $currentValue ?? $lastValue ?? 0;
    @endphp

    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 py-4 border-b border-dashed border-gray-200 last:border-0">
        
        <!-- Label Info -->
        <div class="w-full sm:w-auto text-center sm:text-left space-y-1">
            <p class="text-base sm:text-lg font-bold text-gray-700 uppercase tracking-wide">
                {{ $item->name }}
            </p>
            <p class="text-xs text-gray-400 font-medium">
                Range: {{ $item->min_limit }} – {{ $item->max_limit }}
            </p>
        </div>

        <!-- Controls -->
        <div class="flex items-center gap-5">
            <!-- Minus -->
            <button type="button" onclick="stepValue({{ $item->id }}, -1)" class="flex h-12 w-12 items-center justify-center rounded-full
                               bg-red-50 text-red-500 hover:bg-red-100 active:scale-95 transition shadow-sm border border-red-100">
                <i class="mdi mdi-minus text-2xl"></i>
            </button>

            <!-- Input -->
            <input type="number" id="input_{{ $item->id }}" name="readings[{{ $item->id }}]" 
                value="{{ intval($stepperValue) }}"
                readonly 
                data-type="{{ $item->gas_type }}" 
                data-min="{{ $item->min_limit }}"
                data-max="{{ $item->max_limit }}" 
                class="h-12 w-24 rounded-lg border border-gray-300 bg-white text-center text-xl font-bold text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition">

            <!-- Plus -->
            <button type="button" onclick="stepValue({{ $item->id }}, 1)" class="flex h-12 w-12 items-center justify-center rounded-full
                               bg-emerald-500 text-white hover:bg-emerald-600 active:scale-95 transition shadow-md shadow-emerald-500/30">
                <i class="mdi mdi-plus text-2xl"></i>
            </button>
        </div>
    </div>

@else

    {{-- ======================================================================= --}}
    {{-- BAGIAN STANDARD INPUT (MANUAL TYPE)                                   --}}
    {{-- ======================================================================= --}}
    @php
        // $inputValue -> Nilai hari ini (bisa null)
        $inputValue = $currentValue;
        
        // $placeholderText -> Nilai terakhir sebagai referensi bayangan
        $placeholderText = $lastValue !== null ? number_format((float)$lastValue, 2, '.', '') : '0.00';
    @endphp

    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 py-4 border-b border-gray-200 last:border-b-0">

        <!-- Left: Label -->
        <div class="w-full sm:flex-1 text-center sm:text-left">
            <p class="font-bold text-gray-800 text-base">
                {{ $item->name }}
            </p>
            @if($item->min_limit || $item->max_limit)
                <p class="text-xs text-gray-500 mt-1">
                    Std: {{ $item->min_limit ?? '0' }} – {{ $item->max_limit ?? 'Max' }}
                </p>
            @endif
        </div>

        <!-- Right: Input -->
        <div class="relative w-full sm:w-40">
            <input type="number" step="0.01" name="readings[{{ $item->id }}]" 
                   value="{{ $inputValue }}" 
                   placeholder="{{ $placeholderText }}" 
                   class="w-full h-12 text-lg font-bold text-right text-gray-800
                          border border-gray-300 rounded-xl shadow-sm
                          focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                          placeholder:text-gray-300 placeholder:font-normal
                          transition bg-slate-50 focus:bg-white"
                   onfocus="if (this.placeholder) { this.dataset.placeholder = this.placeholder; this.placeholder = ''; }"
                   onblur="if (this.dataset.placeholder) { this.placeholder = this.dataset.placeholder; }">
                   
            {{-- Unit Label kecil di pojok input jika perlu, optional --}}
            {{-- <span class="absolute right-3 top-3 text-xs text-gray-400 pointer-events-none">Bar</span> --}}
        </div>
    </div>

@endif