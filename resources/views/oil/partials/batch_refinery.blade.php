<div class="w-full font-sans text-slate-700">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">

        <!-- KOLOM KIRI: DATA TABLE -->
        <div class="card rounded-xl shadow-lg overflow-hidden border border-slate-100 h-full flex flex-col">
            <div class="bg-blue-600 px-6 py-5 flex justify-between items-center shrink-0">
                <!-- Font Judul Diperbesar -->
                <h4 class="text-white font-bold text-3xl flex items-center gap-2">
                    <i class="mdi mdi-bottle-tonic-outline"></i> Data Batch Refinery
                </h4>
                <div class="text-right">
                    <span id="filterStatusBadge" class="bg-white/20 text-white text-sm px-3 py-1 rounded">Waiting...</span>
                    <!-- Last Update Info -->
                    <div id="lastUpdateInfo" class="text-blue-100 text-md mt-1 font-medium hidden">
                        Last Input: <span id="lastUpdateTimestamp" class="font-mono text-white font-bold">-</span>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto max-h-[750px] overflow-y-auto custom-scrollbar flex-grow">
                <table class="w-full text-left border-collapse">
                    <!-- Header Table Diperbesar (text-sm) -->
                    <thead class="text-sm font-bold text-slate-600 uppercase bg-slate-100 sticky top-0 z-10 shadow-sm">
                        <tr>
                            <th class="px-6 py-4">Tank Name</th>
                            <!-- [BARU] Kolom Current Value -->
                            <th class="px-6 py-4 text-right">Current (Kg)</th>
                            <th class="px-6 py-4 text-right">Capacity (Kg)</th>
                            <th class="px-6 py-4 text-left">Description</th>
                            <th class="px-6 py-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody id="productionTanksBody" class="divide-y divide-slate-100">
                        <tr><td colspan="5" class="p-8 text-center text-lg text-slate-400">Memuat data tabel...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- KOLOM KANAN: CONTROLLER & CHART -->
        <div class="flex flex-col gap-6">
            <div class="card rounded-xl shadow-lg border border-slate-100 p-6 bg-white">
                <!-- Judul Section Diperbesar -->
                <h5 class="text-xl font-bold text-slate-800 mb-5 border-l-4 border-emerald-500 pl-3">⚙️ Filter & Kontrol</h5>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                    <div>
                        <label for="dateStartFilter" class="block mb-2 text-sm font-bold text-slate-600 uppercase">Tanggal Mulai</label>
                        <!-- Input Diperbesar -->
                        <input type="date" id="dateStartFilter" class="w-full border-slate-300 text-base rounded-lg p-3 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                     <div>
                        <label for="dateEndFilter" class="block mb-2 text-sm font-bold text-slate-600 uppercase">Tanggal Akhir</label>
                        <input type="date" id="dateEndFilter" class="w-full border-slate-300 text-base rounded-lg p-3 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div class="mb-6">
                    <label for="groupSelector" class="block mb-2 text-sm font-bold text-slate-600 uppercase">Tampilkan Grup di Grafik</label>
                    <select id="groupSelector" class="w-full border-slate-300 text-base rounded-lg p-3 focus:ring-blue-500 focus:border-blue-500">
                        <option value="SUMMARY">📊 Tampilkan Semua Grup</option>
                        <option value="Hydro">💧 Hydro</option>
                        <option value="N.W.B">⚗️ N.W.B</option>
                        <option value="Deodorizer">🔥 Deodorizer</option>
                        <option value="Drop Tank">🛢️ Drop Tank</option>
                        <option value="Wead Tank">🗑️ Wead Tank</option>
                        <option value="Crystalizer">❄️ Crystalizer</option>
                        <option value="SX Tank">📦 SX Tank (S12-14)</option>
                    </select>
                </div>
                <button type="button" id="btnApplyRefineryFilter" class="w-full text-white bg-blue-600 hover:bg-blue-700 font-bold rounded-lg text-base px-5 py-3 shadow-md transition-all">Terapkan Filter</button>
            </div>

            <div class="card rounded-xl shadow-lg border border-slate-100 flex-grow flex flex-col bg-white">
                <div class="px-6 py-5 border-b border-slate-100">
                    <h5 id="chartTitle" class="text-xl font-bold text-slate-700">Tren Stok Harian per Grup (Kg)</h5>
                </div>
                <div class="p-4 flex-grow relative min-h-[350px]"><canvas id="refineryLineChart"></canvas></div>
                
                <!-- SNAPSHOT SECTION -->
                <div class="p-5 border-t border-slate-100 bg-slate-50/50">
                    <h6 id="snapshotTitle" class="font-bold text-slate-600 text-base mb-4">Ringkasan Stok Grup</h6>
                    <div id="groupSnapshotContainer" class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="p-4 text-center text-slate-400 col-span-full">Pilih rentang tanggal untuk melihat ringkasan.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(function () {
    let chartDetailData = {};
    window.refineryLineChartInstance = null;

    const groupConfig = {
        'Hydro':       { border: '#0ea5e9', bg: '#0ea5e933', icon: '💧' },
        'N.W.B':       { border: '#6366f1', bg: '#6366f133', icon: '⚗️' },
        'Deodorizer':  { border: '#ef4444', bg: '#ef444433', icon: '🔥' },
        'Drop Tank':   { border: '#3b82f6', bg: '#3b82f633', icon: '🛢️' },
        'Wead Tank':   { border: '#64748b', bg: '#64748b33', icon: '🗑️' },
        'Crystalizer': { border: '#10b981', bg: '#10b98133', icon: '❄️' },
        'SX Tank':     { border: '#f59e0b', bg: '#f59e0b33', icon: '📦' }
    };
    
    function renderRefineryLineChart(detailData) {
        const ctx = document.getElementById('refineryLineChart');
        if (!ctx) return;
        
        const labels = Object.keys(detailData).sort();
        
        const datasets = Object.keys(groupConfig).map(groupName => {
            const data = labels.map(date => {
                if (detailData[date] && detailData[date][groupName]) {
                    return detailData[date][groupName].reduce((sum, tank) => sum + Number(tank.value), 0);
                }
                return 0;
            });

            return {
                label: groupName, data: data,
                borderColor: groupConfig[groupName].border, backgroundColor: groupConfig[groupName].bg,
                tension: 0.3, borderWidth: 3, pointRadius: 2, pointHoverRadius: 8, fill: true,
            };
        });

        if (window.refineryLineChartInstance) window.refineryLineChartInstance.destroy();
        
        window.refineryLineChartInstance = new Chart(ctx, {
            type: 'line',
            data: { labels: labels.map(d => new Date(d).toLocaleDateString('id-ID', {day:'numeric', month:'short'})), datasets: datasets },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'nearest', axis: 'x', intersect: false },
                plugins: {
                    // Font Legend & Tooltip Diperbesar
                    legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 10, padding: 20, font: {size: 13, weight: 'bold'} } },
                    tooltip: {
                        bodyFont: { size: 13 },
                        titleFont: { size: 14 },
                        callbacks: {
                            footer: (tooltipItems) => {
                                let footerText = [];
                                const dateLabel = labels[tooltipItems[0].dataIndex];
                                tooltipItems.forEach(item => {
                                    const groupName = item.dataset.label;
                                    const groupDetails = chartDetailData[dateLabel]?.[groupName];
                                    if(groupDetails && groupDetails.length > 0) {
                                        footerText.push('');
                                        groupDetails.forEach(tank => footerText.push(`• ${tank.name}: ${new Intl.NumberFormat('id-ID').format(tank.value)}`));
                                    }
                                });
                                return footerText;
                            }
                        }
                    }
                },
                scales: { 
                    y: { ticks: { font: {size: 12}, callback: (v) => (v / 1000) + 'K' } }, 
                    x: { ticks: { font: {size: 12} }, grid: { display: false } } 
                }
            }
        });
        filterChartVisibility();
    }

    function renderGroupSnapshots(summaryData, endDate) {
        const container = $('#groupSnapshotContainer'), title = $('#snapshotTitle');
        container.empty();
        const formattedDate = new Date(endDate + 'T00:00:00').toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        title.text(`Ringkasan Stok Grup (Snapshot per ${formattedDate})`);

        if (Object.keys(summaryData).length === 0) {
            container.html('<div class="p-4 text-center text-slate-400 col-span-full text-lg">Tidak ada data ringkasan.</div>');
            return;
        }

        Object.keys(groupConfig).forEach(groupName => {
            const totalKg = summaryData[groupName] || 0;
            const config = groupConfig[groupName];
            container.append(`
                <div class="bg-white p-4 rounded-xl border-l-4 shadow-sm hover:shadow-md transition" style="border-color: ${config.border};">
                    <div class="flex items-center justify-between mb-2">
                        <p class="font-bold text-slate-700 text-sm uppercase tracking-wide truncate" title="${groupName}">${groupName}</p>
                        <span class="text-xl">${config.icon}</span>
                    </div>
                    <!-- Font Angka Summary Diperbesar -->
                    <p class="font-mono font-bold text-slate-800 text-2xl">${new Intl.NumberFormat('id-ID').format(Number(totalKg).toFixed(0))} <span class="text-xs text-slate-400 font-sans font-bold">Kg</span></p>
                </div>
            `);
        });
    }

    function updateProductionTable(tableData) {
        const tableBody = $('#productionTanksBody');
        tableBody.empty();
        
        if(!tableData || tableData.length === 0) {
            tableBody.html(`<tr><td colspan="5" class="p-8 text-center text-lg text-slate-400">Tidak ada data.</td></tr>`);
            $('#lastUpdateInfo').addClass('hidden');
            return;
        }
        
        const statusColors = { 
            'Hold': 'bg-yellow-100 text-yellow-800 border-yellow-200', 
            'Process': 'bg-blue-100 text-blue-800 border-blue-200', 
            'Release': 'bg-green-100 text-green-800 border-green-200', 
            'Reject': 'bg-red-100 text-red-800 border-red-200', 
        };
        
        let latestDate = null;

        tableData.forEach(row => {
            const badgeClass = statusColors[row.status] || 'bg-gray-100 text-gray-700 border-gray-200';
            const description = row.description || '-'; 

            // MENAMPILKAN DATA DENGAN FONT LEBIH BESAR (text-lg)
            tableBody.append(`
                <tr class="hover:bg-slate-50 transition border-b border-slate-100 last:border-0">
                    <td class="px-6 py-4 text-lg font-bold text-slate-700">${row.name}</td>
                    <!-- KOLOM BARU: CURRENT VALUE -->
                    <td class="px-6 py-4 text-lg text-right font-mono font-bold text-blue-600">${row.current_value}</td>
                    <td class="px-6 py-4 text-lg text-right font-mono text-slate-500">${row.capacity_kg}</td>
                    <td class="px-6 py-4 text-base text-left text-slate-500 truncate max-w-[150px]" title="${description}">${description}</td>
                    <td class="px-6 py-4 text-lg text-center">
                        <span class="px-3 py-1 rounded text-xs font-bold uppercase border shadow-sm ${badgeClass}">${row.status}</span>
                    </td>
                </tr>
            `);

            // Cari tanggal paling baru
            if(row.updated_at) {
                const rowDate = new Date(row.updated_at);
                if (!latestDate || rowDate > latestDate) {
                    latestDate = rowDate;
                }
            }
        });

        if(latestDate) {
            const dateStr = latestDate.toLocaleDateString('id-ID', { 
                day: 'numeric', month: 'long', year: 'numeric' 
            });
            $('#lastUpdateTimestamp').text(dateStr);
            $('#lastUpdateInfo').removeClass('hidden');
        } else {
            $('#lastUpdateInfo').addClass('hidden');
        }
    }

    function applyRefineryFilter() {
        const btn = $('#btnApplyRefineryFilter'), badge = $('#filterStatusBadge');
        const data = { start_date: $('#dateStartFilter').val(), end_date: $('#dateEndFilter').val() };
        
        if (!data.start_date || !data.end_date) { alert('Silakan pilih rentang tanggal.'); return; }

        btn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Memuat...');
        badge.text("Loading...");
        $('#lastUpdateInfo').addClass('hidden');

        $.ajax({
            url: '{{ route("oil.getRefineryData") }}', type: 'GET', data: data,
            success: function(res) {
                chartDetailData = res.chartDetailData;
                renderRefineryLineChart(res.chartDetailData);
                updateProductionTable(res.tableData);
                renderGroupSnapshots(res.summaryData, data.end_date);
                badge.text("Updated").attr('class', 'bg-green-500/20 text-white text-md px-2 py-1 rounded');
            },
            error: function() { 
                alert('Gagal memuat data refinery.'); 
                badge.text("Error").attr('class', 'bg-red-500/20 text-white text-xs px-2 py-1 rounded');
            },
            complete: function() { btn.prop('disabled', false).html('Terapkan Filter'); }
        });
    }
    
    function filterChartVisibility() {
        const selectedGroup = $('#groupSelector').val();
        if (window.refineryLineChartInstance) {
            window.refineryLineChartInstance.data.datasets.forEach(dataset => {
                dataset.hidden = !(selectedGroup === 'SUMMARY' || dataset.label === selectedGroup);
            });
            window.refineryLineChartInstance.update();
        }
    }

    $('#btnApplyRefineryFilter').on('click', applyRefineryFilter);
    $('#groupSelector').on('change', filterChartVisibility);

    const today = new Date(), lastWeek = new Date();
    lastWeek.setDate(today.getDate() - 6);
    $('#dateEndFilter').val(today.toISOString().split('T')[0]);
    $('#dateStartFilter').val(lastWeek.toISOString().split('T')[0]);
    
    applyRefineryFilter();
});
</script>