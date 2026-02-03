@php
    $val = $existingReadings[$item->id] ?? ($lastReadings[$item->id] ?? null);
    $isAutoFilled = !isset($existingReadings[$item->id]) && isset($lastReadings[$item->id]);

    if ($val === null && $item->input_type == 'stepper') {
        $val = 0;
    }
@endphp

@if($item->input_type == 'stepper')

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
            <input type="number" id="input_{{ $item->id }}" name="readings[{{ $item->id }}]" value="{{ intval($val) }}"
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
    {{--
    ======================================================================
    DESIGN-MATCHED STANDARD INPUT (Tailwind v3 Compatible)
    ======================================================================
    - Replaced border opacity shorthand with v3-compatible utilities.
    ======================================================================
    --}}
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
            <input type="number" step="0.01" name="readings[{{ $item->id }}]" value="{{ $val }}" placeholder="0.00" class="w-full h-12
                       text-lg font-semibold text-right
                       border border-gray-300
                       rounded-xl shadow-sm
                       focus:ring-2 focus:ring-blue-500
                       focus:border-blue-500
                       transition
                       {{ $isAutoFilled ? 'text-gray-400 font-normal italic' : 'text-gray-900' }}"
                onfocus="this.classList.remove('text-gray-400','font-normal','italic'); this.classList.add('text-gray-900');">
        </div>
    </div>

@endif