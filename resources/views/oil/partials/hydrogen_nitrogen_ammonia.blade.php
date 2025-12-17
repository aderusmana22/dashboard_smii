<div class="w-full font-sans">
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <!-- Bagian Judul (Tidak ada perubahan) -->
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Utility Gas Monitoring</h2>
        <p class="text-slate-500 text-sm">Pemantauan Stok & Tren Gas Pendukung Produksi</p>
    </div>

    <!-- 
        BAGIAN FILTER YANG DIMODIFIKASI
        - Container utama diubah menjadi flex-col untuk menumpuk item secara vertikal.
        - Diberi w-full sm:w-auto agar responsif.
    -->
    <div class="flex flex-col gap-2 w-full sm:w-auto">
        <!-- Baris 1: Grup untuk input tanggal -->
        <div class="flex gap-3">
            <input type="date" id="gasDateStart" class="w-full card border-slate-200 text-sm rounded-lg p-2.5 shadow-sm" title="Tanggal Mulai Tren">
            <input type="date" id="gasDateEnd" class="w-full card border-slate-200 text-sm rounded-lg p-2.5 shadow-sm" title="Tanggal Akhir Snapshot & Tren">
        </div>
        
        <!-- Baris 2: Tombol -->
        <button id="btnUpdateGasData" class="w-full text-sm bg-yellow-700 text-white px-4 py-2.5 rounded-lg transition shadow-md">Tampilkan Data</button>
    </div>
</div>

    <!-- BAGIAN 1: SNAPSHOT -->
    <h3 class="text-lg font-bold text-slate-600 mb-4 border-l-4 border-slate-400 pl-3">Snapshot Kondisi Terkini</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- 1. HYDROGEN SNAPSHOT -->
        <div class="card rounded-xl shadow-lg border border-slate-100 flex flex-col">
            <div class="bg-gradient-to-r from-red-600 to-rose-600 px-6 py-4"><h5 class="text-white font-bold flex items-center gap-2"><i class="mdi mdi-flash"></i> Hydrogen (H2)</h5></div>
            <div class="p-6 flex-grow"><div id="hydrogenTableContainer" class="overflow-hidden rounded-lg border border-slate-200"></div></div>
        </div>
        <!-- 2. NITROGEN SNAPSHOT -->
        <div class="card rounded-xl shadow-lg border border-slate-100 flex flex-col">
            <div class="bg-gradient-to-r from-blue-600 to-cyan-600 px-6 py-4"><h5 class="text-white font-bold flex items-center gap-2"><i class="mdi mdi-snowflake"></i> Nitrogen (N2)</h5></div>
            <div class="p-6 flex-grow">
                <div class="text-center mb-6"><span class="block text-sm text-slate-500 mb-1">Current Stock</span><div id="nitrogenValue" class="text-5xl font-bold text-blue-600">...</div><span class="text-sm font-medium text-slate-400">Inch Water</span></div>
                <div class="space-y-4"><div class="flex justify-between text-xs font-semibold"><span>0</span><span>Min: 65</span><span>Max: 100</span></div><div class="w-full bg-slate-200 rounded-full h-4 relative overflow-hidden"><div id="nitrogenProgressBar" class="bg-blue-500 h-4 rounded-full" style="width: 0%"></div><div class="absolute inset-y-0 w-0.5 bg-red-500 z-10" style="left: 65%"></div></div><div id="nitrogenAlertContainer"></div></div>
            </div>
        </div>
        <!-- 3. AMMONIA SNAPSHOT -->
        <div class="card rounded-xl shadow-lg border border-slate-100 flex flex-col">
            <div class="bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-4"><h5 class="text-white font-bold flex items-center gap-2"><i class="mdi mdi-test-tube"></i> Ammonia (NH3)</h5></div>
            <div class="p-6 flex-grow flex flex-col">
                <div id="ammoniaStatsContainer" class="grid grid-cols-2 gap-4 mb-4"></div>
                <div class="flex-grow relative h-[150px] w-full"><canvas id="ammoniaDoughnutChart"></canvas></div>
                <div id="ammoniaTotal" class="mt-2 text-center text-xs text-slate-400"></div>
            </div>
        </div>
    </div>
    
    <!-- BAGIAN 2: GRAFIK TREN -->
    <h3 class="text-lg font-bold text-slate-600 mb-4 border-l-4 border-slate-400 pl-3">Grafik Tren Harian</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="card rounded-xl shadow-lg border border-slate-100 p-4 h-[300px]"><canvas id="hydrogenTrendChart"></canvas></div>
        <div class="card rounded-xl shadow-lg border border-slate-100 p-4 h-[300px]"><canvas id="nitrogenTrendChart"></canvas></div>
        <div class="card rounded-xl shadow-lg border border-slate-100 p-4 h-[300px]"><canvas id="ammoniaTrendChart"></canvas></div>
    </div>
</div>

