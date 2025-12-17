<div class="w-full font-sans">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 w-full">

        <div class="lg:col-span-7 flex flex-col h-full">
            <div class="card rounded-xl shadow-md border border-slate-200 flex flex-col h-[650px]">
                <div class="rounded-t-xl bg-indigo-500 px-6 py-4 flex justify-between items-center shrink-0">
                    <h4 class="text-white font-semibold text-lg flex items-center gap-2"><i
                            class="mdi mdi-bottle-tonic-outline"></i> Data Batch Refinery</h4>
                    <span id="totalTankBadge" class="card/20 text-white text-xs px-2 py-1 rounded">Loading...</span>
                </div>
                <div class="overflow-auto flex-grow">
                    <table class="w-full text-left border-collapse">
                        <thead class="text-[10px] text-slate-500 uppercase card sticky top-0 z-10 shadow-sm">
                            <tr>
                                <th class="px-3 py-3 font-bold border-b">Tank Code</th>
                                <th class="px-3 py-3 font-bold border-b text-right">Capacity (Kg)</th>
                                <th class="px-3 py-3 font-bold border-b">Oil Code</th>
                                <th class="px-3 py-3 font-bold border-b">Description</th>
                                <th class="px-3 py-3 font-bold border-b text-center">Gauge Board (Meter)</th>
                                <th class="px-3 py-3 font-bold border-b text-center">Temperature (°C)</th>
                                <th class="px-3 py-3 font-bold border-b text-right">Current Value (Kg)</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody" class="divide-y divide-slate-100 text-xs text-slate-700">
                            <tr>
                                <td colspan="7" class="p-8 text-center text-slate-400">Pilih filter dan klik tombol
                                    untuk menampilkan data.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="lg:col-span-5 flex flex-col gap-6 w-full">
            <div class="card rounded-xl shadow-sm border border-slate-200 p-5 w-full">
                <div class="flex flex-col sm:flex-row gap-4 items-end w-full">
                    <div class="w-full">
                        <label for="dateStart" class="mb-1 text-[10px] font-bold text-slate-500 uppercase">Tanggal
                            Mulai</label>
                        <input type="date" id="dateStart" class="w-full card border-slate-300 text-sm rounded-lg p-2.5">
                    </div>
                    <div class="w-full">
                        <label for="dateEnd" class="mb-1 text-[10px] font-bold text-slate-500 uppercase">Tanggal
                            Akhir</label>
                        <input type="date" id="dateEnd" class="w-full card border-slate-300 text-sm rounded-lg p-2.5">
                    </div>
                    <div class="w-full">
                        <label for="globalTankSelector"
                            class="mb-1 text-[10px] font-bold text-slate-500 uppercase">Pilih Tangki</label>
                        <select id="globalTankSelector" class="w-full card border-slate-300 text-sm rounded-lg p-2.5">
                            <option value="ALL">🔵 TAMPILKAN SEMUA</option>
                            @foreach($tanks as $tank)
                                <option value="{{ $tank->id }}">{{ $tank->tank_code }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <button type="button" id="btnApplyFilter"
                class="w-full sm:w-auto text-white bg-indigo-600 hover:bg-indigo-700 font-medium rounded-lg text-sm px-5 py-2.5 flex items-center justify-center gap-2">
                <i class="mdi mdi-filter-variant"></i>Filter
            </button>
            <div class="card rounded-xl shadow-md border border-slate-200 flex-grow w-full h-[550px] flex flex-col">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h5 class="font-bold text-slate-700">📈 Tren Stok Harian (Kg)</h5>
                </div>
                <div class="relative w-full flex-grow p-4"><canvas id="tankYardBDTChart"></canvas></div>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {
        const ctx = document.getElementById('tankYardBDTChart');
        if (!ctx) return;
        window.tankChartInstance = new Chart(ctx, { type: 'line', data: { labels: [], datasets: [] }, options: { responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false }, plugins: { legend: { position: 'bottom' }, tooltip: { callbacks: { label: (c) => `${c.dataset.label}: ${new Intl.NumberFormat('id-ID').format(c.parsed.y)} Kg` } } }, scales: { y: { ticks: { callback: (v) => (v / 1000) + 'K' } } } } });

        function updateTable(data) {
            const tableBody = $('#tableBody');
            tableBody.empty();
            if (data.length === 0) { tableBody.html(`<tr><td colspan="7" class="p-8 text-center text-slate-400">Tidak ada data untuk filter yang dipilih.</td></tr>`); return; }
            data.forEach(row => {
                tableBody.append(`
                    <tr class="hover:bg-indigo-50">
                        <td class="px-3 py-3 font-bold">${row.tank_code}</td>
                        <td class="px-3 py-3 text-right font-mono">${row.capacity_kg}</td>
                        <td class="px-3 py-3">${row.oil_code}</td>
                        <td class="px-3 py-3">${row.description}</td>
                        <td class="px-3 py-3 text-center">${row.gauge_board_meter}</td>
                        <td class="px-3 py-3 text-center">${row.temperature_celsius}</td>
                        <td class="px-3 py-3 font-bold text-right">${row.current_value_kg}</td>
                    </tr>`);
            });
        }

        function applyDashboardFilter() {
            const btn = $('#btnApplyFilter'), badge = $('#totalTankBadge');
            const data = { tank_id: $('#globalTankSelector').val(), start_date: $('#dateStart').val(), end_date: $('#dateEnd').val() };
            if (!data.start_date || !data.end_date) { alert('Silakan pilih rentang tanggal.'); return; }
            btn.prop('disabled', true).html('Memuat...');
            badge.text("Memuat...");

            $.ajax({
                url: '{{ route("oil.getTankData") }}', type: 'GET', data: data,
                success: function (res) {
                    window.tankChartInstance.data.labels = res.labels;
                    window.tankChartInstance.data.datasets = res.datasets;
                    window.tankChartInstance.update();
                    updateTable(res.tableData);
                    badge.text(data.tank_id === 'ALL' ? 'Semua Tangki' : 'Difilter');
                },
                error: function () { alert('Gagal memuat data. Periksa koneksi atau coba lagi.'); updateTable([]); },
                complete: function () { btn.prop('disabled', false).html('<i class="mdi mdi-filter-variant"></i>Filter'); }
            });
        }

        $('#btnApplyFilter').on('click', applyDashboardFilter);

        // Inisialisasi tanggal default dan muat data awal
        const today = new Date(), lastWeek = new Date();
        lastWeek.setDate(today.getDate() - 6);
        $('#dateEnd').val(today.toISOString().split('T')[0]);
        $('#dateStart').val(lastWeek.toISOString().split('T')[0]);
        applyDashboardFilter();
    });
</script>