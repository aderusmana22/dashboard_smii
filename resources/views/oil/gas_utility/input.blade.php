<x-app-layout>

    <div class="mx-auto py-8 px-4">

        <!-- PAGE HEADER -->
        <div class="rounded-2xl shadow-xl p-6 mb-8"
             style="background: linear-gradient(to right, #6366f1, #818cf8);">
            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
                <h2 class="text-3xl font-bold text-white flex items-center gap-3">
                    <i class="mdi mdi-gas-cylinder"></i>
                    Utility Gas Input
                </h2>
                <div class="flex gap-2">
                    <a href="{{ route('utility.gas.logs') }}"
                       class="px-4 py-2 bg-white/10 border border-white/20 text-white rounded-lg hover:bg-white/20 flex items-center gap-2 text-sm font-medium transition">
                        <i class="mdi mdi-history"></i> History Logs
                    </a>
                    <a href="{{ route('oil.index') }}"
                       class="px-4 py-2 bg-white text-[#1E293B] rounded-lg hover:bg-gray-200 flex items-center gap-2 text-sm font-semibold transition">
                        <i class="mdi mdi-monitor-dashboard"></i> Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- SUCCESS MESSAGE -->
        @if(session('success'))
            <div class="bg-emerald-50 text-emerald-800 p-4 mb-6 rounded-lg border-l-4 border-emerald-500 shadow-sm flex items-center gap-3">
                <i class="mdi mdi-check-circle text-xl"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        <form action="{{ route('utility.gas.store') }}" method="POST">
            @csrf

            <!-- INFO BAR -->
            <div class="bg-white p-4 rounded-2xl shadow-md border border-gray-200 mb-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-4">
                    <div class="bg-blue-100 p-3 rounded-lg text-blue-600">
                        <i class="mdi mdi-calendar-check text-2xl"></i>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase block">Reading Date</label>
                        <p class="font-bold text-xl text-gray-800">{{ now()->format('l, d F Y') }}</p>
                        <input type="hidden" name="reading_date" value="{{ $date }}">
                    </div>
                </div>

                @if($existingReadings->isEmpty())
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                        <i class="mdi mdi-auto-fix mr-1.5"></i> Auto-filled (Last Data)
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        <i class="mdi mdi-pencil mr-1.5"></i> Editing Today's Data
                    </span>
                @endif
            </div>

            <!-- INPUT AREA -->
            <div class="space-y-8 md:space-y-10">

                @php
                    $cardDesigns = [
                        'HYDROGEN' => ['icon' => 'mdi-fire', 'title' => 'Hydrogen', 'unit' => 'PRESSURE (BAR)'],
                        'NITROGEN' => ['icon' => 'mdi-snowflake', 'title' => 'Nitrogen', 'unit' => 'LEVEL (INCH)'],
                        'AMMONIA'  => ['icon' => 'mdi-flask', 'title' => 'Ammonia', 'unit' => 'STOCK (CYL)'],
                    ];
                @endphp

                @foreach($masters as $gasType => $items)
                    @if(!empty($items))
                        @php
                            $design = $cardDesigns[$gasType];

                            $headerGradient = match($gasType) {
                                'HYDROGEN' => 'linear-gradient(to right, #fee2e2, #ffffff)',
                                'NITROGEN' => 'linear-gradient(to right, #dbeafe, #ffffff)',
                                'AMMONIA'  => 'linear-gradient(to right, #d1fae5, #ffffff)',
                                default    => 'linear-gradient(to right, #f3f4f6, #ffffff)',
                            };

                            $bodyGradient = match($gasType) {
                                'HYDROGEN' => 'linear-gradient(to bottom, #fff1f2, #ffffff)',
                                'NITROGEN' => 'linear-gradient(to bottom, #eff6ff, #ffffff)',
                                'AMMONIA'  => 'linear-gradient(to bottom, #ecfdf5, #ffffff)',
                                default    => 'linear-gradient(to bottom, #f9fafb, #ffffff)',
                            };
                        @endphp

                        <div class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden">

                            <!-- CARD HEADER -->
                            <div class="px-6 md:px-8 py-5 md:py-6 flex justify-between items-center border-b border-gray-200"
                                 style="background: {{ $headerGradient }};">
                                <h3 class="text-lg md:text-xl font-semibold text-gray-900 flex items-center gap-3">
                                    <i class="mdi {{ $design['icon'] }} text-xl md:text-2xl text-gray-600"></i>
                                    {{ $design['title'] }}
                                </h3>

                                <span class="text-xs md:text-sm font-semibold bg-gray-200 text-gray-700 px-3 py-1.5 rounded-lg">
                                    {{ $design['unit'] }}
                                </span>
                            </div>

                            <!-- CARD BODY -->
                            <div class="px-4 md:px-6 py-4 md:py-6"
                                 style="background: {{ $bodyGradient }};">
                                @foreach($items as $item)
                                    @include('oil.gas_utility.partials.row_input', ['item' => $item])
                                @endforeach
                            </div>

                            <!-- AMMONIA FOOTER -->
                            @if($gasType === 'AMMONIA')
                                <div class="bg-gray-50 px-6 md:px-8 py-5 md:py-6 border-t border-gray-200 flex justify-between items-center">
                                    <span class="text-sm md:text-base font-semibold text-gray-600 uppercase tracking-wide">
                                        Total Ammonia Stock
                                    </span>
                                    <span id="ammoniaTotal"
                                          class="text-3xl md:text-4xl font-extrabold text-gray-800">
                                        0
                                    </span>
                                </div>
                            @endif

                        </div>
                    @endif
                @endforeach

                <!-- SUBMIT -->
                <div class="pt-6 md:pt-10 space-y-5">
                    <button type="submit"
                            class="w-full bg-green-800 text-white font-bold py-4 md:py-5 rounded-2xl shadow-lg
                                   hover:bg-slate-700 transition-all active:scale-[0.98]
                                   flex justify-center items-center gap-3 text-lg md:text-xl">
                        <i class="mdi mdi-content-save-all text-2xl"></i>
                        SAVE ALL CHANGES
                    </button>

                    <div class="text-center text-sm md:text-base text-gray-500">
                        Input by: <span class="font-medium">{{ Auth::user()->name }}</span>
                    </div>
                </div>

            </div>
        </form>
    </div>

    <!-- JS -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {

            function updateAmmoniaTotal() {
                let total = 0;
                document.querySelectorAll('input[data-type="AMMONIA"]').forEach(input => {
                    total += parseInt(input.value || 0);
                });
                const el = document.getElementById('ammoniaTotal');
                if (el) el.innerText = total;
            }

            window.stepValue = function(id, step) {
                const input = document.getElementById('input_' + id);
                if (!input) return;

                let val = parseInt(input.value || 0);
                let newVal = val + step;

                const min = parseFloat(input.dataset.min);
                const max = parseFloat(input.dataset.max);

                if (newVal >= min && newVal <= max) {
                    input.value = newVal;
                    updateAmmoniaTotal();
                }
            }

            updateAmmoniaTotal();
        });
    </script>

</x-app-layout>
