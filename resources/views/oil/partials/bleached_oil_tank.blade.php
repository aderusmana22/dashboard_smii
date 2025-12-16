<div class="w-full font-sans">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
        <!-- KOLOM KIRI: TABEL DATA -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-slate-100 h-full flex flex-col">
            <div class=" bg-pink-600 px-6 py-4 flex justify-between items-center">
                <h4 class="text-white font-semibold text-lg flex items-center gap-2"><i class="mdi mdi-table-large"></i>
                    Data Inventaris</h4>
                <span id="bleachedTotal" class="bg-white/20 text-white text-xs px-2 py-1 rounded"></span>
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
                    <tbody id="bleachedTanksBody" class="divide-y divide-slate-100"></tbody>
                </table>
            </div>
        </div>

        <!-- KOLOM KANAN: FILTER & CHART -->
        <div class="flex flex-col gap-6">
            <div class="bg-white rounded-xl shadow-lg border border-slate-100 p-6">
                <h5 class="text-lg font-bold text-slate-700 mb-4 border-l-4 border-rose-500 pl-3">🎚️ Filter &
                    Visualisasi</h5>

                {{-- FILTER RENTANG TANGGAL BARU --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="bleachedDateStart"
                            class="block mb-1 text-xs font-semibold text-slate-500 uppercase">Tanggal Mulai</label>
                        <input type="date" id="bleachedDateStart"
                            class="w-full bg-slate-50 border-slate-200 text-sm rounded-lg p-2.5">
                    </div>
                    <div>
                        <label for="bleachedDateEnd"
                            class="block mb-1 text-xs font-semibold text-slate-500 uppercase">Tanggal Akhir</label>
                        <input type="date" id="bleachedDateEnd"
                            class="w-full bg-slate-50 border-slate-200 text-sm rounded-lg p-2.5">
                    </div>
                </div>

                {{-- Dropdown untuk filter visibilitas --}}
                <div class="mb-5">
                    <label for="bleachedGroupSelector"
                        class="block mb-1 text-xs font-semibold text-slate-500 uppercase">Tampilkan Kelompok</label>
                    <select id="bleachedGroupSelector"
                        class="w-full bg-slate-50 border-slate-200 text-sm rounded-lg p-2.5">
                        <option value="ALL">Semua Tangki</option>
                        <option value="SMALL">Hanya Tangki Kecil</option>
                        <option value="LARGE">Hanya Tangki Besar (6T15)</option>
                    </select>
                </div>

                <button type="button" id="btnApplyBleachedFilter"
                    class="w-full text-white bg-pink-600 font-medium rounded-lg text-sm px-5 py-2.5 shadow-md">Tampilkan
                    Tren</button>
            </div>

            <div class="bg-white rounded-xl shadow-lg border border-slate-100 flex-grow">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                    <h5 class="font-bold text-slate-700">Tren Level Stok Harian (Kg)</h5>
                </div>
                <div class="p-4 h-[450px] w-full"><canvas id="bleachedOilTankChart"></canvas></div>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {
        let bleachedChart = null;
        const descColors = {
            'RBDHPKO42': 'bg-purple-100 text-purple-700', 'RBD MISC': 'bg-blue-100 text-blue-700',
            'RBDHPO43': 'bg-orange-100 text-orange-700', 'RBDHSBO36': 'bg-yellow-100 text-yellow-700',
            'RBDHCS58': 'bg-teal-100 text-teal-700', 'RBDHCP46': 'bg-pink-100 text-pink-700',
            'RBDHPO55': 'bg-orange-100 text-orange-700', 'CRUDE MISC': 'bg-gray-200 text-gray-700',
            'PE (T) - Large': 'bg-indigo-100 text-indigo-700', 'Available': 'bg-slate-200 text-slate-500 border border-slate-300'
        };

        function renderBleachedChart(chartData) {
            const ctx = document.getElementById('bleachedOilTankChart');
            if (!ctx) return;
            if (bleachedChart) bleachedChart.destroy();

            bleachedChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.labels.map(d => new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })),
                    datasets: chartData.datasets
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { display: false }, // Legenda disembunyikan agar tidak terlalu ramai
                        tooltip: { callbacks: { label: (c) => ` ${c.dataset.label}: ${new Intl.NumberFormat('id-ID').format(c.raw)} Kg` } }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            // Sumbu Y akan menyesuaikan otomatis, bisa diset manual jika perlu
                            // max: 85000, 
                            ticks: { callback: (v) => (v / 1000) + 'K' }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });
            // Panggil filter visibilitas setelah chart dirender
            filterChartVisibility();
        }

        function updateBleachedTable(tableData) {
            const tableBody = $('#bleachedTanksBody');
            tableBody.empty();
            $('#bleachedTotal').text(`Total: ${tableData.length} Tanks`);
            tableData.forEach(row => {
                const isAvailable = row.description === 'Available';
                const rowClass = isAvailable ? 'bg-slate-50' : 'hover:bg-rose-50/50';
                const textClass = isAvailable ? 'text-slate-400' : 'text-slate-700';
                const descBadge = `<span class="px-2 py-1 rounded text-xs font-bold ${descColors[row.description] || 'bg-gray-100 text-gray-800'}">${row.description || '-'}</span>`;
                tableBody.append(`<tr class="${rowClass} transition"><td class="px-6 py-3 font-semibold ${textClass}">${row.tank_code}</td><td class="px-6 py-3 text-right font-mono ${textClass}">${row.capacity_kg}</td><td class="px-6 py-3 ${textClass}">${row.oil_code || '-'}</td><td class="px-6 py-3">${descBadge}</td></tr>`);
            });
        }

        function applyBleachedFilter() {
            const btn = $('#btnApplyBleachedFilter');
            const data = {
                start_date: $('#bleachedDateStart').val(),
                end_date: $('#bleachedDateEnd').val()
            };
            if (!data.start_date || !data.end_date) { alert('Silakan pilih rentang tanggal.'); return; }
            btn.prop('disabled', true).html('Memuat...');

            $.ajax({
                url: '{{ route("oil.getBleachedOilData") }}', type: 'GET', data: data,
                success: function (res) {
                    updateBleachedTable(res.tableData);
                    renderBleachedChart(res.chartData);
                },
                error: function () { alert('Gagal memuat data Bleached Oil.'); },
                complete: function () { btn.prop('disabled', false).html('Tampilkan Tren'); }
            });
        }

        // Fungsi baru untuk memfilter visibilitas garis
        function filterChartVisibility() {
            const selectedGroup = $('#bleachedGroupSelector').val();
            if (bleachedChart) {
                bleachedChart.data.datasets.forEach(dataset => {
                    const isLargeTank = dataset.label === '6T15';
                    if (selectedGroup === 'ALL') {
                        dataset.hidden = false;
                    } else if (selectedGroup === 'SMALL') {
                        dataset.hidden = isLargeTank;
                    } else if (selectedGroup === 'LARGE') {
                        dataset.hidden = !isLargeTank;
                    }
                });
                bleachedChart.update();
            }
        }

        $('#btnApplyBleachedFilter').on('click', applyBleachedFilter);
        $('#bleachedGroupSelector').on('change', filterChartVisibility);

        const today = new Date(), lastWeek = new Date();
        lastWeek.setDate(today.getDate() - 6);
        $('#bleachedDateEnd').val(today.toISOString().split('T')[0]);
        $('#bleachedDateStart').val(lastWeek.toISOString().split('T')[0]);
        applyBleachedFilter();
    });
</script>