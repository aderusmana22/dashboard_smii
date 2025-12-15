<div class="min-h-screen bg-slate-50 p-6 font-sans">
    
    <!-- Header -->
    <div class="mb-8 flex items-end justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Tank Yard 1T</h2>
            <p class="text-slate-500 text-sm">Distribusi Penyimpanan Minyak (PSS, PO, PKO, SBO)</p>
        </div>
        <div class="hidden md:block">
            <span class="inline-flex items-center gap-1.5 py-1.5 px-3 rounded-full text-xs font-medium bg-cyan-100 text-cyan-800 border border-cyan-200">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Zone 1 Status: Active
            </span>
        </div>
    </div>

    <!-- Grid Layout 50:50 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
        
        <!-- KOLOM KIRI: TABEL DATA -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-slate-100 h-full">
            <div class="bg-gradient-to-r from-cyan-600 to-blue-600 px-6 py-4 flex justify-between items-center">
                <h4 class="text-white font-semibold text-lg tracking-wide flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    Inventory List
                </h4>
                <div class="flex gap-2">
                    <span class="bg-white/20 text-white text-xs px-2 py-1 rounded backdrop-blur-sm">20 Tanks</span>
                </div>
            </div>

            <div class="overflow-x-auto max-h-[650px] overflow-y-auto custom-scrollbar">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-slate-500 uppercase bg-slate-50 sticky top-0 z-10 shadow-sm">
                        <tr>
                            <th class="px-6 py-3 font-bold tracking-wider">Tank Code</th>
                            <th class="px-6 py-3 font-bold tracking-wider text-right">Capacity (Kg)</th>
                            <th class="px-6 py-3 font-bold tracking-wider">Oil Code</th>
                            <th class="px-6 py-3 font-bold tracking-wider">Description</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <!-- 1T1 -->
                        <tr class="hover:bg-cyan-50/50 transition">
                            <td class="px-6 py-3 font-semibold text-slate-700">1T1</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-600">100,000</td>
                            <td class="px-6 py-3 text-slate-500">101-005</td>
                            <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-bold bg-blue-100 text-blue-700">PSS</span></td>
                        </tr>
                        <!-- 1T2 -->
                        <tr class="hover:bg-cyan-50/50 transition">
                            <td class="px-6 py-3 font-semibold text-slate-700">1T2</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-600">100,000</td>
                            <td class="px-6 py-3 text-slate-500">101-005</td>
                            <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-bold bg-blue-100 text-blue-700">PSS</span></td>
                        </tr>
                        <!-- 1T3 -->
                        <tr class="hover:bg-cyan-50/50 transition">
                            <td class="px-6 py-3 font-semibold text-slate-700">1T3</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-600">50,000</td>
                            <td class="px-6 py-3 text-slate-500">101-070</td>
                            <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-bold bg-green-100 text-green-700">PO /SG</span></td>
                        </tr>
                        <!-- 1T4 (Empty) -->
                        <tr class="bg-slate-50 hover:bg-slate-100 transition">
                            <td class="px-6 py-3 font-semibold text-slate-500">1T4</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-400">50,000</td>
                            <td class="px-6 py-3 text-slate-400">-</td>
                            <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-medium bg-slate-200 text-slate-500 border border-slate-300">Available</span></td>
                        </tr>
                        <!-- 1T5 -->
                        <tr class="hover:bg-cyan-50/50 transition">
                            <td class="px-6 py-3 font-semibold text-slate-700">1T5</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-600">50,000</td>
                            <td class="px-6 py-3 text-slate-500">101-071</td>
                            <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-bold bg-indigo-100 text-indigo-700">PSS /SG</span></td>
                        </tr>
                        <!-- 1T6 -->
                        <tr class="hover:bg-cyan-50/50 transition">
                            <td class="px-6 py-3 font-semibold text-slate-700">1T6</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-600">50,000</td>
                            <td class="px-6 py-3 text-slate-500">101-070</td>
                            <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-bold bg-green-100 text-green-700">PO /SG</span></td>
                        </tr>
                        <!-- 1T7 -->
                        <tr class="hover:bg-cyan-50/50 transition">
                            <td class="px-6 py-3 font-semibold text-slate-700">1T7</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-600">50,000</td>
                            <td class="px-6 py-3 text-slate-500">101-036</td>
                            <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-bold bg-amber-100 text-amber-700">PKO</span></td>
                        </tr>
                        <!-- 1T8 -->
                        <tr class="hover:bg-cyan-50/50 transition">
                            <td class="px-6 py-3 font-semibold text-slate-700">1T8</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-600">50,000</td>
                            <td class="px-6 py-3 text-slate-500">111-101</td>
                            <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-bold bg-red-100 text-red-700">HCNO</span></td>
                        </tr>
                        <!-- 1T9 -->
                        <tr class="hover:bg-cyan-50/50 transition">
                            <td class="px-6 py-3 font-semibold text-slate-700">1T9</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-600">25,000</td>
                            <td class="px-6 py-3 text-slate-500">101-010</td>
                            <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-bold bg-yellow-100 text-yellow-700">SBO</span></td>
                        </tr>
                        <!-- 1T10 -->
                        <tr class="hover:bg-cyan-50/50 transition">
                            <td class="px-6 py-3 font-semibold text-slate-700">1T10</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-600">25,000</td>
                            <td class="px-6 py-3 text-slate-500">101-010</td>
                            <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-bold bg-yellow-100 text-yellow-700">SBO</span></td>
                        </tr>
                        <!-- 1T11 (Empty) -->
                        <tr class="bg-slate-50 hover:bg-slate-100 transition">
                            <td class="px-6 py-3 font-semibold text-slate-500">1T11</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-400">25,000</td>
                            <td class="px-6 py-3 text-slate-400">-</td>
                            <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-medium bg-slate-200 text-slate-500 border border-slate-300">Available</span></td>
                        </tr>
                        <!-- 1T12 -->
                        <tr class="hover:bg-cyan-50/50 transition">
                            <td class="px-6 py-3 font-semibold text-slate-700">1T12</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-600">25,000</td>
                            <td class="px-6 py-3 text-slate-500">101-038</td>
                            <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-bold bg-orange-100 text-orange-700">RBD PKS</span></td>
                        </tr>
                        <!-- 1T13 -->
                        <tr class="hover:bg-cyan-50/50 transition">
                            <td class="px-6 py-3 font-semibold text-slate-700">1T13</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-600">200,000</td>
                            <td class="px-6 py-3 text-slate-500">101-007</td>
                            <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-bold bg-emerald-100 text-emerald-700">PO (T)</span></td>
                        </tr>
                        <!-- 1T14 -->
                        <tr class="hover:bg-cyan-50/50 transition">
                            <td class="px-6 py-3 font-semibold text-slate-700">1T14</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-600">200,000</td>
                            <td class="px-6 py-3 text-slate-500">101-007</td>
                            <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-bold bg-emerald-100 text-emerald-700">PO (T)</span></td>
                        </tr>
                        <!-- 1T17 -->
                        <tr class="hover:bg-cyan-50/50 transition">
                            <td class="px-6 py-3 font-semibold text-slate-700">1T17</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-600">25,000</td>
                            <td class="px-6 py-3 text-slate-500">101-001</td>
                            <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-bold bg-teal-100 text-teal-700">CNO</span></td>
                        </tr>
                        <!-- 1T18 -->
                        <tr class="hover:bg-cyan-50/50 transition">
                            <td class="px-6 py-3 font-semibold text-slate-700">1T18</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-600">25,000</td>
                            <td class="px-6 py-3 text-slate-500">101-001</td>
                            <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-bold bg-teal-100 text-teal-700">CNO</span></td>
                        </tr>
                        <!-- 1T19 -->
                        <tr class="hover:bg-cyan-50/50 transition">
                            <td class="px-6 py-3 font-semibold text-slate-700">1T19</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-600">25,000</td>
                            <td class="px-6 py-3 text-slate-500">101-001</td>
                            <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-bold bg-amber-100 text-amber-700">PKO</span></td>
                        </tr>
                        <!-- 1T20 -->
                        <tr class="hover:bg-cyan-50/50 transition">
                            <td class="px-6 py-3 font-semibold text-slate-700">1T20</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-600">25,000</td>
                            <td class="px-6 py-3 text-slate-500">101-036</td>
                            <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-bold bg-amber-100 text-amber-700">PKO</span></td>
                        </tr>
                        <!-- 1T21 -->
                        <tr class="hover:bg-cyan-50/50 transition">
                            <td class="px-6 py-3 font-semibold text-slate-700">1T21</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-600">25,000</td>
                            <td class="px-6 py-3 text-slate-500">101-012</td>
                            <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-bold bg-purple-100 text-purple-700">PE (T)</span></td>
                        </tr>
                        <!-- 1T22 (Empty) -->
                        <tr class="bg-slate-50 hover:bg-slate-100 transition">
                            <td class="px-6 py-3 font-semibold text-slate-500">1T22</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-400">25,000</td>
                            <td class="px-6 py-3 text-slate-400">-</td>
                            <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-medium bg-slate-200 text-slate-500 border border-slate-300">Available</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- KOLOM KANAN: FILTER & CHART -->
        <div class="flex flex-col gap-6">
            
            <!-- Card Filter -->
            <div class="bg-white rounded-xl shadow-lg border border-slate-100 p-6">
                <h5 class="text-lg font-bold text-slate-700 mb-4 border-l-4 border-cyan-500 pl-3">🔍 Filter Inventory</h5>
                <form id="yard1TFilter">
                    <div class="mb-4">
                        <label class="block mb-1 text-xs font-semibold text-slate-500 uppercase">Per Tanggal</label>
                        <input type="date" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-cyan-500 focus:border-cyan-500 block p-2.5">
                    </div>

                    <div class="mb-5">
                        <label class="block mb-1 text-xs font-semibold text-slate-500 uppercase">Kategori Minyak</label>
                        <select id="oilTypeSelector" onchange="update1TChart()" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-cyan-500 focus:border-cyan-500 block p-2.5">
                            <option value="ALL">Semua Tipe Minyak</option>
                            <option value="PSS">PSS - Palm Stearin</option>
                            <option value="PO">PO / PO (T) - Palm Oil</option>
                            <option value="PKO">PKO - Kernel Oil</option>
                            <option value="SBO">SBO - Soybean Oil</option>
                        </select>
                    </div>

                    <button type="button" onclick="update1TChart()" class="w-full text-white bg-cyan-600 hover:bg-cyan-700 focus:ring-4 focus:ring-cyan-300 font-medium rounded-lg text-sm px-5 py-2.5 transition-all shadow-md">
                        Terapkan Filter
                    </button>
                </form>
            </div>

            <!-- Card Chart -->
            <div class="bg-white rounded-xl shadow-lg border border-slate-100 flex-grow">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                    <h5 class="font-bold text-slate-700">Komposisi Stok (Doughnut)</h5>
                    <span class="text-xs text-slate-500 bg-slate-200 px-2 py-0.5 rounded">Real-time</span>
                </div>
                <div class="p-6">
                    <div class="h-[350px] w-full relative">
                        <canvas id="tankYard1TChart"></canvas>
                        <!-- Center Label untuk Doughnut Chart -->
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <div class="text-center">
                                <span class="block text-3xl font-bold text-slate-700">1T</span>
                                <span class="block text-xs text-slate-400">Yard</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let yardChart = null;

    function renderYardChart(labels, dataValues, colors) {
        const ctx = document.getElementById('tankYard1TChart').getContext('2d');
        
        if (yardChart) { yardChart.destroy(); }

        yardChart = new Chart(ctx, {
            type: 'doughnut', // Doughnut terlihat lebih modern dari Pie
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total Stock (Kg)',
                    data: dataValues,
                    backgroundColor: colors,
                    borderWidth: 0, // Borderless agar lebih clean
                    hoverOffset: 10 // Efek pop-out saat hover
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%', // Membuat lubang tengah lebih besar
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: { size: 11 }
                        }
                    },
                    title: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        titleColor: '#1e293b',
                        bodyColor: '#475569',
                        borderColor: '#e2e8f0',
                        borderWidth: 1,
                        padding: 10,
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                let value = context.raw || 0;
                                return ` ${label}: ${new Intl.NumberFormat('id-ID').format(value)} Kg`;
                            }
                        }
                    }
                }
            }
        });
    }

    function update1TChart() {
        const filter = document.getElementById('oilTypeSelector').value;
        
        // Data Dummy (Bisa diganti data dari Controller/API)
        // PSS, PO/SG, PKO, PO (T), Lainnya, Available/Empty
        let baseLabels = ['PSS', 'PO (T) / SG', 'PKO / CNO', 'SBO', 'Empty Space'];
        let baseData = [250000, 480000, 125000, 50000, 100000]; // Total kg estimasi dari tabel
        let baseColors = [
            '#3b82f6', // Blue (PSS)
            '#10b981', // Emerald (PO)
            '#f59e0b', // Amber (PKO)
            '#eab308', // Yellow (SBO)
            '#e2e8f0'  // Slate (Empty)
        ];

        // Logika Filter Sederhana untuk demo visualisasi
        if (filter === 'PSS') {
            // Highlight PSS, sisanya abu-abu
            baseColors = ['#3b82f6', '#f1f5f9', '#f1f5f9', '#f1f5f9', '#f1f5f9'];
        } else if (filter === 'PO') {
            baseColors = ['#f1f5f9', '#10b981', '#f1f5f9', '#f1f5f9', '#f1f5f9'];
        }

        renderYardChart(baseLabels, baseData, baseColors);
    }

    // Init Chart saat load
    document.addEventListener('DOMContentLoaded', update1TChart);
</script>