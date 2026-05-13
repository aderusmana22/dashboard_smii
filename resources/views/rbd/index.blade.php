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

        /* CONTAINER UTAMA (SUDAH DIPERBAIKI - TANPA SPACE KOSONG BAWAH) */
        .dashboard-container {
            font-family: "Inter", sans-serif;
            color: #1e293b;
            display: flex;
            flex-direction: column;
            padding: 12px;
            gap: 12px; 
        }

        .mono { font-family: "JetBrains Mono", monospace; }

        /* COMMAND BAR */
        .command-bar {
            background: #ffffff;
            padding: 8px 16px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border: 1px solid var(--slate-border);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
            flex-shrink: 0;
        }

        .filter-item { display: flex; flex-direction: column; gap: 2px; }
        .filter-label { font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.025em; }
        .filter-input-clean {
            background: #f1f5f9;
            border: 1px solid transparent;
            border-radius: 6px;
            padding: 4px 8px;
            font-weight: 600;
            color: #334155;
            transition: all 0.2s;
        }
        .filter-input-clean:focus { background: #ffffff; border-color: var(--brand-amber); outline: none; box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.1); }

        /* =========================================
           TANGKI DINAMIS & REALISTIS
           ========================================= */
        #tank-container {
            display: grid;
            grid-template-columns: repeat(13, minmax(0, 1fr));
            gap: 1.5vh 0.5vw;
            justify-items: center;
            align-items: end;
            width: 100%;
        }

        .tank-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* TANGKI DIKUNCI MENTOK 14VW DENGAN RASIO PATEN */
        .tank-graphic {
            height: 14vw; 
            max-height: 180px; 
            aspect-ratio: 85 / 160; 
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .tank-roof { width: 100%; height: 5%; background: linear-gradient(to right, #94a3b8, #f1f5f9, #94a3b8); border-radius: 50% 50% 0 0; z-index: 5; border: 2px solid #64748b; border-bottom: none; }
        
        .tank-body { 
            position: relative; 
            width: 100%; 
            flex: 1; 
            background: #ffffff; 
            border: 3px solid #475569; 
            border-top: none; 
            overflow: hidden; 
            display: flex; 
            flex-direction: column-reverse; 
        }

        .tank-base { width: 120%; height: 4%; background: #334155; border-radius: 3px; }

        /* =========================================
           EFEK CAIRAN BERGELOMBANG
           ========================================= */
        .liquid-layer {
            position: relative;
            width: 100%;
            transition: height 1s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden; 
        }

        .liquid-surface {
            position: absolute;
            top: 0; left: 0; width: 200%; height: 20px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 20'%3E%3Cpath d='M0 10 Q 25 0, 50 10 T 100 10 L 100 20 L 0 20 Z' fill='rgba(255,255,255,0.3)'/%3E%3C/svg%3E");
            background-size: 50% 100%;
            animation: wave-slide 2.5s infinite linear;
            z-index: 10;
        }

        .liquid-surface-shadow {
            position: absolute;
            top: 0; left: -100%; width: 200%; height: 20px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 20'%3E%3Cpath d='M0 10 Q 25 20, 50 10 T 100 10 L 100 0 L 0 0 Z' fill='rgba(0,0,0,0.1)'/%3E%3C/svg%3E");
            background-size: 50% 100%;
            animation: wave-slide-left 3.5s infinite linear;
            z-index: 9;
        }

        @keyframes wave-slide { 100% { transform: translateX(-50%); } }
        @keyframes wave-slide-left { 100% { transform: translateX(50%); } }

        .bubble {
            position: absolute;
            bottom: -10px;
            background: rgba(255, 255, 255, 0.4);
            border-radius: 50%;
            animation: rise 2s infinite ease-in;
            z-index: 5;
        }
        @keyframes rise {
            0% { transform: translateY(0) scale(0.5); opacity: 0; }
            50% { opacity: 1; }
            100% { transform: translateY(-40px) scale(1.5); opacity: 0; }
        }

        /* TABLE & CARDS */
        .section-card { background: white; border-radius: 10px; border: 1px solid var(--slate-border); overflow: hidden; display: flex; flex-direction: column; }
        .custom-table { width: 100%; border-collapse: collapse; white-space: nowrap; }
        .custom-table th { background: #f8fafc; color: #64748b; text-align: left; padding: 6px 8px; border-bottom: 2px solid #f1f5f9; font-weight: 800; position: sticky; top: 0; z-index: 10; }
        .custom-table td { padding: 4px 8px; border-bottom: 1px solid #f1f5f9; }
        .custom-table tr:hover { background-color: #fcfcfd; }

        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>

    <div class="dashboard-container">
        <!-- HEADER & FILTER -->
        <form id="filter-form" action="{{ route('rbd.dashboard') }}" method="GET" class="command-bar">
            <div class="flex items-center gap-3">
                <div class="bg-amber-100 p-2 rounded-lg">
                    <i class="mdi mdi-database-cog text-amber-600 text-xl"></i>
                </div>
                <div>
                    <h1 class="text-base font-black text-slate-800 uppercase tracking-tight leading-none">Silo Intelligence</h1>
                    <div class="flex items-center gap-1.5 mt-1">
                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                        <span class="text-xs font-bold text-slate-400 uppercase">Live Mode</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="filter-item">
                    <label class="filter-label text-xs">Period</label>
                    <div class="flex gap-2">
                        <select name="year" class="filter-input-clean text-sm min-w-[70px]">
                            @php $currYear = date('Y'); @endphp
                            @for ($y = $currYear - 3; $y <= $currYear + 1; $y++)
                                <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                        <select name="month" class="filter-input-clean text-sm min-w-[90px]">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="h-6 w-[1px] bg-slate-200"></div>

                <div class="filter-item">
                    <label class="filter-label text-xs">Search</label>
                    <div class="flex gap-2">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="filter-input-clean text-sm w-80">
                    </div>
                </div>

                <button type="submit" id="btn-apply" class="bg-slate-500 mt-3 text-white px-4 py-1.5 rounded-lg text-sm font-black uppercase hover:bg-slate-600 transition flex items-center gap-2 shadow-sm">
                    <i class="mdi mdi-filter" id="btn-icon"></i> <span id="btn-text">Apply</span>
                </button>
            </div>

            <div class="flex items-center gap-4 pl-4 border-l border-slate-100">
                <div class="text-center bg-slate-50 px-3 py-1.5 rounded-lg">
                    <p id="sync-time" class="text-amber-600 text-xl font-bold mono leading-none">00:00:00</p>
                    <p class="text-xs text-slate-400 font-bold uppercase mt-0.5">System Time</p>
                </div>
            </div>
        </form>

        <!-- ROW 1: TANGKI DINAMIS (Dikunci) -->
        <div class="section-card p-2 flex flex-col flex-shrink-0">
            <h2 class="text-sm font-black text-slate-500 uppercase mb-2 flex items-center gap-2 flex-shrink-0">
                <span class="w-1 h-2.5 bg-amber-500 rounded-full"></span> Real-Time Silo Stock
            </h2>
            
            <div id="tank-container">
                @forelse ($tanks as $tank)
                    @php 
                        $percent = $tank['capacity'] > 0 ? ($tank['total_qty'] / $tank['capacity']) * 100 : 0;
                    @endphp
                    <div class="tank-wrapper" title="Items: {{ $tank['item_list'] ?? 'Empty' }} | Volume: {{ number_format($tank['total_qty'], 2) }}">
                        <div class="text-md font-black text-slate-700 mono mb-1 whitespace-nowrap">{{ number_format($percent, 1) }}%</div>
                        
                        <!-- WADAH TANGKI -->
                        <div class="tank-graphic">
                            <div class="tank-roof"></div>
                            <div class="tank-body">
                                @foreach($tank['items'] as $item)
                                    @php $h = ($item['qty'] / $tank['capacity']) * 100; @endphp
                                    <div class="liquid-layer" style="height: {{ $h }}%; background-color: {{ $item['color'] }};" title="{{ $item['part'] }}: {{ number_format($item['qty'], 2) }}">
                                        <!-- Gelombang CSS Realistis -->
                                        <div class="liquid-surface" style="animation-duration: {{ rand(20, 35)/10 }}s;"></div>
                                        <div class="liquid-surface-shadow" style="animation-duration: {{ rand(30, 45)/10 }}s;"></div>
                                        
                                        <div class="bubble" style="left: 20%; animation-delay: 0.1s;"></div>
                                        <div class="bubble" style="left: 60%; animation-delay: 0.5s; width: 4px; height: 4px;"></div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="tank-base"></div>
                        </div>

                        <div class="text-md font-bold text-slate-600 mt-1.5 truncate w-full text-center uppercase tracking-wider">{{ $tank['tank_name'] }}</div>
                    </div>
                @empty
                    <div class="text-sm font-bold text-slate-400 w-full text-center my-auto" style="grid-column: span 13;">No Tanks Data Configured.</div>
                @endforelse
            </div>

            <!-- TANK LEGEND -->
            @php
                $legendItems =[];
                foreach($tanks as $tank) {
                    if(isset($tank['items'])) {
                        foreach($tank['items'] as $item) { $legendItems[$item['part']] = $item['color']; }
                    }
                }
            @endphp
            <div id="tank-legend" class="flex flex-wrap justify-center gap-x-4 gap-y-1 mt-2 pt-2 border-t border-slate-100 flex-shrink-0">
                @forelse($legendItems as $part => $color)
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full shadow-sm" style="background-color: {{ $color }}"></span>
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">{{ $part }}</span>
                    </div>
                @empty
                    <div class="text-xs font-bold text-slate-400 uppercase">Awaiting Stock...</div>
                @endforelse
            </div>
        </div>

        <!-- ROW 2: GRAFIK & TABEL (Dikunci Tinggi 210px agar pas dengan Tabel 5 Baris) -->
        <div class="grid grid-cols-12 gap-3 flex-shrink-0">
            
            <div class="col-span-3 section-card p-2 flex flex-col h-[210px]">
                <h2 class="text-sm font-black text-slate-500 uppercase mb-1 flex items-center gap-2 flex-shrink-0">
                    <span class="w-1 h-2.5 bg-emerald-500 rounded-full"></span> Incoming (IN) Ranking
                </h2>
                <div class="w-full relative flex-1 min-h-0">
                    <canvas id="incomingChart"></canvas>
                </div>
            </div>

            <div class="col-span-3 section-card flex flex-col h-[210px]">
                <div class="p-2 border-b flex justify-between items-center bg-white sticky top-0 z-20 flex-shrink-0">
                    <h2 class="text-sm font-black text-slate-800 uppercase tracking-widest">Incoming Feed (Top 5)</h2>
                </div>
                <div class="overflow-y-auto flex-1 p-0.5">
                    <table class="custom-table text-xs">
                        <thead><tr><th>Supplier</th><th>Item</th><th>Qty(KG)</th></tr></thead>
                        <tbody id="tbody-incoming">
                            @foreach ($incomingTable->take(5) as $row)
                                <tr>
                                    <td class="font-semibold" title="{{ $row->tr_addr_name ?? $row->tr_addr ?? 'Unknown' }}">
                                        {{ Str::limit($row->tr_addr_name ?? $row->tr_addr ?? 'Unknown', 12) }}
                                    </td>
                                    <td class="mono text-slate-500" title="{{ $row->tr_part }}">{{ Str::limit($row->tr_part, 8) }}</td>
                                    <td class="font-bold text-emerald-600">{{ $row->qty_formatted }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-span-3 section-card flex flex-col h-[210px]">
                <div class="p-2 border-b flex justify-between items-center bg-white sticky top-0 z-20 flex-shrink-0">
                    <h2 class="text-sm font-black text-slate-800 uppercase tracking-widest">Outgoing Feed (Top 5)</h2>
                </div>
                <div class="overflow-y-auto flex-1 p-0.5">
                    <table class="custom-table text-xs">
                        <thead><tr><th>Item</th><th>Desc</th><th>Qty(KG)</th></tr></thead>
                        <tbody id="tbody-outgoing">
                            @foreach ($outgoingTable->take(5) as $row)
                                <tr>
                                    <td class="mono font-bold text-blue-600" title="{{ $row->tr_part }}">{{ Str::limit($row->tr_part, 8) }}</td>
                                    <td class="font-semibold" title="{{ $row->tr_part_name ?? 'Internal Dispatch' }}">
                                        {{ Str::limit($row->tr_part_name ?? 'Internal Dispatch', 12) }}
                                    </td>
                                    <td class="font-bold text-orange-600">{{ $row->qty_formatted }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-span-3 section-card p-2 flex flex-col h-[210px]">
                <h2 class="text-sm font-black text-slate-500 uppercase mb-1 flex items-center gap-2 flex-shrink-0">
                    <span class="w-1 h-2.5 bg-orange-500 rounded-full"></span> Outgoing (OUT) Dispatch
                </h2>
                <div class="w-full relative flex-1 min-h-0">
                    <canvas id="outgoingChart"></canvas>
                </div>
            </div>

        </div>
    </div>

    <script>
        setInterval(() => {
            document.getElementById('sync-time').textContent = new Date().toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }, 1000);

        // FONT CHART.JS JUGA SUDAH DIPERBESAR MENJADI SIZE: 10
        const commonOptions = {
            indexAxis: 'y', responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, usePointStyle: true, font: { size: 10, family: 'Inter' } } }, tooltip: { mode: 'index', intersect: false } },
            scales: {
                x: { stacked: true, grid: { color: '#f1f5f9' }, ticks: { font: { size: 10 }, color: '#94a3b8' } },
                y: { stacked: true, grid: { display: false }, ticks: { font: { size: 10, weight: 'bold' }, color: '#475569' } }
            }
        };

        let inChart = new Chart(document.getElementById('incomingChart').getContext('2d'), {
            type: 'bar', data: { labels: {!! json_encode($inLabels) !!}, datasets: {!! json_encode($inDatasets) !!} }, options: commonOptions
        });

        let outOpt = JSON.parse(JSON.stringify(commonOptions)); outOpt.plugins.legend.display = false;
        let outChart = new Chart(document.getElementById('outgoingChart').getContext('2d'), {
            type: 'bar', data: { labels: {!! json_encode($outLabels) !!}, datasets:[{ label: 'Dispatched Qty', data: {!! json_encode($outValues) !!}, backgroundColor: '#f97316', borderRadius: 4 }] }, options: outOpt
        });

        function limitStr(str, limit) { return (!str) ? '' : (str.length > limit ? str.substring(0, limit) + '...' : str); }

        document.getElementById('filter-form').addEventListener('submit', function(e) {
            e.preventDefault();
            let btnIcon = document.getElementById('btn-icon');
            let btnText = document.getElementById('btn-text');
            btnIcon.className = "mdi mdi-loading mdi-spin"; btnText.textContent = "Loading...";

            fetch(new URL(this.action).pathname + '?' + new URLSearchParams(new FormData(this)).toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(response => response.json())
            .then(data => {
                inChart.data.labels = data.inLabels; inChart.data.datasets = data.inDatasets; inChart.update();
                outChart.data.labels = data.outLabels; outChart.data.datasets[0].data = data.outValues; outChart.update();

                let tankHtml = ''; let newLegendItems = {};
                if(data.tanks.length === 0) {
                    tankHtml = '<div class="text-sm font-bold text-slate-400 w-full text-center my-auto" style="grid-column: span 13;">No Tanks Data Configured.</div>';
                } else {
                    data.tanks.forEach(tank => {
                        let percent = tank.capacity > 0 ? (tank.total_qty / tank.capacity) * 100 : 0;
                        let liquidHtml = '';
                        tank.items.forEach(item => {
                            let h = (item.qty / tank.capacity) * 100;
                            let ws1 = (Math.random() * 1.5 + 2).toFixed(1); 
                            let ws2 = (Math.random() * 1.5 + 3).toFixed(1); 
                            
                            liquidHtml += `
                                <div class="liquid-layer" style="height: ${h}%; background-color: ${item.color};" title="${item.part}: ${parseFloat(item.qty).toFixed(2)}">
                                    <div class="liquid-surface" style="animation-duration: ${ws1}s;"></div>
                                    <div class="liquid-surface-shadow" style="animation-duration: ${ws2}s;"></div>
                                    <div class="bubble" style="left: 20%; animation-delay: 0.1s;"></div>
                                    <div class="bubble" style="left: 60%; animation-delay: 0.5s; width: 4px; height: 4px;"></div>
                                </div>`;
                            newLegendItems[item.part] = item.color;
                        });

                        tankHtml += `
                        <div class="tank-wrapper" title="Items: ${tank.item_list} | Volume: ${parseFloat(tank.total_qty).toFixed(2)}">
                            <div class="text-sm font-black text-slate-700 mono mb-1 whitespace-nowrap">${percent.toFixed(1)}%</div>
                            <div class="tank-graphic">
                                <div class="tank-roof"></div>
                                <div class="tank-body">${liquidHtml}</div>
                                <div class="tank-base"></div>
                            </div>
                            <div class="text-xs font-bold text-slate-600 mt-1.5 truncate w-full text-center uppercase tracking-wider">${tank.tank_name}</div>
                        </div>`;
                    });
                }
                document.getElementById('tank-container').innerHTML = tankHtml;

                let legendHtml = '';
                if(Object.keys(newLegendItems).length === 0) {
                    legendHtml = '<div class="text-xs font-bold text-slate-400 uppercase">Awaiting Stock...</div>';
                } else {
                    for (const [part, color] of Object.entries(newLegendItems)) {
                        legendHtml += `<div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full shadow-sm" style="background-color: ${color}"></span><span class="text-xs font-bold text-slate-500 uppercase tracking-wide">${part}</span></div>`;
                    }
                }
                document.getElementById('tank-legend').innerHTML = legendHtml;

                let inTb = ''; let incomingSlice = data.incomingTable.slice(0, 5);
                if(incomingSlice.length === 0) { inTb = '<tr><td colspan="3" class="text-center py-4 text-slate-400">No incoming data.</td></tr>'; }
                incomingSlice.forEach(row => {
                    let supplierName = row.tr_addr_name || row.tr_addr || 'Unknown';
                    inTb += `<tr><td class="font-semibold" title="${supplierName}">${limitStr(supplierName, 12)}</td><td class="mono text-slate-500" title="${row.tr_part}">${limitStr(row.tr_part, 8)}</td><td class="font-bold text-emerald-600">${row.qty_formatted}</td></tr>`;
                });
                document.getElementById('tbody-incoming').innerHTML = inTb;

                let outTb = ''; let outgoingSlice = data.outgoingTable.slice(0, 5);
                if(outgoingSlice.length === 0) { outTb = '<tr><td colspan="3" class="text-center py-4 text-slate-400">No outgoing data.</td></tr>'; }
                outgoingSlice.forEach(row => {
                    let descName = row.tr_part_name || 'Internal Dispatch';
                    outTb += `<tr><td class="mono font-bold text-blue-600" title="${row.tr_part}">${limitStr(row.tr_part, 8)}</td><td class="font-semibold" title="${descName}">${limitStr(descName, 12)}</td><td class="font-bold text-orange-600">${row.qty_formatted}</td></tr>`;
                });
                document.getElementById('tbody-outgoing').innerHTML = outTb;

                btnIcon.className = "mdi mdi-filter"; btnText.textContent = "Apply";
            })
            .catch(error => {
                console.error('Error:', error); btnIcon.className = "mdi mdi-alert-circle"; btnText.textContent = "Error";
                setTimeout(() => { btnIcon.className = "mdi mdi-filter"; btnText.textContent = "Apply"; }, 2000);
            });
        });
    </script>
</x-app-layout>