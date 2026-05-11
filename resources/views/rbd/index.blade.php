<x-app-layout>
    <!-- Resource Imports -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        @import url("https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;700&family=Inter:wght@400;600;800;900&display=swap");

        :root {
            --brand-amber: #f59e0b;
            --slate-border: #e2e8f0;
        }

        .dashboard-container {
            font-family: "Inter", sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            min-height: calc(100vh - 64px);
            display: flex;
            flex-direction: column;
            padding: 16px;
            gap: 16px;
        }

        .mono { font-family: "JetBrains Mono", monospace; }

        /* COMMAND BAR */
        .command-bar {
            background: #ffffff;
            padding: 12px 20px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border: 1px solid var(--slate-border);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        }

        .filter-item { display: flex; flex-direction: column; gap: 2px; }
        .filter-label { font-size: 9px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.025em; }
        .filter-input-clean {
            background: #f1f5f9;
            border: 1px solid transparent;
            border-radius: 6px;
            padding: 5px 10px;
            font-size: 11px;
            font-weight: 600;
            color: #334155;
            transition: all 0.2s;
        }
        .filter-input-clean:focus { background: #ffffff; border-color: var(--brand-amber); outline: none; box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.1); }

        /* TANGKI DINAMIS MULTI-LAYER */
        .tank-wrapper {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1; 
            min-width: 60px; 
            max-width: 80px; 
        }

        .tank-roof { width: 100%; max-width: 54px; height: 10px; background: linear-gradient(to right, #94a3b8, #f1f5f9, #94a3b8); border-radius: 50% 50% 0 0; z-index: 5; border: 2px solid #64748b; }
        
        .tank-body { 
            position: relative; 
            width: 100%; 
            max-width: 50px; 
            height: 80px; 
            background: #ffffff; 
            border: 3px solid #475569; 
            border-top: none; 
            overflow: hidden; 
            display: flex; 
            flex-direction: column-reverse; /* Cairan menumpuk dari bawah */
        }

        .tank-base { width: 110%; max-width: 58px; height: 5px; background: #334155; border-radius: 2px; }

        /* EFEK CAIRAN BERGELOMBANG (WAVY FLUID) */
        .liquid-layer {
            position: relative;
            width: 100%;
            transition: height 1s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden; /* Penting agar gelombang tidak bocor keluar */
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        /* Elemen Gelombang di permukaan cairan */
        .liquid-surface {
            position: absolute;
            top: -35px; /* Mengatur kedalaman ombak */
            left: -50%;
            width: 200%;
            height: 40px;
            border-radius: 40%; /* Kunci animasi air */
            animation: wave-spin 3s infinite linear;
            filter: brightness(1.15); /* Puncak gelombang terlihat lebih cerah */
            z-index: 10;
        }

        @keyframes wave-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Animasi Gelembung Naik */
        .bubble {
            position: absolute;
            bottom: -10px;
            background: rgba(255, 255, 255, 0.4);
            border-radius: 50%;
            animation: rise 2s infinite ease-in;
            z-index: 11;
        }
        @keyframes rise {
            0% { transform: translateY(0) scale(0.5); opacity: 0; }
            50% { opacity: 1; }
            100% { transform: translateY(-30px) scale(1.2); opacity: 0; }
        }

        /* TABLE & CARDS */
        .section-card { background: white; border-radius: 12px; border: 1px solid var(--slate-border); overflow: hidden; display: flex; flex-direction: column; }
        .scroll-container { max-height: 380px; overflow-y: auto; }
        
        .custom-table { width: 100%; font-size: 11px; border-collapse: collapse; }
        .custom-table th { background: #f8fafc; color: #64748b; text-align: left; padding: 10px 12px; border-bottom: 2px solid #f1f5f9; font-weight: 800; position: sticky; top: 0; z-index: 10; }
        .custom-table td { padding: 8px 12px; border-bottom: 1px solid #f1f5f9; }
        .custom-table tr:hover { background-color: #fcfcfd; }

        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>

    <div class="dashboard-container">
        <!-- HEADER & FILTER -->
        <form id="filter-form" action="{{ route('rbd.dashboard') }}" method="GET" class="command-bar">
            <div class="flex items-center gap-4">
                <div class="bg-amber-100 p-2.5 rounded-xl">
                    <i class="mdi mdi-database-cog text-amber-600 text-xl"></i>
                </div>
                <div>
                    <h1 class="text-sm font-black text-slate-800 uppercase tracking-tight leading-none">Silo Intelligence</h1>
                    <div class="flex items-center gap-1.5 mt-1">
                        <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Operational Live Mode</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-6">
                <div class="filter-item">
                    <label class="filter-label">Period</label>
                    <div class="flex gap-2">
                        <!-- Perbaikan Bug Filter Tahun -->
                        <select name="year" class="filter-input-clean min-w-[80px]">
                            @php $currYear = date('Y'); @endphp
                            @for ($y = $currYear - 3; $y <= $currYear + 1; $y++)
                                <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                        <select name="month" class="filter-input-clean min-w-[100px]">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="h-8 w-[1px] bg-slate-100"></div>

                <div class="filter-item">
                    <label class="filter-label">Search</label>
                    <div class="flex gap-2">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Part/Item..." class="filter-input-clean w-48">
                    </div>
                </div>

                <!-- Perbaikan Warna Tombol Abu-abu -->
                <button type="submit" id="btn-apply" class="bg-slate-500 mt-5m text-white px-5 py-2 rounded-lg text-[11px] font-black uppercase hover:bg-slate-600 transition flex items-center gap-2 shadow-sm">
                    <i class="mdi mdi-filter" id="btn-icon"></i> <span id="btn-text">Apply</span>
                </button>
            </div>

            <div class="flex items-center gap-8 pl-6 border-l border-slate-100">
                <div class="text-center bg-slate-50 px-4 py-2 rounded-lg">
                    <p id="sync-time" class="text-amber-600 text-lg font-bold mono leading-none">00:00:00</p>
                    <p class="text-[8px] text-slate-400 font-bold uppercase mt-1">System Time</p>
                </div>
            </div>
        </form>

        <!-- MIDDLE SECTION -->
        <div class="grid grid-cols-12 gap-4 flex-shrink-0" style="min-height: 280px;">
            
            <!-- LEFT: INCOMING CHART -->
            <div class="col-span-3 section-card p-4">
                <h2 class="text-[10px] font-black text-slate-500 uppercase mb-4 flex items-center gap-2">
                    <span class="w-1 h-3 bg-emerald-500 rounded-full"></span> Incoming (IN) Ranking
                </h2>
                <div class="flex-1 relative h-[220px]">
                    <canvas id="incomingChart"></canvas>
                </div>
            </div>

            <!-- CENTER: DYNAMIC TANKS & LEGEND -->
            <div class="col-span-6 section-card p-4 flex flex-col">
                <h2 class="text-[10px] font-black text-slate-500 uppercase mb-6 flex items-center gap-2">
                    <span class="w-1 h-3 bg-amber-500 rounded-full"></span> Real-Time Silo Stock
                </h2>
                
                <div id="tank-container" class="flex flex-wrap justify-center items-end flex-1 pb-2 px-2 gap-y-6 gap-x-3 overflow-y-auto max-h-[180px]">
                    @forelse ($tanks as $tank)
                        @php 
                            $percent = $tank['capacity'] > 0 ? ($tank['total_qty'] / $tank['capacity']) * 100 : 0;
                            $cappedPercent = $percent > 100 ? 100 : $percent;
                        @endphp
                        <div class="tank-wrapper" title="Items: {{ $tank['item_list'] ?? 'Empty' }} | Volume: {{ number_format($tank['total_qty'], 2) }}">
                            <div class="text-[9px] font-black text-slate-600 mono mb-1">{{ number_format($percent, 1) }}%</div>
                            <div class="tank-roof"></div>
                            <div class="tank-body">
                                <!-- Multi-Layer Liquid Loop dengan Animasi Bergelombang -->
                                @foreach($tank['items'] as $item)
                                    @php 
                                        $h = ($item['qty'] / $tank['capacity']) * 100; 
                                    @endphp
                                    <div class="liquid-layer" style="height: {{ $h }}%; background-color: {{ $item['color'] }};" title="{{ $item['part'] }}: {{ number_format($item['qty'], 2) }}">
                                        <!-- Animasi Permukaan Gelombang -->
                                        <div class="liquid-surface" style="background-color: {{ $item['color'] }}; animation-duration: {{ rand(30, 50)/10 }}s;"></div>
                                        <!-- Gelembung Air -->
                                        <div class="bubble" style="left: 20%; animation-delay: 0.1s;"></div>
                                        <div class="bubble" style="left: 60%; animation-delay: 0.5s; width: 6px; height: 6px;"></div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="tank-base"></div>
                            <div class="text-[8px] font-bold text-slate-500 mt-2 truncate w-full text-center">{{ $tank['tank_name'] }}</div>
                        </div>
                    @empty
                        <div class="text-xs font-bold text-slate-400 w-full text-center my-auto">No Tanks Data Configured.</div>
                    @endforelse
                </div>

                <!-- TANK LEGEND (Keterangan Warna) -->
                @php
                    $legendItems =[];
                    foreach($tanks as $tank) {
                        if(isset($tank['items'])) {
                            foreach($tank['items'] as $item) {
                                $legendItems[$item['part']] = $item['color'];
                            }
                        }
                    }
                @endphp
                <div id="tank-legend" class="flex flex-wrap justify-center gap-x-5 gap-y-2 mt-4 pt-4 border-t border-slate-100">
                    @forelse($legendItems as $part => $color)
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-full shadow-sm" style="background-color: {{ $color }}"></span>
                            <span class="text-[9px] font-bold text-slate-500 uppercase tracking-wide">{{ $part }}</span>
                        </div>
                    @empty
                        <div class="text-[9px] font-bold text-slate-400 uppercase">Awaiting Stock...</div>
                    @endforelse
                </div>
            </div>

            <!-- RIGHT: OUTGOING CHART -->
            <div class="col-span-3 section-card p-4">
                <h2 class="text-[10px] font-black text-slate-500 uppercase mb-4 flex items-center gap-2">
                    <span class="w-1 h-3 bg-orange-500 rounded-full"></span> Outgoing (OUT) Dispatch
                </h2>
                <div class="flex-1 relative h-[220px]">
                    <canvas id="outgoingChart"></canvas>
                </div>
            </div>
        </div>

        <!-- BOTTOM SECTION: TABLES -->
        <div class="grid grid-cols-2 gap-4">
            <div class="section-card">
                <div class="p-3 border-b flex justify-between items-center bg-white sticky top-0 z-20">
                    <h2 class="text-[10px] font-black text-slate-800 uppercase tracking-widest">Incoming Feed (IN)</h2>
                </div>
                <div class="scroll-container">
                    <table class="custom-table">
                        <thead><tr><th>Supplier</th><th>Item Code</th><th>Qty (KG)</th><th>Date/Time</th></tr></thead>
                        <tbody id="tbody-incoming">
                            @foreach ($incomingTable as $row)
                                <tr>
                                    <td class="font-semibold">{{ $row->tr_addr_name ?? $row->tr_addr ?? 'Unknown' }}</td>
                                    <td class="mono text-slate-500">{{ $row->tr_part }}</td>
                                    <td class="font-bold text-emerald-600">{{ $row->qty_formatted }}</td>
                                    <td class="text-slate-400">{{ $row->date_formatted }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="section-card">
                <div class="p-3 border-b flex justify-between items-center bg-white sticky top-0 z-20">
                    <h2 class="text-[10px] font-black text-slate-800 uppercase tracking-widest">Outgoing Feed (OUT)</h2>
                </div>
                <div class="scroll-container">
                    <table class="custom-table">
                        <thead><tr><th>Item Code</th><th>Item Description</th><th>Quantity (KG)</th><th>Status</th></tr></thead>
                        <tbody id="tbody-outgoing">
                            @foreach ($outgoingTable as $row)
                                <tr>
                                    <td class="mono font-bold text-blue-600">{{ $row->tr_part }}</td>
                                    <td class="font-semibold">{{ $row->tr_part_name ?? 'Internal Dispatch' }}</td>
                                    <td class="font-bold text-orange-600">{{ $row->qty_formatted }}</td>
                                    <td><span class="text-[9px] font-black uppercase text-slate-400 bg-slate-100 px-2 py-1 rounded">Dispatched</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // 1. Clock Sync
        setInterval(() => {
            document.getElementById('sync-time').textContent = new Date().toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }, 1000);

        // 2. Setup Chart.js Objects
        const commonOptions = {
            indexAxis: 'y', responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, usePointStyle: true, font: { size: 9, family: 'Inter' } } }, tooltip: { mode: 'index', intersect: false } },
            scales: {
                x: { stacked: true, grid: { color: '#f1f5f9' }, ticks: { font: { size: 9 }, color: '#94a3b8' } },
                y: { stacked: true, grid: { display: false }, ticks: { font: { size: 9, weight: 'bold' }, color: '#475569' } }
            }
        };

        let inChart = new Chart(document.getElementById('incomingChart').getContext('2d'), {
            type: 'bar',
            data: { labels: {!! json_encode($inLabels) !!}, datasets: {!! json_encode($inDatasets) !!} },
            options: commonOptions
        });

        let outOpt = JSON.parse(JSON.stringify(commonOptions)); outOpt.plugins.legend.display = false;
        let outChart = new Chart(document.getElementById('outgoingChart').getContext('2d'), {
            type: 'bar',
            data: { labels: {!! json_encode($outLabels) !!}, datasets:[{ label: 'Dispatched Qty', data: {!! json_encode($outValues) !!}, backgroundColor: '#f97316', borderRadius: 4 }] },
            options: outOpt
        });

        // 3. AJAX FILTERING LOGIC (TANPA RELOAD)
        document.getElementById('filter-form').addEventListener('submit', function(e) {
            e.preventDefault();

            let btnIcon = document.getElementById('btn-icon');
            let btnText = document.getElementById('btn-text');
            
            btnIcon.className = "mdi mdi-loading mdi-spin";
            btnText.textContent = "Loading...";

            const url = new URL(this.action);
            const formData = new FormData(this);
            const params = new URLSearchParams(formData);

            fetch(url.pathname + '?' + params.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                // Update Chart Kiri
                inChart.data.labels = data.inLabels;
                inChart.data.datasets = data.inDatasets;
                inChart.update();

                // Update Chart Kanan
                outChart.data.labels = data.outLabels;
                outChart.data.datasets[0].data = data.outValues;
                outChart.update();

                // Update Tangki Dinamis & Kumpulkan Data Legend
                let tankHtml = '';
                let newLegendItems = {};

                if(data.tanks.length === 0) {
                    tankHtml = '<div class="text-xs font-bold text-slate-400 w-full text-center my-auto">No Tanks Data Configured.</div>';
                } else {
                    data.tanks.forEach(tank => {
                        let percent = tank.capacity > 0 ? (tank.total_qty / tank.capacity) * 100 : 0;
                        let cappedPercent = percent > 100 ? 100 : percent;
                        
                        let liquidHtml = '';
                        tank.items.forEach(item => {
                            let h = (item.qty / tank.capacity) * 100;
                            // Random durasi ombak agar antar minyak terlihat tidak seragam persis
                            let waveSpeed = (Math.random() * 2 + 3).toFixed(1); 
                            
                            liquidHtml += `
                                <div class="liquid-layer" style="height: ${h}%; background-color: ${item.color};" title="${item.part}: ${parseFloat(item.qty).toFixed(2)}">
                                    <div class="liquid-surface" style="background-color: ${item.color}; animation-duration: ${waveSpeed}s;"></div>
                                    <div class="bubble" style="left: 20%; animation-delay: 0.1s;"></div>
                                    <div class="bubble" style="left: 60%; animation-delay: 0.5s; width: 6px; height: 6px;"></div>
                                </div>`;
                                
                            // Rekam data legend
                            newLegendItems[item.part] = item.color;
                        });

                        tankHtml += `
                        <div class="tank-wrapper" title="Items: ${tank.item_list} | Volume: ${parseFloat(tank.total_qty).toFixed(2)}">
                            <div class="text-[9px] font-black text-slate-600 mono mb-1">${percent.toFixed(1)}%</div>
                            <div class="tank-roof"></div>
                            <div class="tank-body">${liquidHtml}</div>
                            <div class="tank-base"></div>
                            <div class="text-[8px] font-bold text-slate-500 mt-2 truncate w-full text-center">${tank.tank_name}</div>
                        </div>`;
                    });
                }
                document.getElementById('tank-container').innerHTML = tankHtml;

                // Update Legend
                let legendHtml = '';
                if(Object.keys(newLegendItems).length === 0) {
                    legendHtml = '<div class="text-[9px] font-bold text-slate-400 uppercase">Awaiting Stock...</div>';
                } else {
                    for (const [part, color] of Object.entries(newLegendItems)) {
                        legendHtml += `
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-full shadow-sm" style="background-color: ${color}"></span>
                                <span class="text-[9px] font-bold text-slate-500 uppercase tracking-wide">${part}</span>
                            </div>`;
                    }
                }
                document.getElementById('tank-legend').innerHTML = legendHtml;

                // Update Tabel Kiri
                let inTb = '';
                if(data.incomingTable.length === 0) { inTb = '<tr><td colspan="4" class="text-center py-4 text-slate-400">No incoming data.</td></tr>'; }
                data.incomingTable.forEach(row => {
                    inTb += `<tr>
                        <td class="font-semibold">${row.tr_addr_name || row.tr_addr || 'Unknown'}</td>
                        <td class="mono text-slate-500">${row.tr_part}</td>
                        <td class="font-bold text-emerald-600">${row.qty_formatted}</td>
                        <td class="text-slate-400">${row.date_formatted}</td>
                    </tr>`;
                });
                document.getElementById('tbody-incoming').innerHTML = inTb;

                // Update Tabel Kanan
                let outTb = '';
                if(data.outgoingTable.length === 0) { outTb = '<tr><td colspan="4" class="text-center py-4 text-slate-400">No outgoing data.</td></tr>'; }
                data.outgoingTable.forEach(row => {
                    outTb += `<tr>
                        <td class="mono font-bold text-blue-600">${row.tr_part}</td>
                        <td class="font-semibold">${row.tr_part_name || 'Internal Dispatch'}</td>
                        <td class="font-bold text-orange-600">${row.qty_formatted}</td>
                        <td><span class="text-[9px] font-black uppercase text-slate-400 bg-slate-100 px-2 py-1 rounded">Dispatched</span></td>
                    </tr>`;
                });
                document.getElementById('tbody-outgoing').innerHTML = outTb;

                // Restore Button State
                btnIcon.className = "mdi mdi-filter";
                btnText.textContent = "Apply";
            })
            .catch(error => {
                console.error('Error:', error);
                btnIcon.className = "mdi mdi-alert-circle";
                btnText.textContent = "Error";
                setTimeout(() => { btnIcon.className = "mdi mdi-filter"; btnText.textContent = "Apply"; }, 2000);
            });
        });
    </script>
</x-app-layout>