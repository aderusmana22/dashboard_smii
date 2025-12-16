<div class="w-full font-sans">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
        <!-- KOLOM KIRI: TABEL DATA -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-slate-100 h-full flex flex-col">
            <div class="bg-blue-400 px-6 py-4 flex justify-between items-center">
                <h4 class="text-white font-semibold text-lg flex items-center gap-2"><i class="mdi mdi-format-list-numbered"></i> Inventory List</h4>
                <span id="yard1tTotal" class="bg-white/20 text-white text-xs px-2 py-1 rounded"></span>
            </div>
            <div class="overflow-x-auto max-h-[650px] overflow-y-auto custom-scrollbar">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-slate-500 uppercase bg-slate-50 sticky top-0 z-10 shadow-sm">
                        <tr>
                            <th class="px-6 py-3 font-bold">Tank Code</th>
                            <th class="px-6 py-3 font-bold text-right">Capacity (Kg)</th>
                            <th class="px-6 py-3 font-bold">Oil Code</th>
                            <th class="px-6 py-3 font-bold">Description</th>
                        </tr>
                    </thead>
                    <tbody id="yard1tTanksBody" class="divide-y divide-slate-100"></tbody>
                </table>
            </div>
        </div>

        <!-- KOLOM KANAN: FILTER & CHART -->
        <div class="flex flex-col gap-6">
            <div class="bg-white rounded-xl shadow-lg border border-slate-100 p-6">
                <h5 class="text-lg font-bold text-slate-700 mb-4 border-l-4 border-cyan-500 pl-3">🔍 Filter Inventory</h5>
                {{-- FILTER RENTANG TANGGAL BARU --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="yard1tDateStart" class="block mb-1 text-xs font-semibold text-slate-500 uppercase">Tanggal Mulai</label>
                        <input type="date" id="yard1tDateStart" class="w-full bg-slate-50 border-slate-200 text-sm rounded-lg p-2.5">
                    </div>
                     <div>
                        <label for="yard1tDateEnd" class="block mb-1 text-xs font-semibold text-slate-500 uppercase">Tanggal Akhir</label>
                        <input type="date" id="yard1tDateEnd" class="w-full bg-slate-50 border-slate-200 text-sm rounded-lg p-2.5">
                    </div>
                </div>
                <button type="button" id="btnApplyYard1tFilter" class="w-full text-white bg-blue-400 hover:bg-blue-600 font-medium rounded-lg text-sm px-5 py-2.5 shadow-md">Tampilkan Tren</button>
            </div>
            <div class="bg-white rounded-xl shadow-lg border border-slate-100 flex-grow">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50"><h5 class="font-bold text-slate-700">Tren Total Stok Harian per Tipe Minyak</h5></div>
                <div class="p-6 h-[400px] w-full relative"><canvas id="tankYard1TChart"></canvas></div>
            </div>
        </div>
    </div>
</div>

<script>
$(function () {
    let yardChart = null;
    const oilCategories = ['PSS', 'PO / PO (T)', 'PKO / CNO', 'SBO', 'Others'];
    const oilTypeColors = {
        'PSS': '#3b82f6', 'PO / PO (T)': '#10b981', 'PKO / CNO': '#f59e0b',
        'SBO': '#eab308', 'Others': '#64748b',
    };

    function renderYard1tChart(chartData) {
        const ctx = document.getElementById('tankYard1TChart');
        if (!ctx) return;
        if (yardChart) yardChart.destroy();
        
        const labels = Object.keys(chartData).sort();
        const datasets = oilCategories.map(category => ({
            label: category,
            data: labels.map(date => chartData[date]?.[category] || 0),
            backgroundColor: oilTypeColors[category],
        }));

        yardChart = new Chart(ctx, {
            type: 'bar',
            data: { 
                labels: labels.map(d => new Date(d).toLocaleDateString('id-ID', {day:'numeric', month:'short'})),
                datasets: datasets 
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' }, tooltip: { mode: 'index' }},
                scales: {
                    y: { stacked: true, ticks: { callback: (v) => (v / 1000) + 'K' }},
                    x: { stacked: true, grid: { display: false }}
                }
            }
        });
    }

    function updateYard1tTable(tableData) {
        const tableBody = $('#yard1tTanksBody');
        tableBody.empty();
        $('#yard1tTotal').text(`${tableData.length} Tanks`);
        tableData.forEach(row => {
            const isAvailable = row.description === 'Available';
            const rowClass = isAvailable ? 'bg-slate-50' : 'hover:bg-cyan-50/50';
            const textClass = isAvailable ? 'text-slate-400' : 'text-slate-700';
            const descColors = {'PSS': 'bg-blue-100 text-blue-700', 'PO /SG': 'bg-green-100 text-green-700', 'PSS /SG': 'bg-indigo-100 text-indigo-700', 'PKO': 'bg-amber-100 text-amber-700', 'HCNO': 'bg-red-100 text-red-700', 'SBO': 'bg-yellow-100 text-yellow-700', 'RBD PKS': 'bg-orange-100 text-orange-700', 'PO (T)': 'bg-emerald-100 text-emerald-700', 'CNO': 'bg-teal-100 text-teal-700', 'PE (T)': 'bg-purple-100 text-purple-700'};
            const descBadge = isAvailable ? `<span class="px-2 py-1 rounded text-xs font-medium bg-slate-200 text-slate-500 border border-slate-300">${row.description}</span>` : `<span class="px-2 py-1 rounded text-xs font-bold ${descColors[row.description] || 'bg-gray-100'}">${row.description}</span>`;
            tableBody.append(`<tr class="${rowClass} transition"><td class="px-6 py-3 font-semibold ${textClass}">${row.tank_code}</td><td class="px-6 py-3 text-right font-mono ${textClass}">${row.capacity_kg}</td><td class="px-6 py-3 ${textClass}">${row.oil_code || '-'}</td><td class="px-6 py-3">${descBadge}</td></tr>`);
        });
    }

    function applyYard1tFilter() {
        const btn = $('#btnApplyYard1tFilter');
        const data = { 
            start_date: $('#yard1tDateStart').val(), 
            end_date: $('#yard1tDateEnd').val() 
        };
        if (!data.start_date || !data.end_date) { alert('Silakan pilih rentang tanggal.'); return; }

        btn.prop('disabled', true).html('Memuat...');
        $.ajax({
            url: '{{ route("oil.getYard1tData") }}',
            type: 'GET', data: data,
            success: function(res) {
                updateYard1tTable(res.tableData);
                renderYard1tChart(res.chartData);
            },
            error: function() { alert('Gagal memuat data Tank Yard 1T.'); },
            complete: function() { btn.prop('disabled', false).html('Tampilkan Tren'); }
        });
    }

    $('#btnApplyYard1tFilter').on('click', applyYard1tFilter);

    const today = new Date(), lastWeek = new Date();
    lastWeek.setDate(today.getDate() - 6);
    $('#yard1tDateEnd').val(today.toISOString().split('T')[0]);
    $('#yard1tDateStart').val(lastWeek.toISOString().split('T')[0]);
    applyYard1tFilter();
});
</script>