<!-- DEPENDENCIES: Pastikan jQuery, Chart.js, dan Tailwind CSS sudah dimuat di layout utama Anda -->

<style>
    /* --- ANIMASI GELOMBANG CAIRAN (COMMON) --- */
    @keyframes wave-move {
        0% { transform: translateX(-50%) rotate(0deg); }
        50% { transform: translateX(-50%) rotate(2deg) translateY(-5px); }
        100% { transform: translateX(-50%) rotate(0deg); }
    }

    .liquid-fill {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        transition: height 1s ease-in-out;
        z-index: 2;
    }

    .liquid-fill::before {
        content: "";
        position: absolute;
        width: 300%;
        height: 25px;
        background: inherit;
        top: -10px;
        left: 50%;
        border-radius: 40%;
        opacity: 0.8;
        animation: wave-move 4s infinite linear;
    }

    /* --- 1. VISUALISASI HYDROGEN: TORPEDO SHAPE --- */
    .shape-torpedo {
        position: relative;
        width: 70px;
        height: 180px;
        background: #f1f5f9;
        /* Bentuk Peluru/Torpedo: Atas lonjong, bawah sedikit tumpul */
        border-radius: 50% 50% 15px 15px;
        border: 3px solid #b91c1c; /* Merah Tua */
        overflow: hidden;
        box-shadow: inset 10px 0 15px rgba(0, 0, 0, 0.1);
        z-index: 10;
    }

    .shape-torpedo::after {
        content: '';
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background: repeating-linear-gradient(to bottom, transparent, transparent 40px, rgba(0, 0, 0, 0.1) 41px, rgba(0, 0, 0, 0.1) 43px);
        pointer-events: none;
        z-index: 20;
    }

    /* --- 2. VISUALISASI NITROGEN: STORAGE TANK (CRYO) --- */
    .shape-tank-wrapper {
        position: relative;
        width: 100px;
        height: 180px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .shape-tank-body {
        position: relative;
        width: 100%;
        height: 160px;
        background: #f1f5f9;
        /* Bentuk Tanki: Atas Kubah, Bawah Datar */
        border-radius: 40px 40px 10px 10px;
        border: 3px solid #1d4ed8; /* Biru Tua */
        overflow: hidden;
        z-index: 10;
        box-shadow: inset -15px 0 20px rgba(0, 0, 0, 0.05);
    }

    .shape-tank-legs {
        width: 90%;
        height: 15px;
        display: flex;
        justify-content: space-between;
    }

    .tank-leg {
        width: 8px;
        height: 100%;
        background: #475569;
        border-radius: 0 0 4px 4px;
    }

    /* --- 3. VISUALISASI AMMONIA: GAS CYLINDER (UPDATED) --- */
    .shape-cylinder-wrapper {
        position: relative;
        width: 80px;
        height: 180px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-end;
    }

    .cylinder-cap {
        width: 30px;
        height: 15px;
        background: #475569;
        border-radius: 3px;
        margin-bottom: -2px;
        z-index: 5;
    }

    .cylinder-neck {
        width: 20px;
        height: 10px;
        background: #64748b;
        z-index: 5;
    }

    .shape-cylinder-body {
        position: relative;
        width: 100%;
        height: 150px;
        /* UPDATE: Background kaca kehijauan, bukan abu-abu polos */
        background: linear-gradient(to right, #ecfdf5, #f0fdf4, #ecfdf5); 
        border-radius: 25px 25px 5px 5px;
        border: 3px solid #059669; /* Emerald-600 */
        overflow: hidden;
        z-index: 10;
        /* Shadow dalam agar terlihat cekung 3D */
        box-shadow: inset 5px 0 10px rgba(0,0,0,0.05), inset -5px 0 10px rgba(0,0,0,0.05);
    }

    /* BARU: Warna Cairan Khusus Ammonia (Gradient Hijau) */
    .liquid-gradient-ammonia {
        background: linear-gradient(to bottom, #10b981, #047857); /* Emerald-500 ke Emerald-700 */
    }

    /* Efek Kaca Umum */
    .glass-glare {
        position: absolute;
        top: 0;
        left: 15%;
        width: 25%;
        height: 100%;
        background: linear-gradient(to right, rgba(255, 255, 255, 0.7), rgba(255, 255, 255, 0.1));
        z-index: 30;
        pointer-events: none;
    }
</style>

<div class="w-full font-sans">
    <!-- HEADER -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-800">Utility Gas Monitor</h2>
            <p class="text-slate-500 font-medium">Real-time Stock Level & Pressure</p>
        </div>

        <!-- FILTER DATE -->
        <div class="flex flex-col gap-2 w-full sm:w-auto">
            <div class="flex gap-2">
                <input type="date" id="gasDateStart"
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none shadow-sm">
                <input type="date" id="gasDateEnd"
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none shadow-sm">
            </div>
            <button id="btnUpdateGasData"
                class="w-full bg-blue-800 hover:bg-slate-700 text-white font-bold py-2.5 rounded-lg shadow-md transition-all flex justify-center items-center">
                <i class="mdi mdi-refresh mr-2"></i> Update Data
            </button>
        </div>
    </div>

    <!-- BAGIAN 1: SNAPSHOT (VISUALISASI REALISTIS) -->
    <h3 class="text-xl font-bold text-slate-700 mb-5 flex items-center gap-2">
        <span class="w-1 h-6 bg-slate-600 rounded-full"></span> Status & Stok Terkini
    </h3>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">

        <!-- 1. HYDROGEN (TORPEDO STYLE) -->
        <div class="rounded-2xl shadow-lg border border-slate-100 overflow-hidden flex flex-col h-full">
            <div class="border-b border-red-100 px-6 py-3 flex justify-between items-center"
                style="background: linear-gradient(to right, #fee2e2, #ffffff);">
                <h5 class="text-red-700 font-bold text-lg">
                    <i class="mdi mdi-fire"></i> Hydrogen (H2)
                </h5>
                <span class="text-[10px] font-bold bg-red-100 text-red-800 px-2 py-1 rounded border border-red-200 uppercase tracking-wide">
                    Pressure
                </span>
            </div>

            <div class="p-6 flex items-center justify-between gap-2 h-full">
                <div class="w-1/3 flex justify-center">
                    <div class="shape-torpedo">
                        <div class="glass-glare"></div>
                        <div id="h2Liquid" class="liquid-fill bg-red-600" style="height: 0%;"></div>
                    </div>
                </div>

                <div class="w-2/3 text-right">
                    <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">
                        Avg Pressure
                    </span>
                    <div class="flex items-baseline justify-end gap-1">
                        <span id="h2Value" class="text-6xl font-black text-slate-800 tracking-tighter">0</span>
                        <span class="text-lg font-bold text-slate-500">Bar</span>
                    </div>
                    <div class="mt-3 border-t border-slate-100 pt-2">
                        <span id="h2TorpedoCount"
                            class="text-xs font-bold text-red-500 bg-red-50 px-2 py-1 rounded-full">
                            Active: 0 Unit
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. NITROGEN (STORAGE TANK STYLE) -->
        <div class="rounded-2xl shadow-lg border border-slate-100 overflow-hidden flex flex-col h-full">
            <div class="border-b border-blue-100 px-6 py-3 flex justify-between items-center"
                style="background: linear-gradient(to right, #dbeafe, #ffffff);">
                <h5 class="text-blue-700 font-bold text-lg">
                    <i class="mdi mdi-snowflake"></i> Nitrogen (N2)
                </h5>
                <span class="text-[10px] font-bold bg-blue-100 text-blue-800 px-2 py-1 rounded border border-blue-200 uppercase tracking-wide">
                    Level
                </span>
            </div>

            <div class="p-6 flex items-center justify-between gap-2 h-full">
                <div class="w-1/3 flex justify-center">
                    <div class="shape-tank-wrapper">
                        <div class="shape-tank-body">
                            <div class="glass-glare"></div>
                            <div id="n2Liquid" class="liquid-fill bg-blue-500" style="height: 0%;"></div>

                            <div class="absolute right-2 top-4 bottom-4 w-2 flex flex-col justify-between z-20 opacity-30">
                                <div class="w-full h-px bg-black"></div>
                                <div class="w-full h-px bg-black"></div>
                                <div class="w-full h-px bg-black"></div>
                                <div class="w-full h-px bg-black"></div>
                            </div>
                        </div>
                        <div class="shape-tank-legs">
                            <div class="tank-leg"></div>
                            <div class="tank-leg"></div>
                        </div>
                    </div>
                </div>

                <div class="w-2/3 text-right">
                    <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">
                        Liquid Level
                    </span>
                    <div class="flex items-baseline justify-end gap-1">
                        <span id="n2Value" class="text-6xl font-black text-slate-800 tracking-tighter">0</span>
                        <span class="text-lg font-bold text-slate-500">Inch</span>
                    </div>
                    <div id="n2StatusBadge"
                        class="mt-3 inline-block px-3 py-1 bg-slate-100 rounded text-xs font-bold text-slate-500">
                        Checking...
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. AMMONIA (CYLINDER STYLE) - UPDATED VISUAL -->
        <div class="rounded-2xl shadow-lg border border-slate-100 overflow-hidden flex flex-col h-full">
            <div class="border-b border-emerald-100 px-6 py-3 flex justify-between items-center"
                style="background: linear-gradient(to right, #d1fae5, #ffffff);">
                <h5 class="text-emerald-700 font-bold text-lg">
                    <i class="mdi mdi-flask"></i> Ammonia (NH3)
                </h5>
                <span class="text-[10px] font-bold bg-emerald-100 text-emerald-800 px-2 py-1 rounded border border-emerald-200 uppercase tracking-wide">
                    Stock
                </span>
            </div>

            <div class="p-6 flex items-center justify-between gap-2 h-full">
                <div class="w-1/3 flex justify-center">
                    <div class="shape-cylinder-wrapper">
                        <!-- Cap color adjustment -->
                        <div class="cylinder-cap bg-slate-600"></div>
                        <div class="cylinder-neck bg-slate-500"></div>
                        
                        <div class="shape-cylinder-body">
                            <div class="glass-glare"></div>
                            
                            <!-- CAIRAN HIJAU GRADASI -->
                            <div id="nh3Liquid" class="liquid-fill liquid-gradient-ammonia" style="height: 0%;"></div>
                            
                            <!-- Teks Persentase dengan Shadow agar terbaca di atas hijau -->
                            <div class="absolute inset-0 flex items-center justify-center z-20 pointer-events-none">
                                <span id="nh3PctLabel" class="text-white font-black text-lg drop-shadow-[0_2px_2px_rgba(0,0,0,0.5)]">0%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-2/3 flex flex-col items-end justify-center">
                    <div class="mb-2 text-right">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">FULL CYLINDERS</p>
                        <div class="flex items-center justify-end gap-2">
                            <span id="nh3Full" class="text-5xl font-black text-emerald-600 tracking-tighter">0</span>
                            <i class="mdi mdi-gas-cylinder text-2xl text-emerald-200"></i>
                        </div>
                    </div>

                    <div class="flex justify-end items-center gap-3 w-full border-t border-slate-100 pt-3 mt-1">
                        <div class="text-right">
                            <span class="block text-[10px] font-bold text-slate-400 uppercase">EMPTY</span>
                            <span id="nh3Empty" class="text-xl font-bold text-slate-500">0</span>
                        </div>
                        <div class="text-right pl-4 border-l border-slate-100">
                            <span class="block text-[10px] font-bold text-slate-400 uppercase">TOTAL</span>
                            <span id="nh3Total" class="text-xl font-bold text-slate-800">0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- BAGIAN 2: GRAFIK (Clean & Minimalist) -->
    <h3 class="text-xl font-bold text-slate-700 mb-5 flex items-center gap-2">
        <span class="w-1 h-6 bg-slate-600 rounded-full"></span> Tren Harian (7 Hari Terakhir)
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="rounded-2xl shadow-sm border border-slate-200 p-4 h-[250px]">
            <canvas id="hydrogenTrendChart"></canvas>
        </div>
        <div class="rounded-2xl shadow-sm border border-slate-200 p-4 h-[250px]">
            <canvas id="nitrogenTrendChart"></canvas>
        </div>
        <div class="rounded-2xl shadow-sm border border-slate-200 p-4 h-[250px]">
            <canvas id="ammoniaTrendChart"></canvas>
        </div>
    </div>
</div>

<script>
    $(function () {
        let hydrogenTrendChart, nitrogenTrendChart, ammoniaTrendChart;

        function updateSnapshot(data) {
            // --- 1. HYDROGEN (H2) ---
            let h2Val = 0;
            let activeTorpedo = 0;
            if (data.hydrogen && data.hydrogen.length > 0) {
                const total = data.hydrogen.reduce((acc, curr) => acc + parseFloat(curr.value), 0);
                activeTorpedo = data.hydrogen.length;
                h2Val = (total / activeTorpedo).toFixed(1);
            }

            // Animasi angka
            $('#h2Value').text(h2Val);
            $('#h2TorpedoCount').text(`Active: ${activeTorpedo} Unit(s)`);

            // Update Torpedo Liquid
            let h2Pct = (h2Val / 200) * 100;
            if (h2Pct > 100) h2Pct = 100;
            $('#h2Liquid').css('height', `${h2Pct}%`);


            // --- 2. NITROGEN (N2) ---
            let n2Val = 0;
            if (data.nitrogen) {
                n2Val = parseFloat(data.nitrogen.value).toFixed(1);
            }

            $('#n2Value').text(n2Val);

            // Update Tank Liquid
            let n2Pct = n2Val;
            if (n2Pct > 100) n2Pct = 100;
            $('#n2Liquid').css('height', `${n2Pct}%`);

            // Status Logic
            const n2Liquid = $('#n2Liquid');
            const n2Badge = $('#n2StatusBadge');
            if (n2Val >= 65) {
                n2Liquid.removeClass('bg-red-500').addClass('bg-blue-500');
                n2Badge.html('<i class="mdi mdi-check-circle"></i> SAFE LEVEL').removeClass('bg-red-100 text-red-600 animate-pulse').addClass('bg-green-100 text-green-700');
            } else {
                n2Liquid.removeClass('bg-blue-500').addClass('bg-red-500');
                n2Badge.html('<i class="mdi mdi-alert-circle"></i> CRITICAL').removeClass('bg-green-100 text-green-700').addClass('bg-red-100 text-red-600 animate-pulse');
            }


            // --- 3. AMMONIA (NH3) ---
            const fullRaw = data.ammonia?.find(i => i.unit_name === 'Full Cylinders')?.value || 0;
            const emptyRaw = data.ammonia?.find(i => i.unit_name === 'Empty Cylinders')?.value || 0;

            const full = parseInt(fullRaw, 10);
            const empty = parseInt(emptyRaw, 10);
            const total = full + empty;

            $('#nh3Full').text(full);
            $('#nh3Empty').text(empty);
            $('#nh3Total').text(total);

            // Update Cylinder Liquid
            let nh3Pct = 0;
            if (total > 0) {
                nh3Pct = Math.round((full / total) * 100);
            }
            $('#nh3Liquid').css('height', `${nh3Pct}%`);
            $('#nh3PctLabel').text(`${nh3Pct}%`);
        }

        // --- CHART CONFIG ---
        const chartDefaults = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 9 } } },
                y: { beginAtZero: true, grid: { color: '#f8fafc' } }
            },
            elements: {
                line: { tension: 0.4, borderWidth: 2 },
                point: { radius: 0, hitRadius: 10, hoverRadius: 4 }
            }
        };

        const createChart = (id, instance, labels, datasets, color) => {
            const ctx = document.getElementById(id);
            if (!ctx) return null;
            if (instance) instance.destroy();
            return new Chart(ctx, {
                type: 'line',
                data: { labels, datasets },
                options: {
                    ...chartDefaults,
                    elements: { ...chartDefaults.elements, line: { ...chartDefaults.elements.line, borderColor: color, backgroundColor: color } }
                }
            });
        };

        function updateTrendCharts(data) {
            const labels = data.labels.map(d => new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }));

            // H2 Chart
            const h2Data = data.hydrogen.map(h => ({ ...h, borderColor: '#dc2626', backgroundColor: '#dc2626' }));
            hydrogenTrendChart = createChart('hydrogenTrendChart', hydrogenTrendChart, labels, h2Data, '#dc2626');

            // N2 Chart
            const n2Data = data.nitrogen.map(n => ({ ...n, borderColor: '#2563eb', backgroundColor: '#2563eb' }));
            nitrogenTrendChart = createChart('nitrogenTrendChart', nitrogenTrendChart, labels, n2Data, '#2563eb');

            // NH3 Chart (Multi-line)
            if (ammoniaTrendChart) ammoniaTrendChart.destroy();
            const ctxNH3 = document.getElementById('ammoniaTrendChart');
            if (ctxNH3) {
                ammoniaTrendChart = new Chart(ctxNH3, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: data.ammonia.map((a, i) => ({
                            ...a,
                            label: a.label.replace(' Cylinders', ''),
                            borderColor: i === 0 ? '#059669' : '#94a3b8',
                            backgroundColor: i === 0 ? '#059669' : '#94a3b8',
                            borderDash: i === 1 ? [4, 4] : [],
                            tension: 0.4, pointRadius: 0
                        }))
                    },
                    options: { ...chartDefaults, plugins: { legend: { display: true, position: 'bottom', labels: { boxWidth: 8, usePointStyle: true } } } }
                });
            }
        }

        function fetchData() {
            const btn = $('#btnUpdateGasData');
            const payload = {
                start_date: $('#gasDateStart').val(),
                end_date: $('#gasDateEnd').val()
            };

            if (!payload.start_date || !payload.end_date) { alert('Pilih tanggal.'); return; }

            btn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin mr-2"></i> Loading...');

            $.ajax({
                url: '{{ route("utility.gas.data") }}',
                type: 'GET', data: payload,
                success: function (res) {
                    updateSnapshot(res.snapshot);
                    updateTrendCharts(res.trend);
                },
                error: function () { alert('Gagal mengambil data.'); },
                complete: function () { btn.prop('disabled', false).html('<i class="mdi mdi-refresh mr-2"></i> Update Data'); }
            });
        }

        // Init
        const today = new Date();
        const lastWeek = new Date();
        lastWeek.setDate(today.getDate() - 6);
        $('#gasDateEnd').val(today.toISOString().split('T')[0]);
        $('#gasDateStart').val(lastWeek.toISOString().split('T')[0]);
        fetchData();
    });
</script>