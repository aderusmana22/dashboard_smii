<div class="w-full font-sans">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">

        <!-- KOLOM KIRI: DATA TABLE -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-slate-100 h-full flex flex-col">
            <div class="bg-blue-500 px-6 py-4 flex justify-between items-center shrink-0">
                <h4 class="text-white font-semibold text-lg flex items-center gap-2"><i class="mdi mdi-bottle-tonic-outline"></i> Data Batch Refinery</h4>
                <span id="filterStatusBadge" class="bg-white/20 text-white text-xs px-2 py-1 rounded">Loading...</span>
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
            <div class="bg-white rounded-xl shadow-lg border border-slate-100 p-6">
                <h5 class="text-lg font-bold text-slate-700 mb-4 border-l-4 border-emerald-500 pl-3">⚙️ Filter & Kontrol</h5>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="dateStartFilter" class="block mb-1 text-xs font-semibold text-slate-500 uppercase">Tanggal Mulai</label>
                        <input type="date" id="dateStartFilter" class="w-full bg-slate-50 border-slate-200 text-sm rounded-lg p-2.5">
                    </div>
                     <div>
                        <label for="dateEndFilter" class="block mb-1 text-xs font-semibold text-slate-500 uppercase">Tanggal Akhir</label>
                        <input type="date" id="dateEndFilter" class="w-full bg-slate-50 border-slate-200 text-sm rounded-lg p-2.5">
                    </div>
                </div>

                <div class="mb-5">
                    <label for="groupSelector" class="block mb-1 text-xs font-semibold text-slate-500 uppercase">Tampilkan Grup di Grafik</label>
                    <select id="groupSelector" class="w-full bg-slate-50 border-slate-200 text-sm rounded-lg p-2.5">
                        <option value="SUMMARY">📊 Tampilkan Semua Grup</option>
                        <option value="CRYSTALIZER">❄️ Hanya Crystalizers</option>
                        <option value="DROPTANK">💧 Hanya Drop Tanks</option>
                        <option value="DEODORIZER">🔥 Hanya Deodorizers</option>
                        <option value="STANK">🛢️ Hanya S-Tanks</option>
                        <option value="PROCESS">⚙️ Hanya Process Tanks</option>
                        <option value="OTHER">📦 Hanya Other</option>
                    </select>
                </div>
                <button type="button" id="btnApplyRefineryFilter" class="w-full text-white bg-blue-600 hover:bg-blue-700 font-medium rounded-lg text-sm px-5 py-2.5 shadow-md">Terapkan Filter</button>
            </div>

            <div class="bg-white rounded-xl shadow-lg border border-slate-100 flex-grow flex flex-col">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                    <h5 id="chartTitle" class="font-bold text-slate-700">Tren Stok Harian per Grup (Kg)</h5>
                </div>
                <div class="p-4 flex-grow relative min-h-[300px]"><canvas id="refineryLineChart"></canvas></div>
                
                <!-- ==================================================== -->
                <!-- ============ BAGIAN SNAPSHOT GRUP BARU ============ -->
                <!-- ==================================================== -->
                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    <h6 id="snapshotTitle" class="font-bold text-slate-600 text-sm mb-3">Ringkasan Stok Grup</h6>
                    <div id="groupSnapshotContainer" class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <!-- Kartu snapshot akan di-generate oleh JavaScript di sini -->
                        <div class="p-4 text-center text-slate-400 col-span-full">Pilih rentang tanggal untuk melihat ringkasan.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SCRIPT BARU SECARA KESELURUHAN --}}
