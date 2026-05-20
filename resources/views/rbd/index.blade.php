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

        body { overflow-x: hidden; }

        .dashboard-container {
            font-family: "Inter", sans-serif;
            color: #1e293b;
            display: flex;
            flex-direction: column;
            padding: 1.5vh 1.5vw;
            gap: 1vh; 
            min-height: calc(100vh - 65px); 
            height: calc(100vh - 65px); 
        }

        .mono { font-family: "JetBrains Mono", monospace; }

        .command-bar {
            background: #ffffff;
            padding: 1vh 1vw;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border: 1px solid var(--slate-border);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
            flex-shrink: 0;
        }

        .filter-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .filter-label {
            font-weight: 800;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            font-size: 0.7rem;
        }

        .filter-input-clean {
            /* UBAH ke background-color agar tidak menimpa panah bawaan browser/Tailwind */
            background-color: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 4px 8px;
            font-weight: 600;
            color: #334155;
            font-size: 0.8rem;
            outline: none;
            /* Pastikan background image (panah) tidak ikut ter-reset */
            background-repeat: no-repeat;
            background-position: right 0.5rem center;
            background-size: 1.5em 1.5em;
        }

        .filter-input-clean:focus {
            outline: none;
            border: 1px solid #e2e8f0;
            box-shadow: none;
            /* UBAH juga di sini ke background-color */
            background-color: #f1f5f9;
        }

        /* TANGKI CSS */
        .tank-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }

        .tank-graphic {
            height: 13vh; 
            min-height: 130px; 
            aspect-ratio: 85 / 160; 
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .tank-roof { width: 100%; height: 5%; background: linear-gradient(to right, #94a3b8, #f1f5f9, #94a3b8); border-radius: 50% 50% 0 0; z-index: 5; border: 2px solid #64748b; border-bottom: none; }
        .tank-body { position: relative; width: 100%; flex: 1; background: #ffffff; border: 3px solid #475569; border-top: none; overflow: hidden; display: flex; flex-direction: column-reverse; }
        .tank-base { width: 120%; height: 4%; background: #334155; border-radius: 3px; }

        .liquid-layer { position: relative; width: 100%; transition: height 1s cubic-bezier(0.4, 0, 0.2, 1); overflow: hidden; }
        .liquid-surface { position: absolute; top: 0; left: 0; width: 200%; height: 20px; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 20'%3E%3Cpath d='M0 10 Q 25 0, 50 10 T 100 10 L 100 20 L 0 20 Z' fill='rgba(255,255,255,0.3)'/%3E%3C/svg%3E"); background-size: 50% 100%; animation: wave-slide 2.5s infinite linear; z-index: 10; }
        .liquid-surface-shadow { position: absolute; top: 0; left: -100%; width: 200%; height: 20px; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 20'%3E%3Cpath d='M0 10 Q 25 20, 50 10 T 100 10 L 100 0 L 0 0 Z' fill='rgba(0,0,0,0.1)'/%3E%3C/svg%3E"); background-size: 50% 100%; animation: wave-slide-left 3.5s infinite linear; z-index: 9; }

        @keyframes wave-slide { 100% { transform: translateX(-50%); } }
        @keyframes wave-slide-left { 100% { transform: translateX(50%); } }

        .bubble { position: absolute; bottom: -10px; background: rgba(255, 255, 255, 0.4); border-radius: 50%; animation: rise 2s infinite ease-in; z-index: 5; }
        @keyframes rise { 0% { transform: translateY(0) scale(0.5); opacity: 0; } 50% { opacity: 1; } 100% { transform: translateY(-40px) scale(1.5); opacity: 0; } }

        /* TABLE & CARDS */
        .section-card { background: white; border-radius: 10px; border: 1px solid var(--slate-border); overflow: hidden; display: flex; flex-direction: column; }
        .custom-table { width: 100%; border-collapse: collapse; white-space: nowrap; }
        .custom-table th { background: #f8fafc; color: #64748b; text-align: center; border-bottom: 2px solid #f1f5f9; font-weight: 800; position: sticky; top: 0; z-index: 10; font-size: 10px; text-transform: uppercase; }
        .custom-table td { padding: 1vh 1vw; border-bottom: 1px solid #f1f5f9; }
        .custom-table tr:hover { background-color: #fcfcfd; }

        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

        /* CUSTOM TOOLTIP CHART.JS STYLE */
        #chartjs-style-tooltip {
            position: absolute;
            background: rgba(0, 0, 0, 0.85);
            color: white;
            border-radius: 6px;
            pointer-events: none;
            transform: translate(-50%, -100%);
            margin-top: -15px;
            z-index: 9999;
            transition: opacity 0.2s ease;
            padding: 8px 12px;
            font-family: 'Inter', sans-serif;
            font-size: 11px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            white-space: nowrap;
        }
        #chartjs-style-tooltip::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: rgba(0, 0, 0, 0.85) transparent transparent transparent;
        }
    </style>

    <div id="chartjs-style-tooltip" class="opacity-0"></div>

    <div class="dashboard-container">
        <!-- HEADER & FILTER -->
        <form id="filter-form" action="{{ route('rbd.dashboard') }}" method="GET" class="command-bar">
            <!-- SISI KIRI: TITLE & LOGO (Diperkecil) -->
            <div class="flex items-center gap-2.5">
                <div class="bg-amber-100 p-1.5 rounded-lg">
                    <i class="mdi mdi-database-cog text-amber-600 text-base xl:text-lg"></i>
                </div>
                <div>
                    <h1 class="text-xs xl:text-sm 2xl:text-base font-black text-slate-800 uppercase tracking-tight leading-none">Silo Intelligence</h1>
                    <div class="flex items-center gap-1.5 mt-1">
                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                        <span class="text-[8px] xl:text-[9px] 2xl:text-[10px] font-bold text-slate-400 uppercase">Live Mode</span>
                    </div>
                </div>
            </div>

            <!-- BAGIAN TENGAH: FILTER & SEARCH (Sejajar Bawah) -->
            <!-- items-end digunakan agar button apply sejajar rata bawah dengan input -->
            <div class="flex items-end gap-3 xl:gap-4">
                <div class="filter-item">
                    <label class="filter-label text-[9px] xl:text-[10px]">Period</label>
                    <div class="flex gap-2">
                        <!-- Ditambahkan !pr-8 agar panah tidak menabrak angka, Lebar pakai w-28 & w-32 -->
                        <select name="year" class="filter-input-clean w-28 xl:w-32 !pr-8 cursor-pointer h-[28px] xl:h-[30px]">
                            @php $currYear = date('Y'); @endphp
                            @for ($y = $currYear - 3; $y <= $currYear + 1; $y++)
                                <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                        <!-- Lebar Month diperlebar signifikan pakai w-36 & w-44 -->
                        <select name="month" class="filter-input-clean w-36 xl:w-44 !pr-8 cursor-pointer h-[28px] xl:h-[30px]">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="h-6 w-[1px] bg-slate-200 mb-[2px]"></div>

                <div class="filter-item">
                    <label class="filter-label text-[9px] xl:text-[10px]">Search</label>
                    <div class="flex gap-2">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="filter-input-clean w-48 xl:w-64 2xl:w-80 h-[28px] xl:h-[30px]">
                    </div>
                </div>

                <!-- Tombol Apply disesuaikan tingginya agar sinkron dengan input -->
                <button type="submit" id="btn-apply" class="bg-slate-500 text-white px-3 xl:px-4 rounded-lg text-[10px] xl:text-xs font-black uppercase hover:bg-slate-600 transition flex items-center gap-1.5 shadow-sm h-[28px] xl:h-[30px]">
                    <i class="mdi mdi-filter text-sm" id="btn-icon"></i> <span id="btn-text">Apply</span>
                </button>
            </div>

            <!-- SISI KANAN: SYSTEM TIME (Diperkecil) -->
            <div class="flex items-center gap-4 pl-4 border-l border-slate-100">
                <div class="text-center bg-slate-50 px-3 py-1 xl:px-4 xl:py-1.5 rounded-lg">
                    <p id="sync-time" class="text-amber-600 text-base xl:text-lg 2xl:text-xl font-bold mono leading-none mt-0.5">00:00:00</p>
                    <p class="text-[8px] xl:text-[9px] 2xl:text-[10px] text-slate-400 font-bold uppercase mt-1">System Time</p>
                </div>
            </div>
        </form>

        <!-- ROW 1: TANGKI -->
        <div class="section-card p-2 xl:p-3 flex flex-col flex-shrink-0">
            <h2 class="text-[10px] xl:text-xs 2xl:text-sm font-black text-slate-500 uppercase mb-2 flex items-center gap-2 flex-shrink-0">
                <span class="w-1.5 h-3 bg-amber-500 rounded-full"></span> Aggregate Group Stock
            </h2>
            
            <div id="tank-container" class="grid grid-cols-3 xl:grid-cols-6 gap-x-4 gap-y-[1.5vh] w-full">
                @forelse ($groupedTanks as $tank)
                    @php 
                        $percent = $tank['capacity'] > 0 ? ($tank['total_qty'] / $tank['capacity']) * 100 : 0;
                    @endphp
                    
                    <div class="tank-wrapper group cursor-pointer" 
                         data-group="{{ $tank['group_name'] }}" 
                         data-tanks="{{ $tank['tanks_included'] }}" 
                         data-items="{{ $tank['item_list'] ?? 'Empty' }}" 
                         data-volume="{{ number_format($tank['total_qty'], 2) }}">
                        
                        <div class="text-xs xl:text-[13px] 2xl:text-[14px] font-black text-slate-700 mono mb-1 whitespace-nowrap">{{ number_format($percent, 1) }}%</div>
                        
                        <div class="tank-graphic transition-transform duration-300 group-hover:scale-105">
                            <div class="tank-roof"></div>
                            <div class="tank-body">
                                @foreach($tank['items'] as $item)
                                    @php $h = ($item['qty'] / $tank['capacity']) * 100; @endphp
                                    <div class="liquid-layer" style="height: {{ $h }}%; background-color: {{ $item['color'] }};">
                                        <div class="liquid-surface" style="animation-duration: {{ rand(20, 35)/10 }}s;"></div>
                                        <div class="liquid-surface-shadow" style="animation-duration: {{ rand(30, 45)/10 }}s;"></div>
                                        <div class="bubble" style="left: 20%; animation-delay: 0.1s;"></div>
                                        <div class="bubble" style="left: 60%; animation-delay: 0.5s; width: 4px; height: 4px;"></div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="tank-base"></div>
                        </div>

                        <div class="text-[9px] xl:text-[10px] 2xl:text-[11px] leading-tight font-black text-slate-700 mt-1.5 truncate w-full text-center uppercase tracking-widest">{{ $tank['group_name'] }}</div>
                        <div class="text-[8px] xl:text-[9px] 2xl:text-[10px] font-bold text-slate-400 mt-0.5 truncate text-center">Cap: {{ number_format($tank['capacity']) }}</div>
                    </div>
                @empty
                    <div class="col-span-6 text-xs xl:text-sm font-bold text-slate-400 w-full text-center">No Tanks Data Configured.</div>
                @endforelse
            </div>

            <!-- TANK LEGEND -->
            @php
                $legendItems =[];
                foreach($groupedTanks as $tank) {
                    if(isset($tank['items'])) {
                        foreach($tank['items'] as $item) { $legendItems[$item['part']] = $item['color']; }
                    }
                }
            @endphp
            <div id="tank-legend" class="flex flex-wrap justify-center gap-x-4 xl:gap-x-5 gap-y-1 mt-[1.5vh] pt-2 border-t border-slate-100 flex-shrink-0">
                @forelse($legendItems as $part => $color)
                    <div class="flex items-center gap-1.5 xl:gap-2">
                        <span class="w-2.5 h-2.5 xl:w-3 xl:h-3 rounded-full shadow-sm" style="background-color: {{ $color }}"></span>
                        <span class="text-[9px] xl:text-[11px] font-bold text-slate-500 uppercase tracking-wide">{{ $part }}</span>
                    </div>
                @empty
                    <div class="text-[9px] xl:text-[11px] font-bold text-slate-400 uppercase">Awaiting Stock...</div>
                @endforelse
            </div>
        </div>

        <!-- ROW 2: GRAFIK & TABEL -->
        <div class="grid grid-cols-12 gap-[1vw] flex-1 max-h-[31vh]">
            
            <div class="col-span-4 section-card p-[1vh] xl:p-2 2xl:p-3 flex flex-col h-full">
                <h2 class="text-[10px] xl:text-xs 2xl:text-sm font-black text-slate-500 uppercase mb-1 xl:mb-2 flex items-center gap-2 flex-shrink-0">
                    <span class="w-1.5 h-3 bg-emerald-500 rounded-full"></span> Incoming (IN) Ranking
                </h2>
                <div class="w-full relative flex-1 min-h-0">
                    <canvas id="incomingChart"></canvas>
                </div>
            </div>

            <div class="col-span-4 section-card flex flex-col h-full">
                <div class="p-2 xl:p-3 border-b flex justify-between items-center bg-white sticky top-0 z-20 flex-shrink-0">
                    <h2 class="text-[10px] xl:text-xs 2xl:text-sm font-black text-slate-800 uppercase tracking-widest flex items-center gap-2">
                        <i class="mdi mdi-clock-alert text-rose-500 text-base xl:text-lg"></i> Top 5 Oldest Stock
                    </h2>
                </div>
                <div class="overflow-y-auto flex-1 p-1 bg-slate-50">
                    <table class="custom-table text-[9px] xl:text-[10px] 2xl:text-xs w-full h-full">
                        <thead><tr><th>Location</th><th>Item (Desc)</th><th>Age</th><th>Qty(KG)</th></tr></thead>
                        <tbody id="tbody-aging">
                            @foreach ($agingTable as $row)
                                <tr>
                                    <td class="font-bold text-slate-700">{{ $row->ld_loc ?? '-' }}</td>
                                    <td class="font-semibold" title="{{ $row->pt_desc1 }}">
                                        <span class="mono text-blue-600 block text-[10px] xl:text-[11px] 2xl:text-xs">{{ $row->ld_part }}</span>
                                        <span class="text-[8px] xl:text-[9px] 2xl:text-[10px] text-slate-400">{{ Str::limit($row->pt_desc1, 15) }}</span>
                                    </td>
                                    <td class="font-black {{ $row->aging > 10 ? 'text-rose-600' : 'text-amber-600' }}">{{ $row->aging }}d</td>
                                    <td class="font-bold text-emerald-600">{{ $row->qty_formatted }}</td>
                                </tr>
                            @endforeach
                            @if(count($agingTable) == 0)
                                <tr><td colspan="4" class="text-center py-4 text-slate-400 font-bold">No aging data available.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-span-4 section-card p-[1vh] xl:p-2 2xl:p-3 flex flex-col h-full">
                <h2 class="text-[10px] xl:text-xs 2xl:text-sm font-black text-slate-500 uppercase mb-1 xl:mb-2 flex items-center gap-2 flex-shrink-0">
                    <span class="w-1.5 h-3 bg-orange-500 rounded-full"></span> Outgoing (OUT) Dispatch
                </h2>
                <div class="w-full relative flex-1 min-h-0">
                    <canvas id="outgoingChart"></canvas>
                </div>
            </div>

        </div>
    </div>

    <script>
        // Update Waktu
        setInterval(() => {
            document.getElementById('sync-time').textContent = new Date().toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }, 1000);

        // FUNGSI TOOLTIP TANGKI DINAMIS
        function bindTankTooltips() {
            const tooltip = document.getElementById('chartjs-style-tooltip');
            const wrappers = document.querySelectorAll('.tank-wrapper');

            wrappers.forEach(tank => {
                tank.addEventListener('mouseenter', function() {
                    const group = this.getAttribute('data-group');
                    const tanks = this.getAttribute('data-tanks');
                    const items = this.getAttribute('data-items');
                    const vol = this.getAttribute('data-volume');

                    tooltip.innerHTML = `
                        <div style="font-weight: 900; font-size: 12px; margin-bottom: 6px; border-bottom: 1px solid rgba(255,255,255,0.2); padding-bottom: 4px;">${group}</div>
                        <table style="text-align: left; border-spacing: 0; font-size: 10px;">
                            <tr><td style="color: #cbd5e1; padding-right: 12px; padding-bottom: 4px;">Included Tanks:</td><td class="mono font-bold" style="padding-bottom: 4px;">${tanks}</td></tr>
                            <tr><td style="color: #cbd5e1; padding-right: 12px; padding-bottom: 4px;">Total Items:</td><td class="font-bold" style="padding-bottom: 4px;">${items}</td></tr>
                            <tr><td style="color: #cbd5e1; padding-right: 12px;">Total Volume:</td><td class="font-bold text-amber-400 text-[11px]">${vol} KG</td></tr>
                        </table>
                    `;
                    tooltip.classList.remove('opacity-0');
                });

                tank.addEventListener('mousemove', function(e) {
                    tooltip.style.left = e.pageX + 'px';
                    tooltip.style.top = e.pageY + 'px';
                });

                tank.addEventListener('mouseleave', function() {
                    tooltip.classList.add('opacity-0');
                });
            });
        }
        
        bindTankTooltips();

        // PALET WARNA CHART KANAN (Dinamic Colors)
        const outColorPalette = ['#ef4444', '#f97316', '#f59e0b', '#84cc16', '#10b981', '#06b6d4', '#3b82f6', '#8b5cf6', '#ec4899', '#f43f5e'];

        // DETEKSI LEBAR LAYAR UNTUK CHART.JS DINAMIS
        const isLargeScreen = window.innerWidth > 1400;
        const chartScaleFont = isLargeScreen ? 10 : 9; 
        const chartLegendFont = isLargeScreen ? 10 : 9; 
        const chartLegendBox = isLargeScreen ? 10 : 8; 

        const commonOptions = {
            indexAxis: 'y', 
            responsive: true, 
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: true
            },
            plugins: { 
                legend: { 
                    position: 'bottom', 
                    labels: { boxWidth: chartLegendBox, usePointStyle: true, font: { size: chartLegendFont, family: 'Inter', weight: 'bold' } } 
                }, 
                tooltip: { 
                    mode: 'index', 
                    intersect: true,
                    // PERUBAHAN UTAMA KEDUA: Filter Nilai 0 agar Tooltip Cerdas (Smart Tooltip Filter)
                    filter: function(tooltipItem) {
                        // Hanya kembalikan nilai boolean true jika datanya (raw) lebih besar dari 0
                        return tooltipItem.raw > 0;
                    }
                } 
            },
            scales: {
                x: { stacked: true, grid: { color: '#f1f5f9' }, ticks: { font: { size: chartScaleFont, weight: 'bold' }, color: '#64748b' } },
                y: { stacked: true, grid: { display: false }, ticks: { font: { size: chartScaleFont, weight: 'bold' }, color: '#334155' } }
            }
        };

        let inChart = new Chart(document.getElementById('incomingChart').getContext('2d'), {
            type: 'bar', data: { labels: {!! json_encode($inLabels) !!}, datasets: {!! json_encode($inDatasets) !!} }, options: commonOptions
        });

        // Mapping Data Warna Awal untuk Chart Kanan
        let initialOutLabels = {!! json_encode($outLabels) !!};
        let initialOutColors = initialOutLabels.map((_, i) => outColorPalette[i % outColorPalette.length]);

        let outOpt = JSON.parse(JSON.stringify(commonOptions)); outOpt.plugins.legend.display = false;
        let outChart = new Chart(document.getElementById('outgoingChart').getContext('2d'), {
            type: 'bar', 
            data: { 
                labels: initialOutLabels, 
                datasets:[{ 
                    label: 'Dispatched Qty', 
                    data: {!! json_encode($outValues) !!}, 
                    backgroundColor: initialOutColors, // Menggunakan array warna
                    borderRadius: 4 
                }] 
            }, 
            options: outOpt
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
                
                // Update Logika Warna Chart Outgoing
                outChart.data.labels = data.outLabels; 
                outChart.data.datasets[0].data = data.outValues; 
                outChart.data.datasets[0].backgroundColor = data.outLabels.map((_, i) => outColorPalette[i % outColorPalette.length]);
                outChart.update();

                let tankHtml = ''; let newLegendItems = {};
                
                if(data.groupedTanks.length === 0) {
                    tankHtml = '<div class="col-span-6 text-xs xl:text-sm font-bold text-slate-400 w-full text-center">No Tanks Data Configured.</div>';
                } else {
                    data.groupedTanks.forEach(tank => {
                        let percent = tank.capacity > 0 ? (tank.total_qty / tank.capacity) * 100 : 0;
                        let liquidHtml = '';
                        
                        tank.items.forEach(item => {
                            let h = (item.qty / tank.capacity) * 100;
                            let ws1 = (Math.random() * 1.5 + 2).toFixed(1); 
                            let ws2 = (Math.random() * 1.5 + 3).toFixed(1); 
                            
                            liquidHtml += `
                                <div class="liquid-layer" style="height: ${h}%; background-color: ${item.color};">
                                    <div class="liquid-surface" style="animation-duration: ${ws1}s;"></div>
                                    <div class="liquid-surface-shadow" style="animation-duration: ${ws2}s;"></div>
                                    <div class="bubble" style="left: 20%; animation-delay: 0.1s;"></div>
                                    <div class="bubble" style="left: 60%; animation-delay: 0.5s; width: 4px; height: 4px;"></div>
                                </div>`;
                            newLegendItems[item.part] = item.color;
                        });

                        tankHtml += `
                        <div class="tank-wrapper group cursor-pointer" 
                             data-group="${tank.group_name}" 
                             data-tanks="${tank.tanks_included}" 
                             data-items="${tank.item_list || 'Empty'}" 
                             data-volume="${parseFloat(tank.total_qty).toFixed(2)}">
                            
                            <div class="text-xs xl:text-[13px] 2xl:text-[14px] font-black text-slate-700 mono mb-1 whitespace-nowrap">${percent.toFixed(1)}%</div>
                            <div class="tank-graphic transition-transform duration-300 group-hover:scale-105">
                                <div class="tank-roof"></div>
                                <div class="tank-body">${liquidHtml}</div>
                                <div class="tank-base"></div>
                            </div>
                            <div class="text-[9px] xl:text-[10px] 2xl:text-[11px] leading-tight font-black text-slate-700 mt-1.5 truncate w-full text-center uppercase tracking-widest">${tank.group_name}</div>
                            <div class="text-[8px] xl:text-[9px] 2xl:text-[10px] font-bold text-slate-400 mt-0.5 truncate text-center">Cap: ${tank.capacity.toLocaleString()}</div>
                        </div>`;
                    });
                }
                document.getElementById('tank-container').innerHTML = tankHtml;
                
                bindTankTooltips();

                let legendHtml = '';
                if(Object.keys(newLegendItems).length === 0) {
                    legendHtml = '<div class="text-[9px] xl:text-[11px] font-bold text-slate-400 uppercase">Awaiting Stock...</div>';
                } else {
                    for (const [part, color] of Object.entries(newLegendItems)) {
                        legendHtml += `<div class="flex items-center gap-1.5 xl:gap-2"><span class="w-2.5 h-2.5 xl:w-3 xl:h-3 rounded-full shadow-sm" style="background-color: ${color}"></span><span class="text-[9px] xl:text-[11px] font-bold text-slate-500 uppercase tracking-wide">${part}</span></div>`;
                    }
                }
                document.getElementById('tank-legend').innerHTML = legendHtml;

                let agingTb = '';
                if(data.agingTable.length === 0) { 
                    agingTb = '<tr><td colspan="4" class="text-center py-4 text-slate-400 font-bold">No aging data available.</td></tr>'; 
                } else {
                    data.agingTable.forEach(row => {
                        let ageClass = row.aging > 10 ? 'text-rose-600' : 'text-amber-600';
                        let desc = limitStr(row.pt_desc1, 15);
                        agingTb += `
                        <tr>
                            <td class="font-bold text-slate-700">${row.ld_loc || '-'}</td>
                            <td class="font-semibold" title="${row.pt_desc1}">
                                <span class="mono text-blue-600 block text-[10px] xl:text-[11px] 2xl:text-xs">${row.ld_part}</span>
                                <span class="text-[8px] xl:text-[9px] 2xl:text-[10px] text-slate-400">${desc}</span>
                            </td>
                            <td class="font-black ${ageClass}">${row.aging}d</td>
                            <td class="font-bold text-emerald-600">${row.qty_formatted}</td>
                        </tr>`;
                    });
                }
                document.getElementById('tbody-aging').innerHTML = agingTb;

                btnIcon.className = "mdi mdi-filter"; btnText.textContent = "Apply";
            })
            .catch(error => {
                console.error('Error:', error); btnIcon.className = "mdi mdi-alert-circle"; btnText.textContent = "Error";
                setTimeout(() => { btnIcon.className = "mdi mdi-filter"; btnText.textContent = "Apply"; }, 2000);
            });
        });
    </script>
</x-app-layout>