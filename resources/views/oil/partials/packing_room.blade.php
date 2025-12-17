<div class="w-full font-sans">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
        <!-- KOLOM KIRI: TABEL DATA -->
        <div class="card rounded-xl shadow-lg overflow-hidden border border-slate-100 h-full flex flex-col">
            <div class="bg-orange-500 px-6 py-4 flex justify-between items-center">
                <h4 class="text-white font-semibold text-lg flex items-center gap-2"><i class="mdi mdi-package-variant"></i> Stok Packing</h4>
                <span id="packingTotal" class="card/20 text-white text-xs px-2 py-1 rounded"></span>
            </div>
            <div class="overflow-x-auto max-h-[600px] overflow-y-auto custom-scrollbar">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-slate-500 uppercase card sticky top-0 z-10 shadow-sm">
                        <tr>
                            <th class="px-6 py-3 font-bold">Tank Code</th>
                            <th class="px-6 py-3 font-bold text-right">Capacity (Kg)</th>
                            <th class="px-6 py-3 font-bold text-center">Current Status</th>
                        </tr>
                    </thead>
                    <tbody id="packingTanksBody" class="divide-y divide-slate-100"></tbody>
                </table>
            </div>
        </div>

        <!-- KOLOM KANAN: CONTROLLER & CHART -->
        <div class="flex flex-col gap-6">
            <div class="card rounded-xl shadow-lg border border-slate-100 p-6">
                <h5 class="text-lg font-bold text-slate-700 mb-4 border-l-4 border-orange-500 pl-3">📦 Kontrol Packing</h5>
                {{-- FILTER RENTANG TANGGAL BARU --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="packingDateStart" class="block mb-1 text-xs font-semibold text-slate-500 uppercase">Tanggal Mulai</label>
                        <input type="date" id="packingDateStart" class="w-full card border-slate-200 text-sm rounded-lg p-2.5">
                    </div>
                    <div>
                        <label for="packingDateEnd" class="block mb-1 text-xs font-semibold text-slate-500 uppercase">Tanggal Akhir</label>
                        <input type="date" id="packingDateEnd" class="w-full card border-slate-200 text-sm rounded-lg p-2.5">
                    </div>
                </div>
                {{-- Tombol untuk menerapkan filter --}}
                <button type="button" id="btnApplyPackingFilter" class="w-full text-white bg-orange-500 hover:bg-orange-600 font-medium rounded-lg text-sm px-5 py-2.5 shadow-md">Tampilkan Tren</button>
            </div>
            <div class="card rounded-xl shadow-lg border border-slate-100 flex-grow">
                <div class="px-6 py-4 border-b border-slate-100 card flex justify-between items-center">
                    <h5 class="font-bold text-slate-700">Tren Volume Harian</h5>
                    <span class="text-xs text-orange-600 bg-orange-100 px-2 py-0.5 rounded font-bold">Max: 10,000 Kg</span>
                </div>
                <div class="p-4 h-[400px] w-full"><canvas id="packingRoomChart"></canvas></div>
            </div>
        </div>
    </div>
</div>

<script>
$(function () {
    let packingChart = null;
    const statusBadges = {
        'READY': '<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">Ready to Pack</span>',
        'FILLING': '<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700 animate-pulse">Filling...</span>',
        'STANDBY': '<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700">Standby</span>',
        'MAINTENANCE': '<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-200">Maintenance</span>'
    };

    function renderPackingChart(chartData) {
        const ctx = document.getElementById('packingRoomChart');
        if (!ctx) return;
        if (packingChart) packingChart.destroy();
        packingChart = new Chart(ctx, {
            type: 'line',
            data: { 
                labels: chartData.labels.map(d => new Date(d).toLocaleDateString('id-ID', {day:'numeric', month:'short'})),
                datasets: chartData.datasets
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false }, // Disembunyikan agar tidak terlalu ramai
                    tooltip: { callbacks: { label: (c) => ` ${c.dataset.label}: ${new Intl.NumberFormat('id-ID').format(c.raw)} Kg` }}
                },
                scales: { 
                    y: { 
                        beginAtZero: true, 
                        max: 11000,
                        ticks: { callback: (v) => (v/1000) + 'K' }
                    }, 
                    x: { grid: { display: false }}
                }
            }
        });
    }

    function updatePackingTable(tableData) {
        const tableBody = $('#packingTanksBody');
        tableBody.empty();
        $('#packingTotal').text(`${tableData.length} Units`);
        tableData.forEach(row => {
            tableBody.append(`
                <tr class="hover:bg-orange-50/50 transition">
                    <td class="px-6 py-4 font-semibold text-slate-700">${row.tank_code}</td>
                    <td class="px-6 py-4 text-right font-mono text-slate-600">${row.capacity_kg}</td>
                    <td class="px-6 py-4 text-center">${statusBadges[row.status] || ''}</td>
                </tr>`);
        });
    }

    function applyPackingFilter() {
        const btn = $('#btnApplyPackingFilter');
        const data = { 
            start_date: $('#packingDateStart').val(), 
            end_date: $('#packingDateEnd').val() 
        };
        if (!data.start_date || !data.end_date) { 
            alert('Silakan pilih rentang tanggal.'); 
            return; 
        }
        btn.prop('disabled', true).html('Memuat...');

        $.ajax({
            url: '{{ route("oil.getPackingData") }}',
            type: 'GET', data: data,
            success: function(res) {
                updatePackingTable(res.tableData);
                renderPackingChart(res.chartData);
            },
            error: function() { alert('Gagal memuat data Packing Room.'); },
            complete: function() { btn.prop('disabled', false).html('Tampilkan Tren'); }
        });
    }

    $('#btnApplyPackingFilter').on('click', applyPackingFilter);

    const today = new Date(), lastWeek = new Date();
    lastWeek.setDate(today.getDate() - 6);
    $('#packingDateEnd').val(today.toISOString().split('T')[0]);
    $('#packingDateStart').val(lastWeek.toISOString().split('T')[0]);
    applyPackingFilter();
});
</script>