<script>
$(function () {
    // Variabel global untuk menyimpan data detail & instance chart
    let chartDetailData = {};
    window.refineryLineChartInstance = null;

    // Palet warna dan ikon untuk grup (bisa digunakan bersama)
    const groupConfig = {
        DROPTANK:    { border: '#3b82f6', bg: '#3b82f633', icon: '💧' },
        PROCESS:     { border: '#6366f1', bg: '#6366f133', icon: '⚙️' },
        CRYSTALIZER: { border: '#10b981', bg: '#10b98133', icon: '❄️' },
        STANK:       { border: '#f59e0b', bg: '#f59e0b33', icon: '🛢️' },
        DEODORIZER:  { border: '#ef4444', bg: '#ef444433', icon: '🔥' },
        OTHER:       { border: '#6b7280', bg: '#6b728033', icon: '📦' }
    };
    
    // Fungsi untuk merender atau memperbarui LINE CHART
    function renderRefineryLineChart(detailData) {
        const ctx = document.getElementById('refineryLineChart');
        if (!ctx) return;
        
        const labels = Object.keys(detailData).sort();
        const datasets = Object.keys(groupConfig).map(groupName => {
            const data = labels.map(date => {
                if (detailData[date] && detailData[date][groupName]) {
                    return detailData[date][groupName].reduce((sum, tank) => sum + tank.value, 0);
                }
                return null;
            });

            return {
                label: groupName,
                data: data,
                borderColor: groupConfig[groupName].border,
                backgroundColor: groupConfig[groupName].bg,
                tension: 0.3, borderWidth: 2, pointRadius: 0, pointHoverRadius: 5, fill: true,
            };
        });

        if (window.refineryLineChartInstance) window.refineryLineChartInstance.destroy();
        
        window.refineryLineChartInstance = new Chart(ctx, {
            type: 'line',
            data: { labels: labels.map(d => new Date(d).toLocaleDateString('id-ID', {day:'numeric', month:'short'})), datasets: datasets },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, padding: 20 } },
                    tooltip: {
                        callbacks: {
                            footer: (tooltipItems) => {
                                let footerText = [];
                                const dateLabel = labels[tooltipItems[0].dataIndex];
                                
                                tooltipItems.forEach(item => {
                                    const groupName = item.dataset.label;
                                    const groupDetails = chartDetailData[dateLabel]?.[groupName];
                                    
                                    if(groupDetails && groupDetails.length > 0) {
                                        footerText.push('');
                                        footerText.push(`--- ${groupName} ---`);
                                        groupDetails.forEach(tank => {
                                            footerText.push(`${tank.name}: ${new Intl.NumberFormat('id-ID').format(tank.value)} Kg`);
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

    // ====================================================
    // ============ FUNGSI BARU UNTUK SNAPSHOT ============
    // ====================================================
    function renderGroupSnapshots(summaryData, endDate) {
        const container = $('#groupSnapshotContainer');
        const title = $('#snapshotTitle');
        container.empty(); // Kosongkan kontainer

        // Update judul dengan tanggal akhir yang relevan
        const formattedDate = new Date(endDate + 'T00:00:00').toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        title.text(`Ringkasan Stok Grup (Snapshot per ${formattedDate})`);

        if (Object.keys(summaryData).length === 0) {
            container.html('<div class="p-4 text-center text-slate-400 col-span-full">Tidak ada data ringkasan untuk tanggal ini.</div>');
            return;
        }

        // Urutkan grup sesuai urutan di groupConfig untuk konsistensi
        const sortedGroups = Object.keys(groupConfig);

        sortedGroups.forEach(groupName => {
            if (summaryData[groupName] !== undefined) {
                const totalKg = summaryData[groupName];
                const config = groupConfig[groupName];

                const cardHtml = `
                    <div class="bg-white p-3 rounded-lg border-l-4 shadow-sm" style="border-color: ${config.border};">
                        <div class="flex items-center justify-between mb-1">
                            <p class="font-bold text-slate-700 text-sm">${groupName}</p>
                            <span class="text-lg">${config.icon}</span>
                        </div>
                        <p class="font-mono font-bold text-emerald-600 text-xl">
                            ${new Intl.NumberFormat('id-ID').format(totalKg.toFixed(0))}
                            <span class="text-xs text-slate-500 font-sans font-medium">Kg</span>
                        </p>
                    </div>
                `;
                container.append(cardHtml);
            }
        });
    }

    // Fungsi untuk update tabel (tetap sama)
    function updateProductionTable(tableData) {
        const tableBody = $('#productionTanksBody');
        tableBody.empty();
        if(!tableData || tableData.length === 0) {
            tableBody.html(`<tr><td colspan="3" class="p-8 text-center text-slate-400">Tidak ada data.</td></tr>`);
            return;
        }
        tableData.forEach(row => {
            const statusColors = { 'Holding': 'bg-slate-100 text-slate-600 border-slate-200', 'Process': 'bg-blue-100 text-blue-700 border-blue-200', 'Cooling': 'bg-teal-100 text-teal-700 border-teal-200', 'Storage': 'bg-orange-100 text-orange-700 border-orange-200', 'Heating': 'bg-red-100 text-red-700 border-red-200', 'Other': 'bg-gray-100 text-gray-600 border-gray-200' };
            const statusBadge = `<span class="px-2 py-0.5 rounded text-xs border ${statusColors[row.status] || statusColors['Other']}">${row.status}</span>`;
            tableBody.append(`<tr class="hover:bg-emerald-50/50 transition"><td class="px-6 py-3 font-medium text-slate-700">${row.name}</td><td class="px-6 py-3 text-right font-mono text-slate-600">${row.capacity_kg}</td><td class="px-6 py-3 text-center">${statusBadge}</td></tr>`);
        });
    }

    // Fungsi utama untuk filter via AJAX
    function applyRefineryFilter() {
        const btn = $('#btnApplyRefineryFilter'), badge = $('#filterStatusBadge');
        const data = { start_date: $('#dateStartFilter').val(), end_date: $('#dateEndFilter').val() };
        if (!data.start_date || !data.end_date) { alert('Silakan pilih rentang tanggal.'); return; }

        btn.prop('disabled', true).html('Memuat...');
        badge.text("Memuat...");

        $.ajax({
            url: '{{ route("oil.getRefineryData") }}', type: 'GET', data: data,
            success: function(res) {
                chartDetailData = res.chartDetailData;
                renderRefineryLineChart(res.chartDetailData);
                updateProductionTable(res.tableData);
                
                // PANGGIL FUNGSI BARU DI SINI
                renderGroupSnapshots(res.summaryData, data.end_date);

                badge.text("Data Dimuat").attr('class', 'bg-white/20 text-white text-xs px-2 py-1 rounded');
            },
            error: function() { 
                alert('Gagal memuat data refinery.'); 
                $('#productionTanksBody').html(`<tr><td colspan="3" class="p-8 text-center text-red-500">Gagal memuat data.</td></tr>`); 
                $('#groupSnapshotContainer').html('<div class="p-4 text-center text-red-500 col-span-full">Gagal memuat ringkasan.</div>');
            },
            complete: function() { btn.prop('disabled', false).html('Terapkan Filter'); }
        });
    }
    
    // Fungsi untuk memfilter visibilitas garis di chart
    function filterChartVisibility() {
        const selectedGroup = $('#groupSelector').val();
        if (window.refineryLineChartInstance) {
            window.refineryLineChartInstance.data.datasets.forEach(dataset => {
                dataset.hidden = !(selectedGroup === 'SUMMARY' || dataset.label === selectedGroup);
            });
            window.refineryLineChartInstance.update();
        }
    }

    // Event Listeners
    $('#btnApplyRefineryFilter').on('click', applyRefineryFilter);
    $('#groupSelector').on('change', filterChartVisibility);

    // Inisialisasi
    const today = new Date(), lastWeek = new Date();
    lastWeek.setDate(today.getDate() - 6);
    $('#dateEndFilter').val(today.toISOString().split('T')[0]);
    $('#dateStartFilter').val(lastWeek.toISOString().split('T')[0]);
    applyRefineryFilter();
});
</script>