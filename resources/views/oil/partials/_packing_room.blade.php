<div class="min-h-screen bg-slate-50 p-6 font-sans">
    
    <!-- Header -->
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Packing Room Dashboard</h2>
            <p class="text-slate-500 text-sm">Monitoring Tangki Siap Packing (10T1 - 10T9)</p>
        </div>
        <div class="hidden md:block">
            <span class="inline-flex items-center gap-1.5 py-1.5 px-3 rounded-full text-xs font-medium bg-orange-100 text-orange-800 border border-orange-200">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                Line Status: Running
            </span>
        </div>
    </div>

    <!-- Grid Layout 50:50 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
        
        <!-- KOLOM KIRI: TABEL DATA -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-slate-100 h-full">
            <div class="bg-gradient-to-r from-orange-500 to-amber-500 px-6 py-4 flex justify-between items-center">
                <h4 class="text-white font-semibold text-lg tracking-wide flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                    Stok Packing
                </h4>
                <div class="flex gap-2">
                    <span class="bg-white/20 text-white text-xs px-2 py-1 rounded backdrop-blur-sm">9 Units</span>
                </div>
            </div>

            <div class="overflow-x-auto max-h-[600px] overflow-y-auto custom-scrollbar">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-slate-500 uppercase bg-slate-50 sticky top-0 z-10 shadow-sm">
                        <tr>
                            <th class="px-6 py-3 font-bold tracking-wider">Tank Code</th>
                            <th class="px-6 py-3 font-bold tracking-wider text-right">Capacity (Kg)</th>
                            <th class="px-6 py-3 font-bold tracking-wider text-center">Current Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <!-- Loop Data 10T1 - 10T9 -->
                        <!-- 10T1 -->
                        <tr class="hover:bg-orange-50/50 transition">
                            <td class="px-6 py-4 font-semibold text-slate-700">10T1</td>
                            <td class="px-6 py-4 text-right font-mono text-slate-600">10,000</td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">Ready to Pack</span>
                            </td>
                        </tr>
                        <!-- 10T2 -->
                        <tr class="hover:bg-orange-50/50 transition">
                            <td class="px-6 py-4 font-semibold text-slate-700">10T2</td>
                            <td class="px-6 py-4 text-right font-mono text-slate-600">10,000</td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">Ready to Pack</span>
                            </td>
                        </tr>
                        <!-- 10T3 -->
                        <tr class="hover:bg-orange-50/50 transition">
                            <td class="px-6 py-4 font-semibold text-slate-700">10T3</td>
                            <td class="px-6 py-4 text-right font-mono text-slate-600">10,000</td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700 animate-pulse">Filling...</span>
                            </td>
                        </tr>
                        <!-- 10T4 -->
                        <tr class="hover:bg-orange-50/50 transition">
                            <td class="px-6 py-4 font-semibold text-slate-700">10T4</td>
                            <td class="px-6 py-4 text-right font-mono text-slate-600">10,000</td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">Ready to Pack</span>
                            </td>
                        </tr>
                        <!-- 10T5 -->
                        <tr class="hover:bg-orange-50/50 transition">
                            <td class="px-6 py-4 font-semibold text-slate-700">10T5</td>
                            <td class="px-6 py-4 text-right font-mono text-slate-600">10,000</td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">Ready to Pack</span>
                            </td>
                        </tr>
                        <!-- 10T6 -->
                        <tr class="hover:bg-orange-50/50 transition">
                            <td class="px-6 py-4 font-semibold text-slate-700">10T6</td>
                            <td class="px-6 py-4 text-right font-mono text-slate-600">10,000</td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700">Standby</span>
                            </td>
                        </tr>
                        <!-- 10T7 -->
                        <tr class="hover:bg-orange-50/50 transition">
                            <td class="px-6 py-4 font-semibold text-slate-700">10T7</td>
                            <td class="px-6 py-4 text-right font-mono text-slate-600">10,000</td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700">Standby</span>
                            </td>
                        </tr>
                        <!-- 10T8 -->
                        <tr class="hover:bg-orange-50/50 transition">
                            <td class="px-6 py-4 font-semibold text-slate-700">10T8</td>
                            <td class="px-6 py-4 text-right font-mono text-slate-600">10,000</td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-200">Maintenance</span>
                            </td>
                        </tr>
                        <!-- 10T9 -->
                        <tr class="hover:bg-orange-50/50 transition">
                            <td class="px-6 py-4 font-semibold text-slate-700">10T9</td>
                            <td class="px-6 py-4 text-right font-mono text-slate-600">10,000</td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700 animate-pulse">Filling...</span>
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
                <h5 class="text-lg font-bold text-slate-700 mb-4 border-l-4 border-orange-500 pl-3">📦 Kontrol Packing</h5>
                <form id="packingFilter">
                    <div class="mb-4">
                        <label class="block mb-1 text-xs font-semibold text-slate-500 uppercase">Tanggal Data</label>
                        <input type="date" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block p-2.5">
                    </div>

                    <div class="mb-5">
                        <label class="block mb-1 text-xs font-semibold text-slate-500 uppercase">Filter Kondisi</label>
                        <select id="packingSelector" onchange="updatePackingChart()" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block p-2.5">
                            <option value="ALL">Tampilkan Semua</option>
                            <option value="READY">Siap Packing (> 8,000 Kg)</option>
                            <option value="LOW">Low Level (< 2,000 Kg)</option>
                        </select>
                    </div>

                    <button type="button" onclick="updatePackingChart()" class="w-full text-white bg-orange-500 hover:bg-orange-600 focus:ring-4 focus:ring-orange-300 font-medium rounded-lg text-sm px-5 py-2.5 transition-all shadow-md">
                        Update Grafik
                    </button>
                </form>
            </div>

            <!-- Card Chart -->
            <div class="bg-white rounded-xl shadow-lg border border-slate-100 flex-grow">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                    <h5 class="font-bold text-slate-700">Volume per Tangki</h5>
                    <span class="text-xs text-orange-600 bg-orange-100 px-2 py-0.5 rounded font-bold">Max: 10,000 Kg</span>
                </div>
                <div class="p-4">
                    <div class="h-[350px] w-full">
                        <canvas id="packingRoomChart"></canvas>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let packingChart = null;

    function renderPackingChart(labels, dataValues) {
        const ctx = document.getElementById('packingRoomChart').getContext('2d');
        
        // Buat Gradient Orange
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(249, 115, 22, 0.8)'); // Orange-500
        gradient.addColorStop(1, 'rgba(251, 191, 36, 0.2)'); // Amber-300 transparent

        if (packingChart) { packingChart.destroy(); }

        packingChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Volume (Kg)',
                    data: dataValues,
                    backgroundColor: gradient,
                    borderColor: 'rgba(249, 115, 22, 1)',
                    borderWidth: 1,
                    borderRadius: 5,
                    barPercentage: 0.6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let val = context.raw;
                                let pct = (val / 10000 * 100).toFixed(0);
                                return ` ${new Intl.NumberFormat('id-ID').format(val)} Kg (${pct}%)`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 11000, // Sedikit di atas kapasitas
                        grid: { borderDash: [2, 4] },
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10, weight: 'bold' } }
                    }
                }
            }
        });
    }

    function updatePackingChart() {
        const filter = document.getElementById('packingSelector').value;
        
        // Data Simulasi
        // 10T1 - 10T9
        let allLabels = ['10T1', '10T2', '10T3', '10T4', '10T5', '10T6', '10T7', '10T8', '10T9'];
        let allData = [8500, 9200, 4500, 9500, 8800, 2000, 1500, 0, 5000];

        let labels = [];
        let data = [];

        for(let i=0; i<allData.length; i++) {
            let val = allData[i];
            let shouldAdd = false;

            if (filter === 'ALL') {
                shouldAdd = true;
            } else if (filter === 'READY' && val >= 8000) {
                shouldAdd = true;
            } else if (filter === 'LOW' && val <= 2000) {
                shouldAdd = true;
            }

            if(shouldAdd) {
                labels.push(allLabels[i]);
                data.push(val);
            }
        }

        renderPackingChart(labels, data);
    }

    // Init
    document.addEventListener('DOMContentLoaded', updatePackingChart);
</script>