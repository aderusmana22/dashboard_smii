<x-app-layout>
    @section('title')
        Inward Dashboard
    @endsection

    <div class="p-6 min-h-screen font-sans tracking-tight">

        <!-- HEADER -->
        <h1 class="text-5xl font-extrabold text-center mb-8 uppercase tracking-wide">
            Inward Dashboard
        </h1>

        <div class="grid grid-cols-5 gap-6">

            <!-- LEFT COLUMN (Span 3 dari 5 kolom) -->
            <div class="col-span-3 space-y-8">

                <!-- GAUGES ROW -->
                <div class="grid grid-cols-3 gap-4 text-center">
                    @foreach($storageAreas as $area)
                        <div class="flex flex-col items-center justify-start w-full h-full space-y-2">

                            <!-- Header Section (Title, Temp Range, Actuals) -->
                            <div class="h-16">
                                <div class="text-xl font-bold ">
                                    <span>{{ $area->name }}</span>
                                    <span style="color: {{ $area->color }}">| {{ $area->temp_range }}</span>
                                </div>
                                <p class="font-semibold text-base" style="color: {{ $area->color }}">
                                    {{ number_format($area->total_pp) }} PP
                                </p>
                            </div>

                            <!-- GAUGE WRAPPER -->
                            <div class="relative w-full max-w-[160px] aspect-square">
                                <canvas id="chart-{{ $loop->index }}"></canvas>

                                <!-- Text Inside Gauge -->
                                <div
                                    class="absolute inset-0 flex flex-col items-center justify-center z-10 pointer-events-none">
                                    <span class="text-lg font-semibold" style="color: {{ $area->color }}">
                                        Occupancy
                                    </span>
                                    <span class="text-6xl font-extrabold leading-tight my-1"
                                        style="color: {{ $area->color }}">
                                        {{ $area->occupancy_percent }}%
                                    </span>
                                    <span class="text-3xl font-bold" style="color: {{ $area->color }}">
                                        {{ $area->actual_temp }}°C
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- INGREDIENT EXPIRY STATUS ROW -->
                <div>
                    {{-- REVISI: Menambahkan div wrapper dengan text-center --}}
                    <div class="text-center">
                        <div class="text-3xl font-bold mb-3 pb-1 inline-block">
                            Ingredient Expiry Status
                        </div>
                    </div>
                    <div class="card border-2 border-black shadow-md">
                        <table class="w-full border-collapse text-base">
                            <thead>
                                <tr class="font-extrabold uppercase text-center">
                                    <th class="border border-black p-2 w-10">No</th>
                                    <th class="border border-black p-2">Item Code</th>
                                    <th class="border border-black p-2 w-2/5">Description</th>
                                    <th class="border border-black p-2">Qty</th>
                                    <th class="border border-black p-2">Expiry Date</th>
                                </tr>
                            </thead>
                            <tbody class="text-center font-medium">
                                @forelse($expiries as $index => $item)
                                    <tr class="h-12 hover:bg-yellow-50">
                                        <td class="border border-black">{{ $index + 1 }}</td>
                                        <td class="border border-black px-1 font-bold">{{ $item->item_code }}</td>
                                        <td class="border border-black px-1 text-left pl-3">{{ $item->description }}</td>
                                        <td class="border border-black px-1">{{ $item->qty }}</td>
                                        <td class="border border-black px-1 text-red-600 font-bold">
                                            {{ $item->expiry_date }}</td>
                                    </tr>
                                @empty
                                    @for($i = 0; $i < 9; $i++)
                                        <tr class="h-12">
                                            @for($j = 0; $j < 5; $j++)
                                            <td class="border border-black"></td> @endfor
                                        </tr>
                                    @endfor
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN (Span 2 dari 5 kolom) -->
            <div class="col-span-2">
                {{-- REVISI: Menambahkan div wrapper dengan text-center --}}
                <div class="text-center">
                    <h3 class="text-3xl font-bold mb-3 pb-1 inline-block">
                        Daily Incoming Status
                    </h3>
                </div>
                <div class="card border-2 border-black min-h-[550px] shadow-md">
                    <table class="w-full border-collapse text-base">
                        <thead>
                            <tr class="-100 font-extrabold uppercase text-center">
                                <th class="border border-black p-2 w-10">No</th>
                                <th class="border border-black p-2">Item Code</th>
                                <th class="border border-black p-2">Jumlah</th>
                                <th class="border border-black p-2">Satuan</th>
                                <th class="border border-black p-2">No. RC</th>
                            </tr>
                        </thead>
                        <tbody class="text-center font-medium">
                            @forelse($incoming as $index => $item)
                                <tr class="h-12 hover:bg-blue-50">
                                    <td class="border border-black">{{ $index + 1 }}</td>
                                    <td class="border border-black px-1 font-bold">{{ $item->item_code }}</td>
                                    <td class="border border-black px-1">{{ $item->jumlah }}</td>
                                    <td class="border border-black px-1">{{ $item->satuan }}</td>
                                    <td class="border border-black px-1">{{ $item->no_rc }}</td>
                                </tr>
                            @empty
                                @for($i = 0; $i < 18; $i++)
                                    <tr class="h-12">
                                        @for($j = 0; $j < 5; $j++)
                                        <td class="border border-black"></td> @endfor
                                    </tr>
                                @endfor
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- CHART JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const storageAreas = @json($storageAreas);

            storageAreas.forEach((area, index) => {
                const ctx = document.getElementById(`chart-${index}`).getContext('2d');

                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            data: [
                                area.occupancy_percent,
                                100 - area.occupancy_percent
                            ],
                            backgroundColor: [
                                area.color,
                                '#E5E7EB' // Warna abu-abu muda untuk sisa
                            ],
                            borderColor: '#E5E7EB',
                            borderWidth: 4,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '80%',
                        plugins: {
                            legend: { display: false },
                            tooltip: { enabled: false }
                        },
                        animation: false
                    }
                });
            });
        });
    </script>

</x-app-layout>