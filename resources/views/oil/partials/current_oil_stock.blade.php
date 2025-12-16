<div class="w-full font-sans">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
        <!-- KOLOM KIRI: TABEL DATA -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-slate-100 h-full flex flex-col">
            <div class="bg-green-700 px-6 py-4 flex justify-between items-center">
                <h4 class="text-white font-semibold text-lg flex items-center gap-2"><i class="mdi mdi-database"></i> Master Data Stock</h4>
                <span id="lastSyncTime" class="bg-white/20 text-white text-xs px-2 py-1 rounded"></span>
            </div>
            <div class="overflow-x-auto max-h-[650px] overflow-y-auto custom-scrollbar">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-slate-500 uppercase bg-slate-50 sticky top-0 z-10 shadow-sm">
                        <tr>
                            <th class="px-6 py-3 font-bold">Oil Code</th>
                            <th class="px-6 py-3 font-bold">Description</th>
                            <th class="px-6 py-3 font-bold text-right">Current Value (Kg)</th>
                        </tr>
                    </thead>
                    <tbody id="currentStockBody" class="divide-y divide-slate-100"></tbody>
                </table>
            </div>
        </div>

        <!-- KOLOM KANAN: CONTROLLER & CHART -->
        <div class="flex flex-col gap-6">
            <div class="bg-white rounded-xl shadow-lg border border-slate-100 p-6">
                <h5 class="text-lg font-bold text-slate-700 mb-4 border-l-4 border-slate-600 pl-3">🔍 Filter Report</h5>
                {{-- FILTER RENTANG TANGGAL BARU --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="stockDateStart" class="block mb-1 text-xs font-semibold text-slate-500 uppercase">Tanggal Mulai</label>
                        <input type="date" id="stockDateStart" class="w-full bg-slate-50 border-slate-200 text-sm rounded-lg p-2.5">
                    </div>
                     <div>
                        <label for="stockDateEnd" class="block mb-1 text-xs font-semibold text-slate-500 uppercase">Tanggal Akhir</label>
                        <input type="date" id="stockDateEnd" class="w-full bg-slate-50 border-slate-200 text-sm rounded-lg p-2.5">
                    </div>
                </div>
                <button type="button" id="btnApplyStockFilter" class="w-full text-white bg-green-700 font-medium rounded-lg text-sm px-5 py-2.5 shadow-md">Tampilkan Tren</button>
            </div>
            <div class="bg-white rounded-xl shadow-lg border border-slate-100 flex-grow">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50"><h5 class="font-bold text-slate-700">Tren Total Stok Harian per Tipe Minyak</h5></div>
                <div class="p-6 h-[400px] w-full relative"><canvas id="currentOilStockChart"></canvas></div>
            </div>
        </div>
    </div>
</div>

<script>
$(function () {
    let stockChart = null;
    const descColors = {
        'PFAD': 'bg-indigo-100 text-indigo-700', 'PO (CPO)': 'bg-emerald-100 text-emerald-700', 'PO (T)': 'bg-emerald-100 text-emerald-700',
        'PSS': 'bg-blue-100 text-blue-700', 'PKO': 'bg-amber-100 text-amber-700',
    };

    function renderStockChart(chartData, allOilCodes) {
        const ctx = document.getElementById('currentOilStockChart');
        if (!ctx) return;
        if (stockChart) stockChart.destroy();
        
        const labels = Object.keys(chartData).sort();
        const datasets = allOilCodes.map(code => {
            return {
                label: code,
                data: labels.map(date => chartData[date]?.[code] || 0),
                backgroundColor: `#${(Math.abs(crc32(code)) % 0xFFFFFF).toString(16).padStart(6, '0')}`,
            };
        });

        stockChart = new Chart(ctx, {
            type: 'bar',
            data: { 
                labels: labels.map(d => new Date(d).toLocaleDateString('id-ID', {day:'numeric', month:'short'})),
                datasets: datasets 
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { mode: 'index' }},
                scales: {
                    y: { stacked: true, ticks: { callback: (v) => (v / 1000) + 'K' }},
                    x: { stacked: true, grid: { display: false }}
                }
            }
        });
    }

    function crc32(str) {
        // Fungsi sederhana untuk menghasilkan hash numerik dari string
        let crc = -1;
        for (let i = 0; i < str.length; i++) {
            crc = (crc >>> 8) ^ crcTable[(crc ^ str.charCodeAt(i)) & 0xFF];
        }
        return (crc ^ -1) >>> 0;
    }
    const crcTable = Array.from({ length: 256 }, (v, i) => {
        let c = i;
        for (let j = 0; j < 8; j++) { c = (c & 1) ? (0xEDB88320 ^ (c >>> 1)) : (c >>> 1); }
        return c;
    });

    function updateStockTable(tableData) {
        const tableBody = $('#currentStockBody');
        tableBody.empty();
        $('#lastSyncTime').text(`Last Sync: ${new Date().toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'})}`);
        tableData.forEach(row => {
            const descBadge = `<span class="px-2 py-1 rounded text-xs font-bold ${descColors[row.description] || 'bg-slate-100 text-slate-600'}">${row.description}</span>`;
            tableBody.append(`<tr class="hover:bg-slate-50 transition"><td class="px-6 py-3 font-mono text-slate-600 font-semibold">${row.oil_code}</td><td class="px-6 py-3">${descBadge}</td><td class="px-6 py-3 text-right font-mono text-slate-700 font-bold">${new Intl.NumberFormat('id-ID').format(row.current_value)}</td></tr>`);
        });
    }

    function applyStockFilter() {
        const btn = $('#btnApplyStockFilter');
        const data = { 
            start_date: $('#stockDateStart').val(), 
            end_date: $('#stockDateEnd').val() 
        };
        if (!data.start_date || !data.end_date) { alert('Silakan pilih rentang tanggal.'); return; }
        btn.prop('disabled', true).html('Memuat...');
        $.ajax({
            url: '{{ route("oil.getCurrentStockData") }}',
            type: 'GET', data: data,
            success: function(res) {
                updateStockTable(res.tableData);
                // Dapatkan semua oil_code unik dari data chart untuk membuat dataset
                const allOilCodes = [...new Set(Object.values(res.chartData).flatMap(Object.keys))];
                renderStockChart(res.chartData, allOilCodes);
            },
            error: function() { alert('Gagal memuat data stok global.'); },
            complete: function() { btn.prop('disabled', false).html('Tampilkan Tren'); }
        });
    }

    $('#btnApplyStockFilter').on('click', applyStockFilter);
    const today = new Date(), lastWeek = new Date();
    lastWeek.setDate(today.getDate() - 6);
    $('#stockDateEnd').val(today.toISOString().split('T')[0]);
    $('#stockDateStart').val(lastWeek.toISOString().split('T')[0]);
    applyStockFilter();
});
</script>