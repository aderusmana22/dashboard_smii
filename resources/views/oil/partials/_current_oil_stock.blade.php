<div class="min-h-screen bg-slate-50 p-6 font-sans">
    
    <!-- Header -->
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Global Oil Stock Monitoring</h2>
            <p class="text-slate-500 text-sm">Rekapitulasi Stok Aktual (Integrated with QAD System)</p>
        </div>
        <div class="hidden md:block">
            <span class="inline-flex items-center gap-1.5 py-1.5 px-3 rounded-full text-xs font-medium bg-slate-200 text-slate-700 border border-slate-300">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                Database: Connected
            </span>
        </div>
    </div>

    <!-- Grid Layout 50:50 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
        
        <!-- KOLOM KIRI: TABEL DATA -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-slate-100 h-full">
            <div class="bg-gradient-to-r from-slate-700 to-slate-800 px-6 py-4 flex justify-between items-center">
                <h4 class="text-white font-semibold text-lg tracking-wide flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Master Data Stock
                </h4>
                <div class="flex gap-2">
                    <span class="bg-white/20 text-white text-xs px-2 py-1 rounded backdrop-blur-sm">Last Sync: 10:00 AM</span>
                </div>
            </div>

            <div class="overflow-x-auto max-h-[650px] overflow-y-auto custom-scrollbar">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-slate-500 uppercase bg-slate-50 sticky top-0 z-10 shadow-sm">
                        <tr>
                            <th class="px-6 py-3 font-bold tracking-wider">Oil Code</th>
                            <th class="px-6 py-3 font-bold tracking-wider">Description</th>
                            <th class="px-6 py-3 font-bold tracking-wider text-right">Current Value (Kg)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <!-- ZYA801 - PFAD -->
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-3 font-mono text-slate-600 font-semibold">ZYA801</td>
                            <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-bold bg-indigo-100 text-indigo-700">PFAD</span></td>
                            <td class="px-6 py-3 text-right font-mono text-slate-700 font-bold">125,000</td>
                        </tr>
                        <!-- 101-007 - PO -->
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-3 font-mono text-slate-600 font-semibold">101-007</td>
                            <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-bold bg-emerald-100 text-emerald-700">PO (CPO)</span></td>
                            <td class="px-6 py-3 text-right font-mono text-slate-700 font-bold">450,000</td>
                        </tr>
                        <!-- 101-005 - PSS -->
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-3 font-mono text-slate-600 font-semibold">101-005</td>
                            <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-bold bg-blue-100 text-blue-700">PSS (EKSTERNAL)</span></td>
                            <td class="px-6 py-3 text-right font-mono text-slate-700 font-bold">80,000</td>
                        </tr>
                        <!-- 101-036 - PKO -->
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-3 font-mono text-slate-600 font-semibold">101-036</td>
                            <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-bold bg-amber-100 text-amber-700">PKO</span></td>
                            <td class="px-6 py-3 text-right font-mono text-slate-700 font-bold">60,000</td>
                        </tr>
                        <!-- 110-103 - RBDHPO43 -->
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-3 font-mono text-slate-600 font-semibold">110-103</td>
                            <td class="px-6 py-3 text-slate-600">RBDHPO43</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-700 font-bold">22,000</td>
                        </tr>
                        <!-- 110-134 - RBDHPO 55 -->
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-3 font-mono text-slate-600 font-semibold">110-134</td>
                            <td class="px-6 py-3 text-slate-600">RBDHPO 55</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-700 font-bold">18,500</td>
                        </tr>
                        <!-- 101-011 - PE(F)+PE(KM) -->
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-3 font-mono text-slate-600 font-semibold">101-011</td>
                            <td class="px-6 py-3 text-slate-600">PE(F) + PE(KM)</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-700 font-bold">35,000</td>
                        </tr>
                        <!-- 101-012 - PE(T) -->
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-3 font-mono text-slate-600 font-semibold">101-012</td>
                            <td class="px-6 py-3 text-slate-600">PE(T)</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-700 font-bold">90,000</td>
                        </tr>
                        <!-- 102-012 - PE(T) Duplicate/Variant -->
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-3 font-mono text-slate-600 font-semibold">102-012</td>
                            <td class="px-6 py-3 text-slate-600">PE(T) <span class="text-xs text-gray-400 italic">(Var 2)</span></td>
                            <td class="px-6 py-3 text-right font-mono text-slate-700 font-bold">15,000</td>
                        </tr>
                        <!-- 110-128 - RBDHPKO 42 -->
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-3 font-mono text-slate-600 font-semibold">110-128</td>
                            <td class="px-6 py-3 text-slate-600">RBDHPKO 42</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-700 font-bold">11,000</td>
                        </tr>
                        <!-- 110-130 - RBDHSBO 36 -->
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-3 font-mono text-slate-600 font-semibold">110-130</td>
                            <td class="px-6 py-3 text-slate-600">RBDHSBO 36</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-700 font-bold">8,000</td>
                        </tr>
                        <!-- 110-147 - RBDHCS 58 -->
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-3 font-mono text-slate-600 font-semibold">110-147</td>
                            <td class="px-6 py-3 text-slate-600">RBDHCS 58</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-700 font-bold">5,000</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- KOLOM KANAN: CONTROLLER & CHART -->
        <div class="flex flex-col gap-6">
            
            <!-- Filter Card -->
            <div class="bg-white rounded-xl shadow-lg border border-slate-100 p-6">
                <h5 class="text-lg font-bold text-slate-700 mb-4 border-l-4 border-slate-600 pl-3">🔍 Filter Report</h5>
                <form id="stockFilter">
                    <div class="mb-4">
                        <label class="block mb-1 text-xs font-semibold text-slate-500 uppercase">Per Tanggal</label>
                        <input type="date" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-slate-500 focus:border-slate-500 block p-2.5">
                    </div>

                    <div class="mb-5">
                        <label class="block mb-1 text-xs font-semibold text-slate-500 uppercase">Kategori Produk</label>
                        <select id="categorySelector" onchange="updateStockChart()" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-slate-500 focus:border-slate-500 block p-2.5">
                            <option value="ALL">Semua Produk</option>
                            <option value="RAW">Bahan Baku (PO, PSS, PKO)</option>
                            <option value="PROCESSED">Produk Olahan (RBD...)</option>
                        </select>
                    </div>

                    <button type="button" onclick="updateStockChart()" class="w-full text-white bg-slate-700 hover:bg-slate-800 focus:ring-4 focus:ring-slate-300 font-medium rounded-lg text-sm px-5 py-2.5 transition-all shadow-md">
                        Generate Report
                    </button>
                </form>
            </div>

            <!-- Chart Card -->
            <div class="bg-white rounded-xl shadow-lg border border-slate-100 flex-grow">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                    <h5 class="font-bold text-slate-700">Distribusi Stok (Top 5)</h5>
                </div>
                <div class="p-6">
                    <div class="h-[350px] w-full relative">
                        <canvas id="currentOilStockChart"></canvas>
                        <!-- Center Label Doughnut -->
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <div class="text-center">
                                <span class="block text-2xl font-bold text-slate-700">TOTAL</span>
                                <span class="block text-sm text-slate-500 font-mono" id="totalStockLabel">...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let stockChart = null;

    function renderStockChart(labels, dataValues) {
        const ctx = document.getElementById('currentOilStockChart').getContext('2d');
        
        // Hitung Total untuk ditampilkan di tengah
        const total = dataValues.reduce((a, b) => a + b, 0);
        document.getElementById('totalStockLabel').innerText = (total / 1000).toFixed(1) + ' Ton';

        // Warna Modern (Indigo to Teal palette)
        const colors = [
            '#4f46e5', // Indigo 600
            '#06b6d4', // Cyan 500
            '#10b981', // Emerald 500
            '#f59e0b', // Amber 500
            '#ec4899', // Pink 500
            '#94a3b8'  // Slate 400 (Others)
        ];

        if (stockChart) { stockChart.destroy(); }

        stockChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Stok (Kg)',
                    data: dataValues,
                    backgroundColor: colors,
                    borderWidth: 0,
                    hoverOffset: 15
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%', // Donat lebih tipis (modern)
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, padding: 15 }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let val = context.raw;
                                return ` ${context.label}: ${new Intl.NumberFormat('id-ID').format(val)} Kg`;
                            }
                        }
                    }
                }
            }
        });
    }

    function updateStockChart() {
        const selector = document.getElementById('categorySelector').value;
        
        // Data Simulasi (Nilai dari Tabel di atas)
        let rawData = {
            'PO (CPO)': 450000,
            'PFAD': 125000,
            'PE (T)': 105000, // Total PE
            'PSS': 80000,
            'PKO': 60000,
            'RBD... (Others)': 64500 // Sisa produk olahan
        };

        let labels = [];
        let data = [];

        if (selector === 'ALL' || selector === 'RAW') {
            labels = Object.keys(rawData);
            data = Object.values(rawData);
        } else if (selector === 'PROCESSED') {
            // Simulasi filter processed only
            labels = ['RBDHPO43', 'RBDHPO55', 'RBDHPKO', 'RBDHSBO', 'RBDHCS'];
            data = [22000, 18500, 11000, 8000, 5000];
        }

        renderStockChart(labels, data);
    }

    // Init
    document.addEventListener('DOMContentLoaded', updateStockChart);
</script>