<script>
$(function() {
    let hydrogenTrendChart, nitrogenTrendChart, ammoniaTrendChart, ammoniaDoughnutChart;

    function updateSnapshot(data) {
        const h2Table = $('#hydrogenTableContainer');
        let h2Html = `<table class="w-full text-sm"><thead class="card text-xs uppercase"><tr><th class="px-4 py-2">Torpedo</th><th class="px-4 py-2 text-right">Pressure</th></tr></thead><tbody class="divide-y">`;
        if (data.hydrogen) {
            data.hydrogen.forEach(item => {
                const val = item.value > 0 ? `<td class="px-4 py-2 text-right font-mono text-red-600 font-bold">${item.value} Bar</td>` : `<td class="px-4 py-2 text-right text-slate-400">Empty</td>`;
                h2Html += `<tr><td class="px-4 py-2">${item.unit_name.replace('Torpedo ', '')}</td>${val}</tr>`;
            });
        }
        h2Table.html(h2Html + `</tbody></table>`);

        if (data.nitrogen) {
            const n2Val = parseFloat(data.nitrogen.value);
            $('#nitrogenValue').text(n2Val);
            $('#nitrogenProgressBar').css('width', `${n2Val}%`);
            const n2Alert = $('#nitrogenAlertContainer');
            if (n2Val >= 65) n2Alert.html(`<div class="bg-green-50 p-3 rounded-lg text-center"><span class="text-sm font-bold text-green-700">Kondisi Aman</span></div>`);
            else n2Alert.html(`<div class="bg-red-50 p-3 rounded-lg text-center"><span class="text-sm font-bold text-red-700">Kondisi Kritis</span></div>`);
        }
        
        const full = data.ammonia?.find(i => i.unit_name === 'Full Cylinders')?.value || 0;
        const empty = data.ammonia?.find(i => i.unit_name === 'Empty Cylinders')?.value || 0;
        $('#ammoniaStatsContainer').html(`<div class="bg-emerald-50 p-3 text-center rounded-lg"><span class="block text-2xl font-bold text-emerald-700">${full}</span><span class="text-xs uppercase">Full</span></div><div class="bg-slate-100 p-3 text-center rounded-lg"><span class="block text-2xl font-bold text-slate-600">${empty}</span><span class="text-xs uppercase">Empty</span></div>`);
        $('#ammoniaTotal').text(`Total: ${full + empty} Cylinders`);
        const ctxNH3Doughnut = document.getElementById('ammoniaDoughnutChart');
        if (ctxNH3Doughnut) {
            if(ammoniaDoughnutChart) ammoniaDoughnutChart.destroy();
            ammoniaDoughnutChart = new Chart(ctxNH3Doughnut, { type: 'doughnut', data: { labels: ['Full', 'Empty'], datasets: [{ data: [full, empty], backgroundColor: ['#10b981', '#e2e8f0'], borderWidth: 0 }]}, options: { responsive: true, maintainAspectRatio: false, cutout: '60%', plugins: { legend: { display: false }}}});
        }
    }

    const createLineChart = (canvasId, chartInstance, data, yLabel, customOptions = {}) => {
        const ctx = document.getElementById(canvasId);
        if(!ctx) return null;
        if(chartInstance) chartInstance.destroy();
        return new Chart(ctx, {
            type: 'line', data: { labels: data.labels.map(d => new Date(d).toLocaleDateString('id-ID', {day:'numeric', month:'short'})), datasets: data.datasets },
            options: { responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false }, plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 }}}, scales: { y: { beginAtZero: true, title: { display: true, text: yLabel }, ...customOptions.y }, x: { grid: { display: false }, ...customOptions.x }}}
        });
    };

    function updateTrendCharts(data) {
        const trendOptions = { plugins: { legend: { display: false }}, scales: { y: { beginAtZero: false }, x: { grid: { display: false }}}};
        hydrogenTrendChart = createLineChart('hydrogenTrendChart', hydrogenTrendChart, { labels: data.labels, datasets: data.hydrogen.map((h, i) => ({ ...h, borderColor: i === 0 ? '#ef4444' : '#fca5a5', tension: 0.3, pointRadius: 0 }))}, 'Bar', trendOptions);
        nitrogenTrendChart = createLineChart('nitrogenTrendChart', nitrogenTrendChart, { labels: data.labels, datasets: data.nitrogen.map(n => ({ ...n, borderColor: '#3b82f6', tension: 0.3, pointRadius: 0 }))}, 'Inch Water', trendOptions);
        ammoniaTrendChart = createLineChart('ammoniaTrendChart', ammoniaTrendChart, { labels: data.labels, datasets: data.ammonia.map((a, i) => ({ ...a, label: a.label.replace(' Cylinders', ''), borderColor: i === 0 ? '#10b981' : '#6ee7b7', tension: 0.3, pointRadius: 0 }))}, 'Units', { ...trendOptions, y: {stacked: true }});
    }

    function applyGasFilter() {
        const btn = $('#btnUpdateGasData');
        // --- PERUBAHAN DI SINI: MENGAMBIL start_date dan end_date ---
        const data = { 
            start_date: $('#gasDateStart').val(),
            end_date: $('#gasDateEnd').val()
        };
        if (!data.start_date || !data.end_date) { alert('Silakan pilih rentang tanggal.'); return; }
        // -----------------------------------------------------------
        btn.prop('disabled', true).html('Memuat...');

        $.ajax({
            url: '{{ route("oil.getUtilityGasData") }}', type: 'GET', data: data,
            success: function(res) {
                // --- PERUBAHAN DI SINI: MEMPROSES DUA SET DATA ---
                updateSnapshot(res.snapshot);
                updateTrendCharts(res.trend);
                // ----------------------------------------------------
            },
            error: function() { alert('Gagal memuat data gas.'); },
            complete: function() { btn.prop('disabled', false).html('Tampilkan Data'); }
        });
    }

    $('#btnUpdateGasData').on('click', applyGasFilter);
    const today = new Date(), lastWeek = new Date();
    lastWeek.setDate(today.getDate() - 6);
    // --- PERUBAHAN DI SINI: MENGATUR DUA INPUT TANGGAL ---
    $('#gasDateEnd').val(today.toISOString().split('T')[0]);
    $('#gasDateStart').val(lastWeek.toISOString().split('T')[0]);
    // ----------------------------------------------------
    applyGasFilter();
});
</script>