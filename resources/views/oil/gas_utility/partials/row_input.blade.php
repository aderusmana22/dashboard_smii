@php
    // Ambil nilai yang sudah ada hari ini (jika ada, untuk mode edit)
    $existingValue = $existingReadings[$item->id] ?? null;
    
    // Ambil nilai terakhir (untuk auto-fill atau placeholder)
    $lastValue = $lastReadings[$item->id] ?? null;

@endphp

@if($item->input_type == 'stepper')

    {{-- ======================================================================= --}}
    {{-- BAGIAN STEPPER (PLUS/MINUS) -> TIDAK DIUBAH, TETAP AUTO-FILL          --}}
    {{-- ======================================================================= --}}
    @php
        // Logika untuk stepper: Jika ada data hari ini, gunakan. Jika tidak, gunakan data terakhir. Jika tidak ada keduanya, mulai dari 0.
        $stepperValue = $existingValue ?? $lastValue ?? 0;
    @endphp

    <div class="flex items-center justify-between py-6 border-b border-gray-200 last:border-b-0">
        <!-- Info -->
        <div class="space-y-2">
            <p class="text-lg font-semibold text-gray-900">
                {{ $item->name }}
            </p>
            <p class="text-sm text-gray-500">
                Range: {{ $item->min_limit }} – {{ $item->max_limit }}
            </p>
        </div>

        <!-- Controls -->
        <div class="flex items-center gap-5">
            <!-- Minus -->
            <button type="button" onclick="stepValue({{ $item->id }}, -1)" class="flex h-12 w-12 items-center justify-center rounded-full
                               bg-red-50 text-red-500
                               hover:bg-red-100
                               active:scale-95 transition">
                <i class="mdi mdi-minus text-2xl"></i>
            </button>

            <!-- Input -->
            <input type="number" id="input_{{ $item->id }}" name="readings[{{ $item->id }}]" value="{{ intval($stepperValue) }}"
                readonly data-type="{{ $item->gas_type }}" data-min="{{ $item->min_limit }}"
                data-max="{{ $item->max_limit }}" class="h-12 w-24 rounded-lg border border-gray-300
                               bg-white text-center text-xl font-bold text-gray-800
                               focus:border-blue-500 focus:ring-0">

            <!-- Plus -->
            <button type="button" onclick="stepValue({{ $item->id }}, 1)" class="flex h-12 w-12 items-center justify-center rounded-full
                               bg-green-500 text-white
                               hover:bg-green-600
                               active:scale-95 transition">
                <i class="mdi mdi-plus text-2xl"></i>
            </button>
        </div>
    </div>

@else

    {{-- ======================================================================= --}}
    {{-- BAGIAN STANDARD INPUT -> DIUBAH MENGGUNAKAN PLACEHOLDER              --}}
    {{-- ======================================================================= --}}
    @php
        // Logika untuk input standar:
        // 1. $inputValue -> HANYA berisi data hari ini. Jika belum ada, nilainya null (kosong).
        $inputValue = $existingValue;
        // 2. $placeholderText -> Berisi data terakhir sebagai referensi. Jika tidak ada, defaultnya '0.00'.
        $placeholderText = $lastValue !== null ? number_format($lastValue, 2, '.', '') : '0.00';
    @endphp

    <div class="flex items-center gap-6 py-4 border-b border-gray-200 last:border-b-0">

        <!-- Left: Label -->
        <div class="flex-1">
            <p class="font-medium text-gray-900">
                {{ $item->name }}
            </p>
            @if($item->min_limit)
                <p class="text-sm text-gray-500">
                    Standard: {{ $item->min_limit }} – {{ $item->max_limit }}
                </p>
            @endif
        </div>

        <!-- Right: Input -->
        <div class="relative ml-auto w-32">
            <input type="number" step="0.01" name="readings[{{ $item->id }}]" 
                   value="{{ $inputValue }}" 
                   placeholder="{{ $placeholderText }}" 
                   class="w-full h-12 text-lg font-semibold text-right
                          border border-gray-300 rounded-xl shadow-sm
                          focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                          placeholder:text-gray-400 placeholder:font-normal
                          transition text-gray-900"
                   onfocus="if (this.placeholder) { this.dataset.placeholder = this.placeholder; this.placeholder = ''; }"
                   onblur="if (this.dataset.placeholder) { this.placeholder = this.dataset.placeholder; }">
        </div>
    </div>

@endif