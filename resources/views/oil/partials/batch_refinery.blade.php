<div class="w-full font-sans">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">

        <!-- KOLOM KIRI: DATA TABLE -->
        <div class="card rounded-xl shadow-lg overflow-hidden border border-slate-100 h-full flex flex-col">
            <div class="bg-blue-600 px-6 py-4 flex justify-between items-center shrink-0">
                <h4 class="text-white font-semibold text-lg flex items-center gap-2"><i class="mdi mdi-bottle-tonic-outline"></i> Data Batch Refinery</h4>
                <span id="filterStatusBadge" class="bg-white/20 text-white text-xs px-2 py-1 rounded">Waiting...</span>
            </div>
            <div class="overflow-x-auto max-h-[650px] overflow-y-auto custom-scrollbar flex-grow">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-slate-500 uppercase bg-slate-50 sticky top-0 z-10 shadow-sm">
                        <tr>
                            <th class="px-6 py-3 font-bold">Tank Name</th>
                            <th class="px-6 py-3 font-bold text-right">Capacity (Kg)</th>
                            <th class="px-6 py-3 font-bold text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody id="productionTanksBody" class="divide-y divide-slate-100">
                        <tr><td colspan="3" class="p-8 text-center text-slate-400">Memuat data tabel...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- KOLOM KANAN: CONTROLLER & CHART -->
        <div class="flex flex-col gap-6">
            <div class="card rounded-xl shadow-lg border border-slate-100 p-6 bg-white">
                <h5 class="text-lg font-bold text-slate-700 mb-4 border-l-4 border-emerald-500 pl-3">⚙️ Filter & Kontrol</h5>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="dateStartFilter" class="block mb-1 text-xs font-semibold text-slate-500 uppercase">Tanggal Mulai</label>
                        <input type="date" id="dateStartFilter" class="w-full border-slate-200 text-sm rounded-lg p-2.5 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                     <div>
                        <label for="dateEndFilter" class="block mb-1 text-xs font-semibold text-slate-500 uppercase">Tanggal Akhir</label>
                        <input type="date" id="dateEndFilter" class="w-full border-slate-200 text-sm rounded-lg p-2.5 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div class="mb-5">
                    <label for="groupSelector" class="block mb-1 text-xs font-semibold text-slate-500 uppercase">Tampilkan Grup di Grafik</label>
                    <select id="groupSelector" class="w-full border-slate-200 text-sm rounded-lg p-2.5 focus:ring-blue-500 focus:border-blue-500">
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
                <button type="button" id="btnApplyRefineryFilter" class="w-full text-white bg-blue-600 hover:bg-blue-700 font-medium rounded-lg text-sm px-5 py-2.5 shadow-md transition-all">Terapkan Filter</button>
            </div>

            <div class="card rounded-xl shadow-lg border border-slate-100 flex-grow flex flex-col bg-white">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h5 id="chartTitle" class="font-bold text-slate-700">Tren Stok Harian per Grup (Kg)</h5>
                </div>
                <div class="p-4 flex-grow relative min-h-[300px]"><canvas id="refineryLineChart"></canvas></div>
                
                <!-- SNAPSHOT SECTION -->
                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    <h6 id="snapshotTitle" class="font-bold text-slate-600 text-sm mb-3">Ringkasan Stok Grup</h6>
                    <div id="groupSnapshotContainer" class="grid grid-cols-2 md:grid-cols-4 gap-3">
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

    // KONFIGURASI SESUAI DB BARU
    const groupConfig = {
        'Hydro':       { border: '#0ea5e9', bg: '#0ea5e933', icon: '💧' }, // Sky Blue
        'N.W.B':       { border: '#6366f1', bg: '#6366f133', icon: '⚗️' }, // Indigo
        'Deodorizer':  { border: '#ef4444', bg: '#ef444433', icon: '🔥' }, // Red
        'Drop Tank':   { border: '#3b82f6', bg: '#3b82f633', icon: '🛢️' }, // Blue
        'Wead Tank':   { border: '#64748b', bg: '#64748b33', icon: '🗑️' }, // Slate
        'Crystalizer': { border: '#10b981', bg: '#10b98133', icon: '❄️' }, // Emerald
        'SX Tank':     { border: '#f59e0b', bg: '#f59e0b33', icon: '📦' }  // Amber
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
                label: groupName,
                data: data,
                borderColor: groupConfig[groupName].border,
                backgroundColor: groupConfig[groupName].bg,
                tension: 0.3, borderWidth: 2, pointRadius: 0, pointHoverRadius: 6, fill: true,
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
                    legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, padding: 15, font: {size: 11} } },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.95)',
                        titleColor: '#1e293b',
                        bodyColor: '#475569',
                        footerColor: '#475569',
                        borderColor: '#e2e8f0',
                        borderWidth: 1,
                        padding: 10,
                        callbacks: {
                            footer: (tooltipItems) => {
                                let footerText = [];
                                const dateLabel = labels[tooltipItems[0].dataIndex];
                                
                                tooltipItems.forEach(item => {
                                    const groupName = item.dataset.label;
                                    const groupDetails = chartDetailData[dateLabel]?.[groupName];
                                    
                                    if(groupDetails && groupDetails.length > 0) {
                                        footerText.push('');
                                        groupDetails.forEach(tank => {
                                            footerText.push(`• ${tank.name}: ${new Intl.NumberFormat('id-ID').format(tank.value)}`);
                                        });
                                    }
                                });
                                return footerText;
                            }
                        }
                    }
                },
                scales: { 
                    y: { ticks: { callback: (v) => (v / 1000) + 'K' } },
                    x: { grid: { display: false } }
                }
            }
        });
        
        filterChartVisibility();
    }

    function renderGroupSnapshots(summaryData, endDate) {
        const container = $('#groupSnapshotContainer');
        const title = $('#snapshotTitle');
        container.empty();

        const formattedDate = new Date(endDate + 'T00:00:00').toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        title.text(`Ringkasan Stok Grup (Snapshot per ${formattedDate})`);

        if (Object.keys(summaryData).length === 0) {
            container.html('<div class="p-4 text-center text-slate-400 col-span-full">Tidak ada data ringkasan.</div>');
            return;
        }

        Object.keys(groupConfig).forEach(groupName => {
            const totalKg = summaryData[groupName] || 0;
            const config = groupConfig[groupName];

            const cardHtml = `
                <div class="bg-white p-3 rounded-lg border-l-4 shadow-sm hover:shadow-md transition" style="border-color: ${config.border};">
                    <div class="flex items-center justify-between mb-1">
                        <p class="font-bold text-slate-700 text-xs uppercase tracking-wide truncate" title="${groupName}">${groupName}</p>
                        <span class="text-base">${config.icon}</span>
                    </div>
                    <p class="font-mono font-bold text-slate-800 text-lg">
                        ${new Intl.NumberFormat('id-ID').format(Number(totalKg).toFixed(0))}
                        <span class="text-[10px] text-slate-400 font-sans font-medium">Kg</span>
                    </p>
                </div>
            `;
            container.append(cardHtml);
        });
    }

    function updateProductionTable(tableData) {
        const tableBody = $('#productionTanksBody');
        tableBody.empty();
        if(!tableData || tableData.length === 0) {
            tableBody.html(`<tr><td colspan="3" class="p-8 text-center text-slate-400">Tidak ada data.</td></tr>`);
            return;
        }
        
        const statusColors = { 
            'Hold': 'bg-yellow-100 text-yellow-700 border-yellow-200', 
            'Process': 'bg-blue-100 text-blue-700 border-blue-200', 
            'Release': 'bg-green-100 text-green-700 border-green-200', 
            'Reject': 'bg-red-100 text-red-700 border-red-200', 
        };

        tableData.forEach(row => {
            const badgeClass = statusColors[row.status] || 'bg-gray-100 text-gray-600 border-gray-200';
            const statusBadge = `<span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase border ${badgeClass}">${row.status}</span>`;
            
            tableBody.append(`
                <tr class="hover:bg-slate-50 transition border-b border-slate-50 last:border-0">
                    <td class="px-6 py-3 font-medium text-slate-700">
                        ${row.name}
                    </td>
                    <td class="px-6 py-3 text-right font-mono text-slate-600">${row.capacity_kg}</td>
                    <td class="px-6 py-3 text-center">${statusBadge}</td>
                </tr>
            `);
        });
    }

    function applyRefineryFilter() {
        const btn = $('#btnApplyRefineryFilter'), badge = $('#filterStatusBadge');
        const data = { start_date: $('#dateStartFilter').val(), end_date: $('#dateEndFilter').val() };
        
        if (!data.start_date || !data.end_date) { alert('Silakan pilih rentang tanggal.'); return; }

        btn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Memuat...');
        badge.text("Loading...");

        $.ajax({
            url: '{{ route("oil.getRefineryData") }}', type: 'GET', data: data,
            success: function(res) {
                chartDetailData = res.chartDetailData;
                renderRefineryLineChart(res.chartDetailData);
                updateProductionTable(res.tableData);
                renderGroupSnapshots(res.summaryData, data.end_date);
                badge.text("Updated").attr('class', 'bg-green-500/20 text-white text-xs px-2 py-1 rounded');
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