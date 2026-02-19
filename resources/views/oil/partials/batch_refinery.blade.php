{{-- 
    File: resources/views/oil/partials/batch_refinery.blade.php
    Deskripsi: Komponen Batch Refinery Dashboard
    Fitur: Live Monitoring, Filter Group/Tank, Eksport per Shift/Daily, Visualisasi KG.
--}}

<div id="batchRefineryDashboard">

<style>
    /* 
      ================================================================
      CSS ISOLATED FOR BATCH REFINERY
      ================================================================
    */
    #batchRefineryDashboard {
        --bg-color: #f1f5f9;
        --card-bg: #ffffff;
        --primary: #1d4ed8; /* Blue 700 */
        --text-dark: #1e293b;
        --text-gray: #64748b;
        --border: #e2e8f0;
        --header-height: 60px;
        --gap: 16px;
        font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    }

    /* Layout Utama */
    #batchRefineryDashboard .dashboard-container { display: flex; flex-direction: column; width: 100%; box-sizing: border-box; }
    #batchRefineryDashboard .dashboard-header { height: var(--header-height); display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--gap); }
    #batchRefineryDashboard .header-title h1 { margin: 0; font-size: 26px; font-weight: 800; color: #1e3a8a; }
    #batchRefineryDashboard .header-title p { margin: 0; font-size: 16px; color: var(--text-gray); }
    #batchRefineryDashboard .header-time { font-size: 22px; font-weight: bold; font-family: monospace; }
    
    /* Grid Content */
    #batchRefineryDashboard .content-grid { display: grid; grid-template-columns: 3fr 2fr; gap: var(--gap); }

    /* Card Styling */
    #batchRefineryDashboard .card { background: var(--card-bg); border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.07); border: 1px solid var(--border); display: flex; flex-direction: column; }
    #batchRefineryDashboard .card-header { background: #eff6ff; padding: 12px 18px; border-bottom: 1px solid var(--border); font-weight: 700; font-size: 18px; display: flex; justify-content: space-between; align-items: center; color: #334155; }
    #batchRefineryDashboard .card-header.primary { background: var(--primary); color: white; }

    /* Table Styling */
    #batchRefineryDashboard .table-container { overflow-x: auto; }
    #batchRefineryDashboard table { width: 100%; border-collapse: collapse; }
    #batchRefineryDashboard thead th { position: sticky; top: 0; background: #f8fafc; padding: 14px 16px; text-align: left; font-size: 14px; text-transform: uppercase; color: #475569; box-shadow: 0 2px 2px rgba(0,0,0,0.05); z-index: 10; white-space: nowrap; }
    #batchRefineryDashboard tbody td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; font-size: 16px; font-weight: 600; color: #334155; }
    #batchRefineryDashboard tbody tr:nth-child(even) { background-color: #fcfcfc; }
    
    /* Column Widths */
    #batchRefineryDashboard th.col-name { width: 20%; }
    #batchRefineryDashboard th.col-num { width: 12%; }
    #batchRefineryDashboard th.col-desc { width: 40%; }
    #batchRefineryDashboard th.col-status { width: 10%; text-align: center; }
    #batchRefineryDashboard th.col-percent { width: 8%; text-align: center; }
    #batchRefineryDashboard td.col-desc-cell { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 0; font-size: 15px; color: #475569; }

    /* Right Panel & Controls */
    #batchRefineryDashboard .right-panel { display: flex; flex-direction: column; gap: var(--gap); }
    #batchRefineryDashboard .control-card .card-body { padding: 16px; }
    #batchRefineryDashboard .control-row { display: flex; gap: 10px; margin-bottom: 10px; }
    #batchRefineryDashboard .form-input { flex: 1; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 16px; font-weight: bold; }
    
    /* Buttons */
    #batchRefineryDashboard .btn { padding: 12px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 16px; color: white; display: flex; align-items: center; justify-content: center; gap: 8px; transition: background-color 0.2s; }
    #batchRefineryDashboard .btn-blue { background-color: var(--primary); }
    #batchRefineryDashboard .btn-blue:hover { background-color: #1e40af; }
    #batchRefineryDashboard .btn-green { background-color: #16a34a; }
    #batchRefineryDashboard .btn-green:hover { background-color: #15803d; }
    #batchRefineryDashboard .btn.auto-width { width: auto; padding-left: 24px; padding-right: 24px; }
    #batchRefineryDashboard .btn.full-width { width: 100%; }

    /* Visual Grid (Bars) */
    #batchRefineryDashboard .visual-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(85px, 1fr)); gap: 12px; padding: 16px; }
    #batchRefineryDashboard .tank-bar-wrapper { text-align: center; }
    #batchRefineryDashboard .tank-box { height: 80px; background: #e2e8f0; border-radius: 4px; position: relative; overflow: hidden; border: 1px solid #cbd5e1; }
    #batchRefineryDashboard .tank-fill { position: absolute; bottom: 0; width: 100%; background: #f59e0b; transition: height 0.5s ease-out; border-top: 2px solid rgba(0,0,0,0.1); }
    #batchRefineryDashboard .visual-grid .tank-bar-wrapper div:nth-of-type(2) { font-size: 12px; font-weight: bold; margin-top: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    #batchRefineryDashboard .visual-grid .tank-bar-wrapper div:nth-of-type(3) { font-size: 12px; color:#334155; font-weight: 700; font-family: monospace; }

    /* Chart & Summary */
    #batchRefineryDashboard .chart-card { min-height: 300px; display: flex; flex-direction: column; }
    #batchRefineryDashboard .chart-wrapper { flex: 1; position: relative; padding: 16px; min-height: 250px; }
    #batchRefineryDashboard .summary-title { padding: 8px 16px; background-color: #f8fafc; border-bottom: 1px solid var(--border); font-size: 12px; color: var(--text-gray); text-align: center; }
    #batchRefineryDashboard .summary-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; padding: 16px; }
    #batchRefineryDashboard .summary-item { background: white; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; }
    #batchRefineryDashboard .summary-item div:first-child { font-size: 12px; color:#1d4ed8; font-weight:bold; text-transform:uppercase; }
    #batchRefineryDashboard .summary-item div:last-child { font-size: 18px; font-weight:800; }

    /* Badges */
    #batchRefineryDashboard .badge { padding: 5px 10px; border-radius: 6px; font-size: 13px; text-transform: uppercase; font-weight: 800; }
    #batchRefineryDashboard .bg-proc { background: #dbeafe; color: #1e40af; }
    #batchRefineryDashboard .bg-hold { background: #fef3c7; color: #92400e; }
    #batchRefineryDashboard .bg-done { background: #d1fae5; color: #065f46; }

    /* Responsive */
    @media (max-width: 1024px) { #batchRefineryDashboard .content-grid { grid-template-columns: 1fr; } }
</style>

<div class="dashboard-container">
    
    <!-- HEADER -->
    <header class="dashboard-header">
        <div class="header-title">
            <h1>BATCH REFINERY</h1>
            <p>Real-time Monitoring & Analysis</p>
        </div>
        <div class="header-time" id="currentTime">--:--:--</div>
    </header>

    <div class="content-grid">
        
        <!-- LEFT PANEL: TABLE -->
        <div class="card left-panel">
            <div class="card-header primary">
                <span><i class="mdi mdi-table"></i> Live Data</span>
                <span id="lastUpdateBadge" style="font-size:14px; background:rgba(255,255,255,0.2); padding:4px 10px; border-radius:6px;">Waiting...</span>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th class="col-name">Tank Name</th>
                            <th class="col-num" style="text-align:right">Current</th>
                            <th class="col-num" style="text-align:right">Max</th>
                            <th class="col-desc">Description</th>
                            <th class="col-status">Status</th>
                            <th class="col-percent">%</th>
                        </tr>
                    </thead>
                    <tbody id="productionTanksBody">
                        <!-- Data will be injected here -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- RIGHT PANEL: CONTROLS, VISUALS, CHART -->
        <div class="right-panel">
            
            <!-- 1. CONTROL PANEL -->
            <div class="card control-card">
                <div class="card-header" style="padding: 10px 18px; font-size: 16px;">⚙️ Control Panel</div>
                <div class="card-body">
                    <!-- Date Filter -->
                    <div class="control-row">
                        <input type="date" id="dateStartFilter" class="form-input">
                        <input type="date" id="dateEndFilter" class="form-input">
                    </div>
                    
                    <!-- Group & Tank Filter -->
                    <div class="control-row">
                        <select id="filterGroup" class="form-input" style="cursor: pointer;">
                            <option value="ALL">All Groups</option>
                            @foreach($groups as $grp)
                                <option value="{{ $grp }}">{{ $grp }}</option>
                            @endforeach
                        </select>
                        <select id="filterTank" class="form-input" style="cursor: pointer;">
                            <option value="ALL">All Tanks</option>
                        </select>
                    </div>
                    
                    <!-- Apply Button -->
                    <div class="control-row" style="margin-top: 4px;">
                        <button type="button" id="btnApplyRefineryFilter" class="btn btn-blue full-width">
                            <i class="mdi mdi-filter-variant"></i> Apply Filter
                        </button>
                    </div>
                    
                    <!-- Export Section (New) -->
                    <div class="control-row" style="margin-top:16px; border-top: 1px solid var(--border); padding-top: 16px; margin-bottom: 0;">
                        <select id="exportType" class="form-input" style="cursor: pointer;">
                            <option value="daily">Daily Report (Last Shift)</option>
                            <option value="shift_1">Shift 1 Report</option>
                            <option value="shift_2">Shift 2 Report</option>
                            <option value="shift_3">Shift 3 Report</option>
                        </select>
                         <button type="button" id="btnExport" class="btn btn-green auto-width">
                            <i class="mdi mdi-download"></i> Export
                        </button>
                    </div>
                </div>
            </div>

            <!-- 2. VISUAL CARD -->
            <div class="card visual-card">
                <div class="card-header" style="padding: 10px 18px; font-size: 16px;">📊 Stock Visual</div>
                <div id="tankVisualContainer" class="visual-grid">
                    <!-- Bars will be injected here -->
                </div>
            </div>

            <!-- 3. CHART CARD -->
            <div class="card chart-card">
                <div class="card-header" style="padding: 10px 18px; font-size: 16px;">📈 Average Snapshot</div>
                <div id="summaryTitle" class="summary-title">Menunggu filter...</div>
                <div class="summary-row" id="groupSnapshotContainer">
                    <!-- Summary numbers will be injected here -->
                </div>
                <div class="chart-wrapper">
                    <canvas id="refineryLineChart"></canvas>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
$(function () {
    // 1. Clock Configuration
    setInterval(() => { 
        const timeEl = document.getElementById('currentTime');
        if(timeEl) timeEl.innerText = new Date().toLocaleTimeString('id-ID', { hour12: false }); 
    }, 1000);

    // 2. Default Date Initialization (Hari Ini)
    const today = new Date().toISOString().split('T')[0];
    $('#dateStartFilter').val(today);
    $('#dateEndFilter').val(today);

    // 3. Dependent Dropdown (Fetch Tanks when Group Changes)
    $('#filterGroup').on('change', function() {
        const selectedGroup = $(this).val();
        const tankFilter = $('#filterTank');
        
        // Reset option
        tankFilter.html('<option value="ALL">All Tanks</option>');
        
        if (selectedGroup !== 'ALL') {
            const url = '{{ route("oil.getTanksByGroup", ["group" => "GROUP_PLACEHOLDER"]) }}'.replace('GROUP_PLACEHOLDER', selectedGroup);
            $.get(url, function(tanks) {
                tanks.forEach(tank => {
                    tankFilter.append(`<option value="${tank.id}">${tank.name}</option>`);
                });
            });
        }
    });

    // 4. Main Function: Apply Filter & Load Data
    function applyRefineryFilter() {
        const btn = $('#btnApplyRefineryFilter');
        const data = { 
            start_date: $('#dateStartFilter').val(), 
            end_date: $('#dateEndFilter').val(),
            group: $('#filterGroup').val(),
            tank_id: $('#filterTank').val()
        };
        
        // UI Loading State
        btn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Loading...');
        const ajaxUrl = '{{ route("oil.getRefineryData") }}'; 

        $.ajax({
            url: ajaxUrl, type: 'GET', data: data,
            success: function(res) {
                renderTable(res.tableData);
                renderVisuals(res.tableData);
                renderChart(res.chartDetailData);
                renderAverageSnapshot(res.averageSummary, data.start_date, data.end_date);
                $('#lastUpdateBadge').text("LIVE").css('background', '#22c55e');
            },
            error: function() { 
                console.error('Failed to load data'); 
                $('#productionTanksBody').html('<tr><td colspan="6" style="text-align:center; padding:20px;">Error loading data.</td></tr>');
            },
            complete: function() { 
                btn.prop('disabled', false).html('<i class="mdi mdi-filter-variant"></i> Apply Filter');
            }
        });
    }

    // 5. Render Functions
    
    // Render Table
    function renderTable(data) {
        const tbody = $('#productionTanksBody');
        tbody.empty();
        if(!data || !data.length) { 
            tbody.html('<tr><td colspan="6" style="text-align:center; padding:20px;">No Data Available</td></tr>'); 
            return; 
        }
        data.forEach(row => {
            let badgeClass = 'bg-proc';
            if(row.status === 'Hold') badgeClass = 'bg-hold';
            if(row.status === 'Release') badgeClass = 'bg-done';
            tbody.append(`
                <tr>
                    <td>${row.name}</td>
                    <td style="text-align:right; font-family:monospace; color:#1d4ed8;">${row.current_value}</td>
                    <td style="text-align:right; color:#64748b;">${row.capacity_kg}</td>
                    <td class="col-desc-cell" title="${row.description || ''}">${row.description || '-'}</td>
                    <td style="text-align:center"><span class="badge ${badgeClass}">${row.status || 'N/A'}</span></td>
                    <td style="text-align:center; font-weight:bold;">${row.fill_percent}%</td>
                </tr>
            `);
        });
    }

    // Render Visuals (Bars with KG values)
    function renderVisuals(data) {
        const container = $('#tankVisualContainer');
        container.empty();
        if (!data) return;
        data.forEach(tank => {
            const h = parseFloat(tank.fill_percent) || 0;
            const rawVal = parseFloat(tank.raw_value) || 0;
            // Format angka misal: 15.000 -> 15K atau full format
            const formattedKg = new Intl.NumberFormat('id-ID', { notation: "compact", maximumFractionDigits: 1 }).format(rawVal);
            
            container.append(`
                <div class="tank-bar-wrapper" title="${tank.name}: ${h.toFixed(1)}%">
                    <div class="tank-box">
                        <div class="tank-fill" style="height: ${h}%"></div>
                    </div>
                    <div>${tank.name}</div>
                    <div>${formattedKg} Kg</div>
                </div>
            `);
        });
    }

    // Render Summary (Average Snapshot)
    function renderAverageSnapshot(data, startDate, endDate) {
        const container = $('#groupSnapshotContainer');
        const titleEl = $('#summaryTitle');
        container.empty();
        
        const formattedStart = new Date(startDate).toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
        const formattedEnd = new Date(endDate).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
        
        titleEl.text(`Rata-rata Stok Periode: ${formattedStart} - ${formattedEnd}`);
        
        if (!data || Object.keys(data).length === 0) {
            container.html('<div style="grid-column: span 2; text-align: center; font-size: 14px; color: #9ca3af;">No data for summary</div>');
            return;
        }
        Object.keys(data).forEach(grp => {
            container.append(`
                <div class="summary-item">
                    <div>${grp}</div>
                    <div>${new Intl.NumberFormat('id-ID', { notation: "compact", maximumFractionDigits: 1 }).format(data[grp] || 0)}</div>
                </div>
            `);
        });
    }

    // Render Line Chart
    let chartInstance = null;
    function renderChart(detailData) {
        const ctx = document.getElementById('refineryLineChart');
        if (!ctx) return;
        if (!detailData) detailData = {};
        const labels = Object.keys(detailData).sort();
        const groupNames = ['Hydro', 'N.W.B', 'Deodorizer', 'Drop Tank', 'Head Tank', 'Crystalizer', 'SX Tank'];
        const datasets = groupNames.map((grp, idx) => {
            const colors = ['#0ea5e9', '#6366f1', '#ef4444', '#3b82f6', '#64748b', '#10b981', '#f59e0b'];
            return {
                label: grp,
                data: labels.map(d => detailData[d]?.[grp]?.reduce((s,t) => s + (Number(t.value) || 0), 0) || 0),
                borderColor: colors[idx % colors.length],
                tension: 0.3, fill: false, borderWidth: 2, pointRadius: 2
            };
        });
        
        if(chartInstance) chartInstance.destroy();
        chartInstance = new Chart(ctx, {
            type: 'line',
            data: { labels: labels, datasets: datasets },
            options: { 
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { labels: { boxWidth: 12, font: {size: 12} } } },
                scales: { 
                    x: { ticks: {font:{size:12}}, grid: {display:false} },
                    y: { ticks: {font:{size:12}}, grid: {color:'#f1f5f9'} } 
                }
            }
        });
    }
    
    // 6. Export Button Handler
    $('#btnExport').on('click', function() {
        const startDate = $('#dateStartFilter').val();
        const endDate = $('#dateEndFilter').val();
        const exportType = $('#exportType').val(); // Ambil tipe ekspor dari dropdown
        
        if(!startDate || !endDate) { 
            alert("Pilih rentang tanggal terlebih dahulu."); 
            return; 
        }
        
        const url = new URL('{{ route("oil.exportRefineryData") }}', window.location.origin);
        url.searchParams.append('start_date', startDate);
        url.searchParams.append('end_date', endDate);
        url.searchParams.append('export_type', exportType);
        
        window.location.href = url.href;
    });

    // 7. Initialize
    // Event Listener untuk tombol apply sudah didefinisikan di atas (secara implisit oleh struktur ini)
    $('#btnApplyRefineryFilter').on('click', applyRefineryFilter);
    
    // Load pertama kali
    applyRefineryFilter();
});
</script>

</div>