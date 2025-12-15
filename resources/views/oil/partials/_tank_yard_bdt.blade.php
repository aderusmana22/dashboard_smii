{{--
CATATAN PENTING TENTANG LEBAR LAYOUT:
Jika komponen ini terlihat menyempit di desktop, masalahnya kemungkinan besar ada di container induk (parent) dari div
ini.
Pastikan container induk yang membungkus kode ini tidak memiliki batasan lebar seperti 'max-w-lg' atau 'w-3/4'.
Untuk membuatnya lebar penuh, pastikan semua elemen induk hingga ke

<body> juga mengizinkan konten untuk melebar.
    --}}
    <div class="w-full bg-slate-50 font-sans rounded-lg">
        {{-- Grid Layout Utama --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 w-full">

            {{-- KOLOM KIRI: TABEL (lg:col-span-4) --}}
            <div class="lg:col-span-7 flex flex-col h-full">
                <div
                    class="bg-white rounded-xl shadow-md border border-slate-200 overflow-hidden flex flex-col h-[650px]">
                    {{-- Header Tabel --}}
                    <div class="bg-indigo-600 px-4 py-3 flex justify-between items-center shrink-0">
                        <h4 class="text-white font-semibold text-sm tracking-wide">Data Tank</h4>
                        <span id="totalTankBadge"
                            class="bg-white/20 text-white text-[10px] px-2 py-0.5 rounded-md backdrop-blur-sm">All
                            Shown</span>
                    </div>

                    {{-- Container Scrollable Tabel --}}
                    <div class="overflow-auto flex-grow bg-white custom-scrollbar relative">
                        <table class="w-full min-w-[300px] text-left border-collapse">
                            <thead class="text-[10px] text-slate-500 uppercase bg-slate-50 sticky top-0 z-10 shadow-sm">
                                <tr>
                                    <th class="px-3 py-3 font-bold tracking-wider border-b">Tank Code</th>
                                    <th class="px-3 py-3 font-bold tracking-wider text-right border-b">Capacity (Kg)
                                    </th>
                                    <th class="px-3 py-3 font-bold tracking-wider border-b">Oil Code</th>
                                    <th class="px-3 py-3 font-bold tracking-wider border-b">Description</th>
                                    <th class="px-3 py-3 font-bold tracking-wider border-b">Gauge Board (Meter)</th>
                                    <th class="px-3 py-3 font-bold tracking-wider border-b">Temperature (°C)</th>
                                    <th class="px-3 py-3 font-bold tracking-wider border-b">Current Value (Kg)</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody" class="divide-y divide-slate-100 text-xs text-slate-700">
                                {{-- Data Rows --}}
                                <tr data-tank-id="80T9" class="hover:bg-indigo-50 transition">
                                    <td class="px-3 py-3 font-bold">80T9</td>
                                    <td class="px-3 py-3 text-right font-mono text-slate-600">40,000</td>
                                    <td class="px-3 py-3">TQA829</td>
                                    <td class="px-3 py-3"><span
                                            class="px-1.5 py-0.5 rounded bg-red-100 text-red-700 font-bold text-[10px]">CFAD</span>
                                    </td>
                                    <td class="px-3 py-3"></td>
                                    <td class="px-3 py-3"></td>
                                    <td class="px-3 py-3"></td>
                                </tr>
                                <tr data-tank-id="80T10" class="hover:bg-indigo-50 transition">
                                    <td class="px-3 py-3 font-bold">80T10</td>
                                    <td class="px-3 py-3 text-right font-mono text-slate-600">77,000</td>
                                    <td class="px-3 py-3">ZYA801</td>
                                    <td class="px-3 py-3"><span
                                            class="px-1.5 py-0.5 rounded bg-orange-100 text-orange-700 font-bold text-[10px]">PFAD</span>
                                    </td>
                                    <td class="px-3 py-3"></td>
                                    <td class="px-3 py-3"></td>
                                    <td class="px-3 py-3"></td>
                                </tr>
                                <tr data-tank-id="80T12" class="hover:bg-indigo-50 transition">
                                    <td class="px-3 py-3 font-bold">80T12</td>
                                    <td class="px-3 py-3 text-right font-mono text-slate-600">70,000</td>
                                    <td class="px-3 py-3">102-013</td>
                                    <td class="px-3 py-3"><span
                                            class="px-1.5 py-0.5 rounded bg-yellow-100 text-yellow-700 font-bold text-[10px]">OLEIN</span>
                                    </td>
                                    <td class="px-3 py-3"></td>
                                    <td class="px-3 py-3"></td>
                                    <td class="px-3 py-3 text-yellow-500">Meter x 9.8082</td>
                                </tr>
                                <tr data-tank-id="80T13" class="hover:bg-indigo-50 transition">
                                    <td class="px-3 py-3 font-bold">80T13</td>
                                    <td class="px-3 py-3 text-right font-mono text-slate-600">150,000</td>
                                    <td class="px-3 py-3">101-007</td>
                                    <td class="px-3 py-3"><span
                                            class="px-1.5 py-0.5 rounded bg-green-100 text-green-700 font-bold text-[10px]">RBDPO
                                            ( T )</span></td>
                                    <td class="px-3 py-3"></td>
                                    <td class="px-3 py-3"></td>
                                    <td class="px-3 py-3"></td>
                                </tr>
                                <tr data-tank-id="80T16" class="hover:bg-indigo-50 transition">
                                    <td class="px-3 py-3 font-bold">80T16</td>
                                    <td class="px-3 py-3 text-right font-mono text-slate-600">172,000</td>
                                    <td class="px-3 py-3">102-013</td>
                                    <td class="px-3 py-3"><span
                                            class="px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 font-bold text-[10px]">PE
                                            BULK</span></td>
                                    <td class="px-3 py-3"></td>
                                    <td class="px-3 py-3"></td>
                                    <td class="px-3 py-3 text-yellow-500">(0.92398-0.0006789 x Meter) x 26.4208 x
                                        Temperature</td>
                                </tr>
                                <tr data-tank-id="80T17" class="hover:bg-indigo-50 transition">
                                    <td class="px-3 py-3 font-bold">80T17</td>
                                    <td class="px-3 py-3 text-right font-mono text-slate-600">172,000</td>
                                    <td class="px-3 py-3">101-012</td>
                                    <td class="px-3 py-3"><span
                                            class="px-1.5 py-0.5 rounded bg-purple-100 text-purple-700 font-bold text-[10px]">PE
                                            (T)</span></td>
                                    <td class="px-3 py-3"></td>
                                    <td class="px-3 py-3"></td>
                                    <td class="px-3 py-3 text-yellow-500">(0.92398-0.0006789 x Meter) x 26.4208 x
                                        Temperature</td>
                                </tr>
                                <tr data-tank-id="80T20" class="hover:bg-indigo-50 transition">
                                    <td class="px-3 py-3 font-bold">80T20</td>
                                    <td class="px-3 py-3 text-right font-mono text-slate-600">185,000</td>
                                    <td class="px-3 py-3">101-007</td>
                                    <td class="px-3 py-3"><span
                                            class="px-1.5 py-0.5 rounded bg-green-100 text-green-700 font-bold text-[10px]">RBDPO
                                            ( T )</span></td>
                                    <td class="px-3 py-3"></td>
                                    <td class="px-3 py-3"></td>
                                    <td class="px-3 py-3"></td>
                                </tr>
                                <tr data-tank-id="80T21" class="hover:bg-indigo-50 transition">
                                    <td class="px-3 py-3 font-bold">80T21</td>
                                    <td class="px-3 py-3 text-right font-mono text-slate-600">190,000</td>
                                    <td class="px-3 py-3">101-007</td>
                                    <td class="px-3 py-3"><span
                                            class="px-1.5 py-0.5 rounded bg-green-100 text-green-700 font-bold text-[10px]">RBDPO
                                            ( T )</span></td>
                                    <td class="px-3 py-3"></td>
                                    <td class="px-3 py-3"></td>
                                    <td class="px-3 py-3"></td>
                                </tr>
                                <tr data-tank-id="80T22" class="hover:bg-indigo-50 transition">
                                    <td class="px-3 py-3 font-bold">80T22</td>
                                    <td class="px-3 py-3 text-right font-mono text-slate-600">190,000</td>
                                    <td class="px-3 py-3">101-007</td>
                                    <td class="px-3 py-3"><span
                                            class="px-1.5 py-0.5 rounded bg-green-100 text-green-700 font-bold text-[10px]">RBDPO
                                            ( T )</span></td>
                                    <td class="px-3 py-3"></td>
                                    <td class="px-3 py-3"></td>
                                    <td class="px-3 py-3"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: CHART & FILTER (lg:col-span-8) --}}
            <div class="lg:col-span-5 flex flex-col gap-6 w-full">

                {{-- Filter Section: Diubah menjadi Date Range --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 w-full">
                    <div class="flex flex-col lg:flex-row gap-4 items-end w-full">

                        {{-- Input Tanggal Mulai --}}
                        <div class="w-full lg:w-3/12">
                            <label for="dateStart"
                                class="block mb-1 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Tanggal
                                Mulai</label>
                            <input type="date" id="dateStart"
                                class="w-full bg-slate-50 border border-slate-300 text-slate-700 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 p-2.5">
                        </div>

                        {{-- Input Tanggal Akhir --}}
                        <div class="w-full lg:w-3/12">
                            <label for="dateEnd"
                                class="block mb-1 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Tanggal
                                Akhir</label>
                            <input type="date" id="dateEnd"
                                class="w-full bg-slate-50 border border-slate-300 text-slate-700 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 p-2.5">
                        </div>

                        {{-- Dropdown Pilih Tangki --}}
                        <div class="w-full lg:w-4/12">
                            <label for="globalTankSelector"
                                class="block mb-1 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Pilih
                                Tangki</label>
                            <select id="globalTankSelector"
                                class="w-full bg-slate-50 border border-slate-300 text-slate-700 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 p-2.5 font-medium">
                                <option value="ALL">🔵 TAMPILKAN SEMUA TANGKI</option>
                                <option value="B0T9">B0T9 - CFAD</option>
                                <option value="B0T10">B0T10 - PFAD</option>
                                <option value="B0T12">B0T12 - OLEIN</option>
                                <option value="B0T13">B0T13 - RBDPO (T)</option>
                                <option value="B0T16">B0T16 - PE BULK</option>
                                <option value="B0T17">B0T17 - PE (T)</option>
                                <option value="B0T20">B0T20 - RBDPO (T)</option>
                                <option value="B0T21">B0T21 - RBDPO (T)</option>
                                <option value="B0T22">B0T22 - RBDPO (T)</option>
                            </select>
                        </div>

                        {{-- Tombol Filter --}}
                        <div class="w-full lg:w-2/12">
                            <button type="button" id="btnApplyFilter"
                                class="w-full text-white bg-indigo-600 hover:bg-indigo-700 font-medium rounded-lg text-sm px-5 py-2.5 transition-all shadow-md flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                                    </path>
                                </svg>
                                Filter
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Chart Container --}}
                <div
                    class="bg-white rounded-xl shadow-md border border-slate-200 flex-grow w-full h-[550px] overflow-hidden flex flex-col">
                    <div
                        class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center shrink-0">
                        <h5 class="font-bold text-slate-700 flex items-center gap-2">
                            📈 Tren Stok Harian (Ton)
                        </h5>
                        <div class="flex items-center gap-3">
                            <span
                                class="inline-flex items-center bg-green-100 text-green-800 text-[10px] font-bold px-2.5 py-0.5 rounded-full animate-pulse">
                                LIVE
                            </span>
                        </div>
                    </div>
                    <div class="relative w-full flex-grow p-4">
                        <canvas id="tankYardBDTChart"></canvas>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Library Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    {{-- Library jQuery (jika belum ada) --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(function () {

            // 1. Definisikan Data Statis (untuk keperluan demo)
            const allTanksData = [
                { id: 'B0T9', label: 'B0T9 - CFAD', color: '#ef4444', data: [30000, 32000, 31000, 35000, 34000, 38000, 39000] },
                { id: 'B0T10', label: 'B0T10 - PFAD', color: '#f97316', data: [60000, 62000, 61000, 65000, 68000, 70000, 72000] },
                { id: 'B0T12', label: 'B0T12 - OLEIN', color: '#eab308', data: [50000, 52000, 55000, 54000, 58000, 60000, 65000] },
                { id: 'B0T13', label: 'B0T13 - RBDPO', color: '#22c55e', data: [120000, 125000, 123000, 130000, 135000, 140000, 142000] },
                { id: 'B0T16', label: 'B0T16 - PE BULK', color: '#3b82f6', data: [150000, 152000, 155000, 158000, 160000, 165000, 168000] },
                { id: 'B0T17', label: 'B0T17 - PE (T)', color: '#a855f7', data: [140000, 142000, 145000, 143000, 148000, 150000, 155000] },
                { id: 'B0T20', label: 'B0T20 - RBDPO', color: '#16a34a', data: [160000, 162000, 165000, 170000, 175000, 180000, 185000] },
                { id: 'B0T21', label: 'B0T21 - RBDPO', color: '#15803d', data: [165000, 168000, 170000, 172000, 178000, 182000, 188000] },
                { id: 'B0T22', label: 'B0T22 - RBDPO', color: '#14532d', data: [170000, 172000, 175000, 178000, 180000, 185000, 189000] },
            ];
            const labels = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

            // 2. Fungsi untuk merender Chart
            function renderMyChart(datasets) {
                const ctx = document.getElementById('tankYardBDTChart');
                if (!ctx) return;

                // Hancurkan instance chart sebelumnya untuk mencegah memory leak
                if (window.tankChartInstance instanceof Chart) {
                    window.tankChartInstance.destroy();
                }

                // Buat Chart Baru
                window.tankChartInstance = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, font: { size: 10 } } },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                backgroundColor: 'rgba(255, 255, 255, 0.95)',
                                titleColor: '#1e293b',
                                bodyColor: '#475569',
                                borderColor: '#e2e8f0',
                                borderWidth: 1,
                                usePointStyle: true,
                                callbacks: {
                                    label: function (context) {
                                        return context.dataset.label + ': ' + new Intl.NumberFormat('id-ID').format(context.parsed.y) + ' Kg';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: false,
                                grid: { borderDash: [4, 4], color: '#f1f5f9' },
                                ticks: { color: '#64748b', font: { size: 10 }, callback: function (value) { return (value / 1000) + 'T'; } }
                            },
                            x: { grid: { display: false }, ticks: { color: '#64748b', font: { size: 10 } } }
                        }
                    }
                });
            }

            // 3. Fungsi Utama untuk menerapkan semua filter
            function applyDashboardFilter() {
                const selectedId = $('#globalTankSelector').val();
                const startDate = $('#dateStart').val();
                const endDate = $('#dateEnd').val();
                const totalBadge = $('#totalTankBadge');

                // --- SIMULASI FILTER DATA ---
                // Di aplikasi nyata, Anda akan menggunakan startDate, endDate, dan selectedId
                // untuk meminta data baru dari server (misalnya via AJAX/Fetch).
                // Karena data di sini statis, kita hanya akan log ke console untuk menunjukkan
                // bahwa nilainya sudah tertangkap.
                console.log(`Filter Diterapkan: Tangki=${selectedId}, Mulai=${startDate || 'N/A'}, Akhir=${endDate || 'N/A'}`);
                // -----------------------------

                // A. Update Tabel berdasarkan pilihan tangki
                $('#tableBody tr').each(function () {
                    const rowId = $(this).data('tank-id');
                    if (selectedId === 'ALL' || rowId === selectedId) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });

                // B. Update Badge
                if (selectedId === 'ALL') {
                    totalBadge.text("All Shown").attr('class', 'bg-white/20 text-white text-[10px] px-2 py-0.5 rounded-md backdrop-blur-sm');
                } else {
                    totalBadge.text("Filtered: " + selectedId).attr('class', 'bg-yellow-400 text-yellow-900 text-[10px] px-2 py-0.5 rounded-md font-bold');
                }

                // C. Persiapan Data untuk Chart
                let chartDatasets = [];
                if (selectedId === 'ALL') {
                    chartDatasets = allTanksData.map(tank => ({
                        label: tank.label,
                        data: tank.data,
                        borderColor: tank.color,
                        backgroundColor: tank.color,
                        borderWidth: 2,
                        pointRadius: 0,
                        pointHoverRadius: 6,
                        tension: 0.4,
                        fill: false
                    }));
                } else {
                    const tank = allTanksData.find(t => t.id === selectedId);
                    if (tank) {
                        chartDatasets = [{
                            label: tank.label,
                            data: tank.data,
                            borderColor: tank.color,
                            backgroundColor: tank.color + '20', // Hex + opacity
                            borderWidth: 3,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: tank.color,
                            pointRadius: 5,
                            fill: true,
                            tension: 0.4
                        }];
                    }
                }

                renderMyChart(chartDatasets);
            }

            // 4. Event Listeners
            // Tombol filter utama
            $('#btnApplyFilter').off('click').on('click', function () {
                applyDashboardFilter();
            });

            // Otomatis filter saat dropdown tangki diubah
            $('#globalTankSelector').off('change').on('change', function () {
                applyDashboardFilter();
            });

            // 5. Inisialisasi Dashboard saat pertama kali dimuat
            // Memberi sedikit delay untuk memastikan semua elemen DOM siap
            setTimeout(() => {
                // Set tanggal default (misal: 7 hari terakhir)
                const today = new Date();
                const lastWeek = new Date();
                lastWeek.setDate(today.getDate() - 7);

                // Format YYYY-MM-DD
                $('#dateEnd').val(today.toISOString().split('T')[0]);
                $('#dateStart').val(lastWeek.toISOString().split('T')[0]);

                // Terapkan filter awal
                applyDashboardFilter();
            }, 100);

        });
    </script>