<div class="min-h-screen bg-slate-50 p-6 font-sans">
    
    <!-- Header Section -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Utility Gas Monitoring</h2>
            <p class="text-slate-500 text-sm">Pemantauan Stok Gas Pendukung Produksi</p>
        </div>
        
        <!-- Badge Manual Input -->
        <div class="flex items-center gap-3 bg-white px-4 py-2 rounded-lg shadow-sm border border-slate-200">
            <div class="flex h-3 w-3 relative">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-orange-500"></span>
            </div>
            <span class="text-sm font-medium text-slate-600">Mode: Input Manual</span>
            <button class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-1 rounded transition">
                Update Data
            </button>
        </div>
    </div>

    <!-- Grid 3 Kolom -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- 1. HYDROGEN (High Pressure) - RED THEME -->
        <div class="bg-white rounded-xl shadow-lg border border-slate-100 overflow-hidden flex flex-col">
            <div class="bg-gradient-to-r from-red-600 to-rose-600 px-6 py-4 flex justify-between items-center">
                <h5 class="text-white font-bold flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    Hydrogen (H2)
                </h5>
                <span class="text-xs bg-white/20 text-white px-2 py-1 rounded">High Pressure</span>
            </div>
            <div class="p-6 flex-grow flex flex-col justify-between">
                
                <!-- Table Data -->
                <div class="overflow-hidden rounded-lg border border-slate-200 mb-4">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-bold">
                            <tr>
                                <th class="px-4 py-2">Torpedo No.</th>
                                <th class="px-4 py-2 text-right">Pressure</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr>
                                <td class="px-4 py-2 font-medium text-slate-700">#04</td>
                                <td class="px-4 py-2 text-right font-mono text-red-600 font-bold">140 Bar</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 font-medium text-slate-700">#05</td>
                                <td class="px-4 py-2 text-right font-mono text-slate-400">Empty</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Visual Bar Chart -->
                <div>
                    <h6 class="text-xs font-bold text-slate-400 uppercase mb-2">Pressure Level</h6>
                    <div class="h-[120px] w-full">
                        <canvas id="hydrogenChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. NITROGEN (Liquid/Cooling) - BLUE THEME -->
        <div class="bg-white rounded-xl shadow-lg border border-slate-100 overflow-hidden flex flex-col">
            <div class="bg-gradient-to-r from-blue-600 to-cyan-600 px-6 py-4 flex justify-between items-center">
                <h5 class="text-white font-bold flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                    Nitrogen (N2)
                </h5>
                <span class="text-xs bg-white/20 text-white px-2 py-1 rounded">Liquid Tank</span>
            </div>
            <div class="p-6 flex-grow">
                
                <!-- Main Stock Display -->
                <div class="text-center mb-6">
                    <span class="block text-sm text-slate-500 mb-1">Current Stock</span>
                    <div class="text-5xl font-bold text-blue-600">78</div>
                    <span class="text-sm font-medium text-slate-400">Inch Water</span>
                </div>

                <!-- Progress Bar Visual -->
                <div class="space-y-4">
                    <div class="flex justify-between text-xs font-semibold text-slate-600">
                        <span>0</span>
                        <span>Minimum: 65</span>
                        <span>Max: 100</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-4 relative overflow-hidden">
                        <!-- Progress -->
                        <div class="bg-blue-500 h-4 rounded-full transition-all duration-1000" style="width: 78%"></div>
                        <!-- Minimum Marker Line -->
                        <div class="absolute top-0 bottom-0 w-0.5 bg-red-500 z-10" style="left: 65%" title="Minimum Limit"></div>
                    </div>
                    
                    <!-- Alert Logic Simulation -->
                    <div class="bg-green-50 border border-green-200 rounded-lg p-3 flex items-start gap-2">
                        <svg class="w-5 h-5 text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <div>
                            <span class="text-sm font-bold text-green-700 block">Kondisi Aman</span>
                            <span class="text-xs text-green-600">Stok di atas batas minimum (65).</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. AMMONIA (Chemical) - EMERALD THEME -->
        <div class="bg-white rounded-xl shadow-lg border border-slate-100 overflow-hidden flex flex-col">
            <div class="bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-4 flex justify-between items-center">
                <h5 class="text-white font-bold flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                    Ammonia (NH3)
                </h5>
                <span class="text-xs bg-white/20 text-white px-2 py-1 rounded">Cylinders</span>
            </div>
            <div class="p-6 flex-grow flex flex-col">
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="bg-emerald-50 rounded-lg p-3 text-center border border-emerald-100">
                        <span class="block text-2xl font-bold text-emerald-700">7</span>
                        <span class="text-xs font-semibold text-emerald-600 uppercase">Full</span>
                    </div>
                    <div class="bg-slate-100 rounded-lg p-3 text-center border border-slate-200">
                        <span class="block text-2xl font-bold text-slate-600">5</span>
                        <span class="text-xs font-semibold text-slate-500 uppercase">Empty</span>
                    </div>
                </div>

                <!-- Doughnut Chart Area -->
                <div class="flex-grow relative h-[150px] w-full">
                    <canvas id="ammoniaChart"></canvas>
                </div>

                <div class="mt-2 text-center">
                    <span class="text-xs text-slate-400">Total Inventory: 12 Cylinders</span>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // 1. Hydrogen Chart (Bar Horizontal sederhana untuk Pressure)
    const ctxH2 = document.getElementById('hydrogenChart').getContext('2d');
    new Chart(ctxH2, {
        type: 'bar',
        data: {
            labels: ['Torpedo 04'],
            datasets: [{
                label: 'Pressure (Bar)',
                data: [140],
                backgroundColor: 'rgba(220, 38, 38, 0.7)', // Red
                borderColor: 'rgba(220, 38, 38, 1)',
                borderWidth: 1,
                barThickness: 30
            }]
        },
        options: {
            indexAxis: 'y', // Horizontal Bar
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { 
                    max: 200, // Asumsi Max Pressure Torpedo
                    grid: { color: '#f1f5f9' },
                    ticks: { font: {size: 10} }
                }, 
                y: { grid: { display: false } }
            }
        }
    });

    // 2. Ammonia Chart (Doughnut: Full vs Empty)
    const ctxNH3 = document.getElementById('ammoniaChart').getContext('2d');
    new Chart(ctxNH3, {
        type: 'doughnut',
        data: {
            labels: ['Full', 'Empty'],
            datasets: [{
                data: [7, 5],
                backgroundColor: [
                    '#10b981', // Emerald 500 (Full)
                    '#e2e8f0'  // Slate 200 (Empty)
                ],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%',
            plugins: {
                legend: { 
                    position: 'right',
                    labels: { usePointStyle: true, boxWidth: 8, font: {size: 11} }
                }
            }
        }
    });
</script>