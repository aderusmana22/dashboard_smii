{{--
CATATAN PENTING TENTANG LEBAR LAYOUT:
Pastikan container induk yang membungkus kode ini tidak memiliki batasan lebar
agar komponen dapat melebar secara penuh.
--}}

<div class="min-h-screen bg-slate-50 p-6 font-sans">
    <!-- Grid Layout 50:50 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">

        <!-- KOLOM KIRI: DATA TABLE -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-slate-100 h-full flex flex-col">
            <div class="bg-gradient-to-r from-emerald-600 to-teal-500 px-6 py-4 flex justify-between items-center shrink-0">
                <h4 class="text-white font-semibold text-lg tracking-wide flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                    Data Tangki Produksi
                </h4>
                <div class="flex gap-2">
                    <span id="filterStatusBadge" class="bg-white/20 text-white text-xs px-2 py-1 rounded backdrop-blur-sm">16 Unit</span>
                </div>
            </div>

            <div class="overflow-x-auto max-h-[650px] overflow-y-auto custom-scrollbar p-0 flex-grow">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-slate-500 uppercase bg-slate-50 sticky top-0 z-10 shadow-sm">
                        <tr>
                            <th class="px-6 py-3 font-bold tracking-wider">Tank Name</th>
                            <th class="px-6 py-3 font-bold tracking-wider text-right">Capacity (Kg)</th>
                            <th class="px-6 py-3 font-bold tracking-wider text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody id="productionTanksBody" class="divide-y divide-slate-100">
                        <!-- Drop Tanks -->
                        <tr data-group="DROPTANK" class="hover:bg-emerald-50/50 transition">
                            <td class="px-6 py-3 font-medium text-slate-700">Drop Tank 1</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-600">10,000</td>
                            <td class="px-6 py-3 text-center"><span class="px-2 py-0.5 rounded text-xs bg-slate-100 text-slate-600 border border-slate-200">Holding</span></td>
                        </tr>
                        <tr data-group="DROPTANK" class="hover:bg-emerald-50/50 transition">
                            <td class="px-6 py-3 font-medium text-slate-700">Drop Tank 2</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-600">10,000</td>
                            <td class="px-6 py-3 text-center"><span class="px-2 py-0.5 rounded text-xs bg-slate-100 text-slate-600 border border-slate-200">Holding</span></td>
                        </tr>
                        <tr data-group="DROPTANK" class="hover:bg-emerald-50/50 transition">
                            <td class="px-6 py-3 font-medium text-slate-700">Drop Tank 3</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-600">10,000</td>
                            <td class="px-6 py-3 text-center"><span class="px-2 py-0.5 rounded text-xs bg-slate-100 text-slate-600 border border-slate-200">Holding</span></td>
                        </tr>
                        <tr data-group="DROPTANK" class="hover:bg-emerald-50/50 transition">
                            <td class="px-6 py-3 font-medium text-slate-700">Drop Tank 4</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-600">10,000</td>
                            <td class="px-6 py-3 text-center"><span class="px-2 py-0.5 rounded text-xs bg-slate-100 text-slate-600 border border-slate-200">Holding</span></td>
                        </tr>
                        <!-- Process Tanks -->
                        <tr data-group="PROCESS" class="hover:bg-emerald-50/50 transition bg-slate-50/30">
                            <td class="px-6 py-3 font-medium text-slate-700">N.W.B.</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-600">10,000</td>
                            <td class="px-6 py-3 text-center"><span class="px-2 py-0.5 rounded text-xs bg-blue-100 text-blue-700 border border-blue-200">Process</span></td>
                        </tr>
                        <tr data-group="PROCESS" class="hover:bg-emerald-50/50 transition bg-slate-50/30">
                            <td class="px-6 py-3 font-medium text-slate-700">Hydro</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-600">10,000</td>
                            <td class="px-6 py-3 text-center"><span class="px-2 py-0.5 rounded text-xs bg-blue-100 text-blue-700 border border-blue-200">Process</span></td>
                        </tr>
                        <!-- Crystalizers -->
                        <tr data-group="CRYSTALIZER" class="hover:bg-emerald-50/50 transition">
                            <td class="px-6 py-3 font-medium text-slate-700">Crystalizer 1</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-600">40,000</td>
                            <td class="px-6 py-3 text-center"><span class="px-2 py-0.5 rounded text-xs bg-teal-100 text-teal-700 border border-teal-200">Cooling</span></td>
                        </tr>
                        <tr data-group="CRYSTALIZER" class="hover:bg-emerald-50/50 transition">
                            <td class="px-6 py-3 font-medium text-slate-700">Crystalizer 2</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-600">40,000</td>
                            <td class="px-6 py-3 text-center"><span class="px-2 py-0.5 rounded text-xs bg-teal-100 text-teal-700 border border-teal-200">Cooling</span></td>
                        </tr>
                        <tr data-group="CRYSTALIZER" class="hover:bg-emerald-50/50 transition">
                            <td class="px-6 py-3 font-medium text-slate-700">Crystalizer 3</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-600">40,000</td>
                            <td class="px-6 py-3 text-center"><span class="px-2 py-0.5 rounded text-xs bg-teal-100 text-teal-700 border border-teal-200">Cooling</span></td>
                        </tr>
                        <tr data-group="CRYSTALIZER" class="hover:bg-emerald-50/50 transition">
                            <td class="px-6 py-3 font-medium text-slate-700">Crystalizer 4</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-600">40,000</td>
                            <td class="px-6 py-3 text-center"><span class="px-2 py-0.5 rounded text-xs bg-teal-100 text-teal-700 border border-teal-200">Cooling</span></td>
                        </tr>
                        <!-- S Tanks -->
                        <tr data-group="STANK" class="hover:bg-emerald-50/50 transition bg-slate-50/30">
                            <td class="px-6 py-3 font-medium text-slate-700">S12 Tank</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-600">5,000</td>
                            <td class="px-6 py-3 text-center"><span class="px-2 py-0.5 rounded text-xs bg-orange-100 text-orange-700 border border-orange-200">Storage</span></td>
                        </tr>
                        <tr data-group="STANK" class="hover:bg-emerald-50/50 transition bg-slate-50/30">
                            <td class="px-6 py-3 font-medium text-slate-700">S13 Tank</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-600">13,000</td>
                            <td class="px-6 py-3 text-center"><span class="px-2 py-0.5 rounded text-xs bg-orange-100 text-orange-700 border border-orange-200">Storage</span></td>
                        </tr>
                        <tr data-group="STANK" class="hover:bg-emerald-50/50 transition bg-slate-50/30">
                            <td class="px-6 py-3 font-medium text-slate-700">S14 Tank</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-600">5,000</td>
                            <td class="px-6 py-3 text-center"><span class="px-2 py-0.5 rounded text-xs bg-orange-100 text-orange-700 border border-orange-200">Storage</span></td>
                        </tr>
                        <!-- Deodorizers -->
                        <tr data-group="DEODORIZER" class="hover:bg-emerald-50/50 transition">
                            <td class="px-6 py-3 font-medium text-slate-700">Deodorizer 1</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-600">10,000</td>
                            <td class="px-6 py-3 text-center"><span class="px-2 py-0.5 rounded text-xs bg-red-100 text-red-700 border border-red-200">Heating</span></td>
                        </tr>
                        <tr data-group="DEODORIZER" class="hover:bg-emerald-50/50 transition">
                            <td class="px-6 py-3 font-medium text-slate-700">Deodorizer 2</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-600">10,000</td>
                            <td class="px-6 py-3 text-center"><span class="px-2 py-0.5 rounded text-xs bg-red-100 text-red-700 border border-red-200">Heating</span></td>
                        </tr>
                        <tr data-group="DEODORIZER" class="hover:bg-emerald-50/50 transition">
                            <td class="px-6 py-3 font-medium text-slate-700">Wead Tank</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-600">10,000</td>
                            <td class="px-6 py-3 text-center"><span class="px-2 py-0.5 rounded text-xs bg-slate-100 text-slate-600">Other</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- KOLOM KANAN: CONTROLLER & CHART -->
        <div class="flex flex-col gap-6">
            <!-- Filter Card -->
            <div class="bg-white rounded-xl shadow-lg border border-slate-100 p-6">
                <h5 class="text-lg font-bold text-slate-700 mb-4 border-l-4 border-emerald-500 pl-3">⚙️ Filter & Kontrol</h5>
                <div id="refineryFilter">
                    <div class="mb-4">
                        <label for="dateFilter" class="block mb-1 text-xs font-semibold text-slate-500 uppercase">Tanggal Data</label>
                        <input type="date" id="dateFilter" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-emerald-500 focus:border-emerald-500 block p-2.5">
                    </div>

                    <div class="mb-5">
                        <label for="groupSelector" class="block mb-1 text-xs font-semibold text-slate-500 uppercase">Grup Visualisasi</label>
                        <select id="groupSelector" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-emerald-500 focus:border-emerald-500 block p-2.5">
                            <option value="SUMMARY">📊 Summary (Total per Kategori)</option>
                            <option value="CRYSTALIZER">❄️ Detail: Crystalizers</option>
                            <option value="DROPTANK">💧 Detail: Drop Tanks</option>
                            <option value="DEODORIZER">🔥 Detail: Deodorizers</option>
                            <option value="STANK">🛢️ Detail: S-Tanks</option>
                            <option value="PROCESS">⚙️ Detail: Process Tanks</option>
                        </select>
                    </div>

                    <button type="button" id="btnApplyRefineryFilter" class="w-full text-white bg-emerald-600 hover:bg-emerald-700 focus:ring-4 focus:ring-emerald-300 font-medium rounded-lg text-sm px-5 py-2.5 transition-all shadow-md">
                        Terapkan Filter
                    </button>
                </div>
            </div>

            <!-- Chart Card -->
            <div class="bg-white rounded-xl shadow-lg border border-slate-100 flex-grow flex flex-col">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center shrink-0">
                    <h5 id="chartTitle" class="font-bold text-slate-700">Utilisasi Kapasitas (%)</h5>
                </div>
                <div class="p-4 flex-grow relative">
                    <canvas id="batchRefineryChart"></canvas>
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

        // 1. Definisikan Data Statis untuk Chart
        const refineryChartData = {
            'SUMMARY': {
                title: 'Total Stok per Grup (Kg)',
                labels: ['Drop Tanks', 'Process', 'Crystalizers', 'S-Tanks', 'Deodorizers'],
                data: [35000, 18000, 145000, 21000, 15000],
                colors: [
                    'rgba(59, 130, 246, 0.7)',  // Blue
                    'rgba(99, 102, 241, 0.7)',  // Indigo
                    'rgba(16, 185, 129, 0.7)',  // Emerald
                    'rgba(245, 158, 11, 0.7)',  // Amber
                    'rgba(239, 68, 68, 0.7)'    // Red
                ]
            },
            'CRYSTALIZER': {
                title: 'Detail Stok Crystalizer (Kg)',
                labels: ['Cryst 1', 'Cryst 2', 'Cryst 3', 'Cryst 4'],
                data: [38000, 39500, 25000, 40000],
                colors: Array(4).fill('rgba(16, 185, 129, 0.7)') // Emerald Theme
            },
            'DROPTANK': {
                title: 'Detail Stok Drop Tank (Kg)',
                labels: ['DT 1', 'DT 2', 'DT 3', 'DT 4'],
                data: [9000, 8500, 9200, 5000],
                colors: Array(4).fill('rgba(59, 130, 246, 0.7)') // Blue Theme
            },
            'DEODORIZER': {
                title: 'Detail Stok Area Deodorizer (Kg)',
                labels: ['Deo 1', 'Deo 2', 'Wead'],
                data: [8000, 9500, 2000],
                colors: Array(3).fill('rgba(239, 68, 68, 0.7)') // Red Theme
            },
            'STANK': {
                title: 'Detail Stok S-Tank (Kg)',
                labels: ['S12', 'S13', 'S14'],
                data: [4500, 12000, 1000],
                colors: Array(3).fill('rgba(245, 158, 11, 0.7)') // Amber Theme
            },
            'PROCESS': {
                title: 'Detail Stok Process Tank (Kg)',
                labels: ['N.W.B.', 'Hydro'],
                data: [9800, 8200],
                colors: Array(2).fill('rgba(99, 102, 241, 0.7)') // Indigo Theme
            }
        };

        // 2. Fungsi untuk merender Chart
        function renderRefineryChart(chartData) {
            const ctx = document.getElementById('batchRefineryChart');
            if (!ctx) return;

            // Hancurkan instance chart sebelumnya
            if (window.refineryChartInstance instanceof Chart) {
                window.refineryChartInstance.destroy();
            }

            // Update Judul Chart
            $('#chartTitle').text(chartData.title);

            // Buat Chart Baru
            window.refineryChartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Stok (Kg)',
                        data: chartData.data,
                        backgroundColor: chartData.colors,
                        borderColor: chartData.colors.map(c => c.replace('0.7', '1')),
                        borderWidth: 1,
                        borderRadius: 6,
                        barPercentage: 0.6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(255, 255, 255, 0.95)',
                            titleColor: '#1e293b', bodyColor: '#475569',
                            borderColor: '#e2e8f0', borderWidth: 1, padding: 10,
                            callbacks: {
                                label: (context) => 'Stok: ' + new Intl.NumberFormat('id-ID').format(context.raw) + ' Kg'
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { borderDash: [2, 4], color: '#f1f5f9' },
                            ticks: { font: { size: 11 } }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 11, weight: 'bold' }, color: '#64748b' }
                        }
                    }
                }
            });
        }

        // 3. Fungsi Utama untuk menerapkan semua filter
        function applyRefineryFilter() {
            const selectedGroup = $('#groupSelector').val();
            const selectedDate = $('#dateFilter').val();
            const badge = $('#filterStatusBadge');

            // --- SIMULASI FILTER DATA ---
            // Di aplikasi nyata, Anda akan menggunakan selectedDate dan selectedGroup
            // untuk meminta data baru dari server.
            console.log(`Filter Diterapkan: Grup=${selectedGroup}, Tanggal=${selectedDate || 'N/A'}`);
            // -----------------------------

            // A. Update Tabel berdasarkan pilihan grup
            $('#productionTanksBody tr').each(function () {
                const rowGroup = $(this).data('group');
                if (selectedGroup === 'SUMMARY' || rowGroup === selectedGroup) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });

            // B. Update Badge
            if (selectedGroup === 'SUMMARY') {
                badge.text("All Shown").attr('class', 'bg-white/20 text-white text-xs px-2 py-1 rounded backdrop-blur-sm');
            } else {
                const groupName = $('#groupSelector option:selected').text().split(':')[1]?.trim() || selectedGroup;
                badge.text("Filtered: " + groupName).attr('class', 'bg-yellow-400 text-yellow-900 text-xs px-2 py-1 rounded font-bold');
            }

            // C. Update Chart
            const chartData = refineryChartData[selectedGroup];
            if (chartData) {
                renderRefineryChart(chartData);
            }
        }

        // 4. Event Listeners
        $('#btnApplyRefineryFilter').on('click', applyRefineryFilter);
        $('#groupSelector').on('change', applyRefineryFilter);

        // 5. Inisialisasi Dashboard saat pertama kali dimuat
        setTimeout(() => {
            // Set tanggal default ke hari ini
            $('#dateFilter').val(new Date().toISOString().split('T')[0]);
            // Terapkan filter awal
            applyRefineryFilter();
        }, 100);

    });
</script>