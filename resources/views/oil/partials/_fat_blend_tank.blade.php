<div class="min-h-screen bg-slate-50 p-6 font-sans">
    
    <!-- Header -->
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Fat Blend Station</h2>
            <p class="text-slate-500 text-sm">Monitoring Level & Konektivitas PLC</p>
        </div>
        <div class="hidden md:block">
            <span class="inline-flex items-center gap-1.5 py-1.5 px-3 rounded-full text-xs font-medium bg-violet-100 text-violet-800">
                <span class="w-2 h-2 rounded-full bg-violet-600 animate-pulse"></span>
                System Online
            </span>
        </div>
    </div>

    <!-- Grid Layout 50:50 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
        
        <!-- KOLOM KIRI: TABEL DATA -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-slate-100 h-full">
            <div class="bg-gradient-to-r from-violet-600 to-fuchsia-600 px-6 py-4 flex justify-between items-center">
                <h4 class="text-white font-semibold text-lg tracking-wide flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    Data Tangki Blending
                </h4>
                <div class="flex gap-2">
                    <span class="bg-white/20 text-white text-xs px-2 py-1 rounded backdrop-blur-sm">Total: 9 Tangki</span>
                </div>
            </div>

            <div class="overflow-x-auto max-h-[600px] overflow-y-auto custom-scrollbar">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-slate-500 uppercase bg-slate-50 sticky top-0 z-10 shadow-sm">
                        <tr>
                            <th class="px-6 py-3 font-bold tracking-wider">Tank Code</th>
                            <th class="px-6 py-3 font-bold tracking-wider text-right">Capacity (Kg)</th>
                            <th class="px-6 py-3 font-bold tracking-wider text-center">Source Info</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <!-- FB 1 - Manual -->
                        <tr class="hover:bg-violet-50/50 transition">
                            <td class="px-6 py-4 font-semibold text-slate-700">Fat Blend 1</td>
                            <td class="px-6 py-4 text-right font-mono text-slate-600">25,000</td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 border border-amber-200">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    Input Manual
                                </span>
                            </td>
                        </tr>
                        <!-- FB 2 - PLC -->
                        <tr class="hover:bg-violet-50/50 transition">
                            <td class="px-6 py-4 font-semibold text-slate-700">Fat Blend 2</td>
                            <td class="px-6 py-4 text-right font-mono text-slate-600">25,000</td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
                                    Auto PLC
                                </span>
                            </td>
                        </tr>
                        <!-- FB 3 - PLC -->
                        <tr class="hover:bg-violet-50/50 transition">
                            <td class="px-6 py-4 font-semibold text-slate-700">Fat Blend 3</td>
                            <td class="px-6 py-4 text-right font-mono text-slate-600">25,000</td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
                                    Auto PLC
                                </span>
                            </td>
                        </tr>
                        <!-- FB 4 -->
                        <tr class="hover:bg-violet-50/50 transition">
                            <td class="px-6 py-4 font-semibold text-slate-700">Fat Blend 4</td>
                            <td class="px-6 py-4 text-right font-mono text-slate-600">25,000</td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                    Waiting Data
                                </span>
                            </td>
                        </tr>
                        <!-- FB 5 -->
                        <tr class="hover:bg-violet-50/50 transition">
                            <td class="px-6 py-4 font-semibold text-slate-700">Fat Blend 5</td>
                            <td class="px-6 py-4 text-right font-mono text-slate-600">25,000</td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                    Waiting Data
                                </span>
                            </td>
                        </tr>
                        <!-- FB 6 -->
                        <tr class="hover:bg-violet-50/50 transition">
                            <td class="px-6 py-4 font-semibold text-slate-700">Fat Blend 6</td>
                            <td class="px-6 py-4 text-right font-mono text-slate-600">25,000</td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                    Waiting Data
                                </span>
                            </td>
                        </tr>
                        <!-- FB 7 -->
                        <tr class="hover:bg-violet-50/50 transition">
                            <td class="px-6 py-4 font-semibold text-slate-700">Fat Blend 7</td>
                            <td class="px-6 py-4 text-right font-mono text-slate-600">25,000</td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                    Waiting Data
                                </span>
                            </td>
                        </tr>
                        <!-- FB 8 -->
                        <tr class="hover:bg-violet-50/50 transition">
                            <td class="px-6 py-4 font-semibold text-slate-700">Fat Blend 8</td>
                            <td class="px-6 py-4 text-right font-mono text-slate-600">25,000</td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                    Waiting Data
                                </span>
                            </td>
                        </tr>
                        <!-- FB 9 -->
                        <tr class="hover:bg-violet-50/50 transition">
                            <td class="px-6 py-4 font-semibold text-slate-700">Fat Blend 9</td>
                            <td class="px-6 py-4 text-right font-mono text-slate-600">25,000</td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                    Waiting Data
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- KOLOM KANAN: CONTROLLER & CHART -->
        <div class="flex flex-col gap-6">
            
            <!-- Card Filter -->
            <div class="bg-white rounded-xl shadow-lg border border-slate-100 p-6">
                <h5 class="text-lg font-bold text-slate-700 mb-4 border-l-4 border-violet-500 pl-3">🎛️ Panel Kontrol</h5>
                <form id="fatBlendFilter">
                    <div class="mb-4">
                        <label class="block mb-1 text-xs font-semibold text-slate-500 uppercase">Pilih Tanggal</label>
                        <input type="date" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-violet-500 focus:border-violet-500 block p-2.5">
                    </div>

                    <div class="mb-5">
                        <label class="block mb-1 text-xs font-semibold text-slate-500 uppercase">Mode Tampilan</label>
                        <select id="viewMode" onchange="updateChart()" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-violet-500 focus:border-violet-500 block p-2.5">
                            <option value="ALL">📊 Tampilkan Semua (FB1 - FB9)</option>
                            <option value="HIGH">⚠️ Hanya Tangki Penuh (>80%)</option>
                            <option value="LOW">📉 Hanya Tangki Kosong (<20%)</option>
                        </select>
                    </div>

                    <button type="button" onclick="updateChart()" class="w-full text-white bg-violet-600 hover:bg-violet-700 focus:ring-4 focus:ring-violet-300 font-medium rounded-lg text-sm px-5 py-2.5 transition-all shadow-md">
                        Update Visualisasi
                    </button>
                </form>
            </div>

            <!-- Card Chart -->
            <div class="bg-white rounded-xl shadow-lg border border-slate-100 flex-grow">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                    <h5 class="font-bold text-slate-700">Level Tangki (Real-time)</h5>
                    <span class="text-xs text-slate-400 font-mono">Max: 25,000 Kg</span>
                </div>
                <div class="p-4">
                    <div class="h-[350px] w-full relative">
                        <canvas id="fatBlendTankChart"></canvas>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let chartInstance = null;

    function renderChart(labels, dataValues) {
        const ctx = document.getElementById('fatBlendTankChart').getContext('2d');
        
        // Buat Gradient Ungu
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(139, 92, 246, 0.8)'); // Violet atas pekat
        gradient.addColorStop(1, 'rgba(192, 132, 252, 0.2)'); // Fuchsia bawah transparan

        if (chartInstance) { chartInstance.destroy(); }

        chartInstance = new Chart(ctx, {
            type: 'bar', // Menggunakan Bar Chart agar lebih logis untuk perbandingan volume
            data: {
                labels: labels,
                datasets: [{
                    label: 'Stok Terkini (Kg)',
                    data: dataValues,
                    backgroundColor: gradient,
                    borderColor: 'rgba(139, 92, 246, 1)',
                    borderWidth: 1,
                    borderRadius: 4,
                    hoverBackgroundColor: 'rgba(124, 58, 237, 1)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 27000, // Sedikit di atas kapasitas max agar tidak mentok
                        grid: { borderDash: [2, 4] },
                        title: { display: true, text: 'Volume (Kg)' }
                    },
                    x: {
                        grid: { display: false }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let val = context.raw;
                                let percentage = ((val / 25000) * 100).toFixed(1);
                                return ` ${new Intl.NumberFormat('id-ID').format(val)} Kg (${percentage}%)`;
                            }
                        }
                    },
                    annotation: {
                        // Jika anda menggunakan plugin chartjs-plugin-annotation, 
                        // kode ini akan membuat garis merah di kapasitas max.
                    }
                }
            }
        });
    }

    function updateChart() {
        const mode = document.getElementById('viewMode').value;
        
        // Data Simulasi (Ganti dengan Data Backend)
        // Kapasitas max 25.000
        let allLabels = ['FB1', 'FB2', 'FB3', 'FB4', 'FB5', 'FB6', 'FB7', 'FB8', 'FB9'];
        let allData = [22000, 15000, 24500, 4000, 19000, 21000, 12000, 2500, 23000];

        let filteredLabels = [];
        let filteredData = [];

        if (mode === 'HIGH') {
            // Filter hanya yang > 20.000 (80%)
            for(let i=0; i<allData.length; i++) {
                if(allData[i] > 20000) {
                    filteredLabels.push(allLabels[i]);
                    filteredData.push(allData[i]);
                }
            }
        } else if (mode === 'LOW') {
            // Filter hanya yang < 5.000 (20%)
            for(let i=0; i<allData.length; i++) {
                if(allData[i] < 5000) {
                    filteredLabels.push(allLabels[i]);
                    filteredData.push(allData[i]);
                }
            }
        } else {
            // Default All
            filteredLabels = allLabels;
            filteredData = allData;
        }

        renderChart(filteredLabels, filteredData);
    }

    // Init
    document.addEventListener('DOMContentLoaded', updateChart);
</script>