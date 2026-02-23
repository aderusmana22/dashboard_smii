<div class="w-full font-sans">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
        <!-- KOLOM KIRI: TABEL DATA -->
        <div class="card rounded-xl shadow-lg overflow-hidden border border-slate-100 h-full flex flex-col">
            <div class="bg-indigo-600 px-6 py-4 flex justify-between items-center">
                <h4 class="text-white font-semibold text-xl flex items-center gap-2"><i class="mdi mdi-blender-software"></i> Data Tangki Blending</h4>
                <span id="fatBlendTotal" class="card/20 text-white text-base px-2 py-1 rounded"></span>
            </div>
            <div class="overflow-x-auto max-h-[600px] overflow-y-auto custom-scrollbar">
                <table class="w-full text-base text-left">
                    <thead class="text-base text-slate-500 uppercase card sticky top-0 z-10 shadow-sm">
                        <tr>
                            <th class="px-6 py-3 font-bold">Tank Code</th>
                            <th class="px-6 py-3 font-bold text-right">Capacity (Kg)</th>
                            <th class="px-6 py-3 font-bold text-center">Source Info</th>
                        </tr>
                    </thead>
                    <tbody id="fatBlendTanksBody" class="divide-y divide-slate-100"></tbody>
                </table>
            </div>
        </div>

        <!-- KOLOM KANAN: CONTROLLER & CHART -->
        <div class="flex flex-col gap-6">
            <div class="card rounded-xl shadow-lg border border-slate-100 p-6">
                <h5 class="text-xl font-bold text-slate-700 mb-4 border-l-4 border-violet-500 pl-3">🎛️ Panel Kontrol</h5>
                
                {{-- FILTER RENTANG TANGGAL BARU --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="fatBlendDateStart" class="block mb-1 text-base font-semibold text-slate-500 uppercase">Tanggal Mulai</label>
                        <input type="date" id="fatBlendDateStart" class="w-full card border-slate-200 text-base rounded-lg p-2.5">
                    </div>
                    <div>
                        <label for="fatBlendDateEnd" class="block mb-1 text-base font-semibold text-slate-500 uppercase">Tanggal Akhir</label>
                        <input type="date" id="fatBlendDateEnd" class="w-full card border-slate-200 text-base rounded-lg p-2.5">
                    </div>
                </div>

                {{-- FILTER DROPDOWN LAMA DIHAPUS, GANTI DENGAN TOMBOL --}}
                <button type="button" id="btnApplyFatBlendFilter" class="w-full text-white bg-indigo-600 hover:bg-indigo-700 font-medium rounded-lg text-lg px-5 py-2.5 shadow-md">Tampilkan Tren</button>
            </div>
            <div class="card rounded-xl shadow-lg border border-slate-100 flex-grow">
                <div class="px-6 py-4 border-b border-slate-100 card flex justify-between items-center">
                    <h5 class="font-bold text-slate-700 text-xl">Tren Level Tangki Harian</h5>
                    <span class="text-base text-slate-400 font-mono">Max: 25,000 Kg</span>
                </div>
                <div class="p-4 h-[350px] w-full relative"><canvas id="fatBlendTankChart"></canvas></div>
            </div>
        </div>
    </div>
</div>

<script>
$(function () {
    let chartInstance = null;

    // FUNGSI BARU UNTUK MERENDER LINE CHART
    function renderFatBlendLineChart(chartData) {
        const ctx = document.getElementById('fatBlendTankChart');
        if(!ctx) return;
        if (chartInstance) chartInstance.destroy();

        chartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels.map(d => new Date(d).toLocaleDateString('id-ID', {day:'numeric', month:'short'})),
                datasets: chartData.datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                scales: { 
                    y: { 
                        beginAtZero: true, 
                        max: 27000, 
                        ticks: { callback: (v) => (v/1000) + 'K' }
                    }, 
                    x: { grid: { display: false }}
                }, 
                plugins: { 
                    legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, padding: 15 }}, 
                    tooltip: { callbacks: { label: (c) => ` ${c.dataset.label}: ${new Intl.NumberFormat('id-ID').format(c.raw)} Kg` }}
                }
            }
        });
    }

    function updateFatBlendTable(tableData) {
        const tableBody = $('#fatBlendTanksBody');
        tableBody.empty();
        $('#fatBlendTotal').text(`Total: ${tableData.length} Tangki`);
        const sourceBadges = {
            'MANUAL': `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-base font-medium bg-amber-100 text-amber-800 border border-amber-200"><i class="mdi mdi-pencil"></i> Manual</span>`,
            'PLC': `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-base font-medium bg-blue-100 text-blue-800 border border-blue-200"><i class="mdi mdi-robot-industrial"></i> PLC</span>`,
            'WAITING': `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-base font-medium bg-gray-100 text-gray-600 border border-gray-200">Waiting</span>`
        };
        tableData.forEach(row => {
            tableBody.append(`
                <tr class="hover:bg-violet-50/50 transition">
                    <td class="px-6 py-4 font-semibold text-slate-700">${row.name}</td>
                    <td class="px-6 py-4 text-right font-mono text-slate-600">${row.capacity_kg}</td>
                    <td class="px-6 py-4 text-center">${sourceBadges[row.source_type] || sourceBadges['WAITING']}</td>
                </tr>`);
        });
    }

    function applyFatBlendFilter() {
        const btn = $('#btnApplyFatBlendFilter');
        const data = { 
            start_date: $('#fatBlendDateStart').val(), 
            end_date: $('#fatBlendDateEnd').val() 
        };
        if (!data.start_date || !data.end_date) { 
            alert('Silakan pilih rentang tanggal.'); 
            return; 
        }

        btn.prop('disabled', true).html('Memuat...');
        $.ajax({
            url: '{{ route("oil.getFatBlendData") }}',
            type: 'GET', data: data,
            success: function(res) {
                updateFatBlendTable(res.tableData);
                renderFatBlendLineChart(res.chartData);
            },
            error: function() { alert('Gagal memuat data Fat Blend.'); },
            complete: function() { btn.prop('disabled', false).html('Tampilkan Tren'); }
        });
    }

    $('#btnApplyFatBlendFilter').on('click', applyFatBlendFilter);

    // Inisialisasi tanggal default (7 hari terakhir)
    const today = new Date(), lastWeek = new Date();
    lastWeek.setDate(today.getDate() - 6);
    $('#fatBlendDateEnd').val(today.toISOString().split('T')[0]);
    $('#fatBlendDateStart').val(lastWeek.toISOString().split('T')[0]);
    
    // Langsung muat data saat komponen ditampilkan
    applyFatBlendFilter();
});
</script>