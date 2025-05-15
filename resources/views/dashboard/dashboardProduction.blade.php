<x-app-layout>
    @section('title')
        Dashboard Production
    @endsection

    <!-- Weight and Quantity Comparison -->
    <div class="flex justify-between">
        <!-- Total Production Year -->
        <div class="box pull-up w-1/3 mr-2">
            <div class="box-body h-36"> <!-- Adjust height as needed -->
                <div class="flex justify-between items-center">
                    <div class="bs-5 ps-10 border-info">
                        <p class="text-fade mb-10 text-3xl">Total Production (YTD)</p>
                        <h2 id="yearProduction" class="my-0 fw-700 text-4xl"></h2>
                    </div>
                    <div class="icon">
                        <i class="fa-solid fa-calendar bg-info-light me-0 fs-24 rounded-3"></i>
                    </div>
                </div>
                <p id="yearComparison" class="text-danger mb-0 mt-10"><i class="fa-solid fa-arrow-down"></i></p>
            </div>
        </div>
        <!-- Weight Comparison -->
        <div class="box pull-up w-1/3 mx-2">
            <div class="box-body h-36"> <!-- Adjust height as needed -->
                <div class="flex justify-between items-center">
                    <div class="bs-5 ps-10 border-info">
                        <p class="text-fade mb-10 text-3xl">Total Production (MTD)</p>
                        <h2 id="weightThisMonth" class="my-0 fw-700 text-4xl"></h2>
                    </div>
                    <div class="icon">
                        <i class="fa-solid fa-box bg-info-light me-0 fs-24 rounded-3"></i>
                    </div>
                </div>
                <p id="weightComparison" class="text-danger mb-0 mt-10"><i class="fa-solid fa-arrow-down"></i></p>
            </div>
        </div>
        <!-- Quantity Comparison -->
        <div class="box pull-up w-1/3 ml-2">
            <div class="box-body h-36"> <!-- Adjust height as needed -->
                <div class="flex justify-between items-center">
                    <div class="bs-5 ps-10 border-info">
                        <p class="text-fade mb-10 text-3xl">Total Quantity (MTD)</p>
                        <h2 id="qtyThisMonth" class="my-0 fw-700 text-4xl"></h2>
                    </div>
                    <div class="icon">
                        <i class="fa-solid fa-boxes-stacked bg-info-light me-0 fs-24 rounded-3"></i>
                    </div>
                </div>
                <p id="qtyComparison" class="text-danger mb-0 mt-10"><i class="fa-solid fa-arrow-down"></i> </p>
            </div>
        </div>
    </div>


    <div class="grid grid-cols-5 gap-x-4 mb-4">
        <!-- Production Year -->
        <div class="col-span-2">
            <div class="box rounded-2xl">
                <div class="box-body analytics-info">
                    <div class="flex justify-between">
                        <div class="text-5xl font-medium">Marsho Production (Year)</div>
                        <div class="flex">
                            <!-- Daftar Tahun -->
                            <select id="yearFilterYear" class="mr-2 text-3xl rounded text-black">
                                @php
                                    $currentYear = date('Y');
                                @endphp
                                <option value="{{ $currentYear }}" class="" disabled selected hidden>
                                    {{ $currentYear }}</option>
                                @foreach ($availableYears as $year)
                                    // Mengganti loop for dengan loop foreach
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div id="yearChart" style="height:450px;"></div>
                </div>
            </div>
        </div>
        <!-- Production Period -->
        <div class="col-span-3">
            <div class="box rounded-2xl">
                <div class="box-body analytics-info">
                    <div class="flex justify-between">
                        <div class="text-5xl font-medium">Marsho Production (Month)</div>
                        <div class="flex">
                            <!-- Daftar Tahun -->
                            <select id="yearFilterBar" class="mr-2  text-3xl rounded text-black">
                                @php
                                    $currentYear = date('Y'); // Mengambil tahun saat ini
                                @endphp
                                <option value="" class="" disabled selected hidden>{{ $currentYear }}
                                </option>
                                @foreach ($availableYears as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>
                            <!-- Daftar bulan -->
                            <select id="monthFilterBar" class="mr-2  text-3xl rounded text-black">
                                <option value="" class="" disabled selected hidden>{{ date('F') }}
                                </option>
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}">{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div id="barChart" style="height:450px;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-5 gap-x-4 mb-4">
        <!-- Table -->
        <div class="col-span-2">
            <div class="card rounded-2xl">
                <div class="box-header items-center">
                    <div class="flex justify-between">
                        <h4 class="font-medium text-5xl">Marsho Line Production</h4>
                        <button id="dateDisplay"
                            class="waves-effect  waves-secondary btn btn-outline dropdown-toggle btn-md text-3xl"
                            style="padding: 15px 30px; font-size: 28px;" data-bs-toggle="dropdown" href="#"
                            aria-expanded="false">
                            {{ date('d F Y') }} <!-- Menampilkan tanggal hari ini -->
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" style="will-change: transform;">
                            <div class="px-3 py-2">
                                <input type="date" id="dateFilterDropdown" class="bg-gray-200 text-black text-2xl"
                                    value="{{ date('Y-m-d') }}">
                                <button id="applyDateFilterDropdown" class=" text-white px-2 py-1 mt-2"></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0 w-full table-hover table-striped table-bordered">
                        <thead>
                            <tr>
                                <th class="text-3xl">Line</th>
                                <th class="text-center text-3xl">Shift 2</th>
                                <th class="text-center text-3xl">Shift 3</th>
                                <th class="text-center text-3xl">Shift 1</th>
                                <th class="text-center text-3xl">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" class="text-center text-3xl">Tidak ada data untuk ditampilkan.</td>
                            </tr>
                        </tbody>

                    </table>
                </div>
            </div>
        </div>
        <!-- Gauge Charts -->
        <div class="col-span-3">
            <div class="box rounded-2xl">
                <div class="box-body analytics-info">
                    <div class="flex justify-between">
                        <div class="text-5xl font-medium">Marsho Line Daily Utilization</div>
                    </div>
                    <div class="grid grid-cols-5 gap-x-4 mt-4">
                        <!-- Line A -->
                        <div class="card rounded-2xl pull-up" style="border:2px solid rgb(96 165 250)">
                            <div class="box-header text-center">
                                <h3 class="box-title m-0 text-3xl">Line A</h3>
                            </div>
                            <div class="flex justify-center items-center" style="height: 300px;">
                                <div id="lineAChart" style="width:300px; height:400px;"></div>
                            </div>
                        </div>
                        <!-- Line B -->
                        <div class="card rounded-2xl pull-up" style="border:2px solid rgb(248 113 113)">
                            <div class="box-header text-center">
                                <h3 class="box-title m-0 text-3xl">Line B</h3>
                            </div>
                            <div class="flex justify-center items-center" style="height: 300px;">
                                <div id="lineBChart" style="width:300px; height:350px;"></div>
                            </div>
                        </div>
                        <!-- Line C -->
                        <div class="card rounded-2xl pull-up" style="border:2px solid rgb(216 180 254)">
                            <div class="box-header text-center">
                                <h3 class="box-title m-0 text-3xl">Line C</h3>
                            </div>
                            <div class="flex justify-center items-center" style="height: 300px;">
                                <div id="lineCChart" style="width:300px; height:350px;"></div>
                            </div>
                        </div>
                        <!-- Line D -->
                        <div class="card rounded-2xl pull-up" style="border:2px solid rgb(134 239 172)">
                            <div class="box-header text-center">
                                <h3 class="box-title m-0 text-3xl">Line D</h3>
                            </div>
                            <div class="flex justify-center items-center" style="height: 300px;">
                                <div id="lineDChart" style="width:300px; height:350px;"></div>
                            </div>
                        </div>
                        <!-- Line E -->
                        <div class="card rounded-2xl pull-up" style="border:2px solid rgb(253 186 116)">
                            <div class="box-header text-center">
                                <h3 class="box-title m-0 text-3xl">Line E</h3>
                            </div>
                            <div class="flex justify-center items-center" style="height: 300px;">
                                <div id="lineEChart" style="width:300px; height:350px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <!-- Import Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/echarts/dist/echarts.min.js"></script>


        <!-- Scripts for Charts -->
        <script>
            // Inisialisasi ECharts
            var barChart = echarts.init(document.getElementById('barChart'));
            var yearChart = echarts.init(document.getElementById('yearChart'));

            // Fungsi untuk membuat chart responsif
            function makeChartsResponsive() {
                window.addEventListener('resize', function() {
                    barChart.resize();
                    yearChart.resize();
                });
            }

            // Panggil fungsi untuk membuat chart responsif
            makeChartsResponsive();

            // Inisialisasi untuk setiap Line (A, B, C, D. E) Gauge Chart
            var doughnutData;
            const myCharts = {};

            // Line A, B, C, D, E
            const lines = ['A', 'B', 'C', 'D', 'E'];

            document.addEventListener('DOMContentLoaded', function() {
                fetchDashboardData();
                updateBarChart();
                updateFilterDropdown();
                createInterval();
                updateYearChart();
            }, {
                passive: true
            }); // Menambahkan opsi passive: true

            function createInterval() {
                setInterval(() => {
                    updateBarChart();
                    updateYearChart();
                }, 5000);
                setInterval(() => {
                    updateFilterDropdown();
                }, 5000);
            }

            function fetchDashboardData() {
                fetch('/get-dashboard-production')
                    .then(response => response.json())
                    .then(data => {
                        // console.log('Dashboard Data:', data);


                        // Inisialisasi standard data
                        doughnutData = data.gaugeStandarData;

                        // Pastikan doughnutData terdefinisi sebelum memanggil createGaugeChart
                        if (doughnutData) {
                            createGaugeChart();
                        } else {
                            console.error('doughnutData is undefined');
                        }
                    })
                    .catch(error => console.error('Error fetching dashboard data:', error));
            }

            function createGaugeChart() {
                // Pastikan doughnutData terdefinisi
                if (!doughnutData) {
                    console.error('doughnutData is undefined');
                    return;
                }

                // Gauge Charts for Lines A, B, C, D, E
                lines.forEach(line => {
                    const ctx = document.getElementById(`line${line}Chart`);
                    if (ctx) {
                        myCharts[line] = echarts.init(ctx);
                        var option;
                        const standardValue = parseFloat(doughnutData[
                        line]); // Mengambil dan mengonversi nilai standar dari doughnutData

                        // Pastikan standardValue terdefinisi
                        if (isNaN(standardValue)) {
                            console.error(`Standard value for line ${line} is undefined or not a number`);
                            return;
                        }

                        // Asumsikan nilai maksimum untuk gauge adalah 1.5 kali nilai standar
                        const maxValue = standardValue * 1.5;

                        // Contoh nilai aktual, ganti dengan nilai aktual yang sesuai
                        const actualValue = 0; // Ganti dengan nilai aktual yang ingin ditampilkan

                        option = {
                            series: [{
                                type: 'gauge',
                                max: maxValue, // Set nilai maksimum untuk gauge
                                axisLine: {
                                    lineStyle: {
                                        width: 30,
                                        color: [
                                            [standardValue / maxValue,
                                            '#3498db'], // Batas biru sampai nilai standar
                                            [1, '#e74c3c'] // Batas merah setelah nilai standar
                                        ]
                                    }
                                },
                                pointer: {
                                    itemStyle: {
                                        color: 'auto'
                                    }
                                },
                                axisTick: {
                                    distance: -30,
                                    length: 8,
                                    lineStyle: {
                                        color: '#fff',
                                        width: 2
                                    }
                                },
                                splitLine: {
                                    distance: -30,
                                    length: 30,
                                    lineStyle: {
                                        color: '#fff',
                                        width: 4
                                    }
                                },
                                axisLabel: {
                                    color: 'inherit',
                                    distance: 30,
                                    fontSize: 10,
                                    show: true, // Tampilkan label
                                    formatter: function(value) {
                                        // Hanya tampilkan label untuk standardValue
                                        if (value === standardValue) {
                                            return `{a|${value.toLocaleString('id-ID')}}`; // Tandai standardValue
                                        }
                                        return ''; // Kosongkan label lainnya
                                    },
                                    rich: {
                                        a: {
                                            color: '#3498db', // Warna khusus untuk standardValue
                                            fontWeight: 'bold'
                                        }
                                    }
                                },
                                detail: {
                                    valueAnimation: true,
                                    formatter: function(value) {
                                        return value.toLocaleString('id-ID') + ' kg';
                                    },
                                    color: 'inherit',
                                    fontSize: 16
                                },
                                data: [{
                                    value: actualValue // Nilai actual yang ingin ditampilkan
                                }]
                            }]
                        };

                        option && myCharts[line].setOption(option);

                        // Tambahkan event listener untuk resize
                        window.addEventListener('resize', function() {
                            myCharts[line].resize();
                        });

                    } else {
                        console.error(`Element with ID line${line}Chart not found.`);
                    }
                });
            }



            // Event listener untuk tombol filter tanggal di dropdown
            document.getElementById('applyDateFilterDropdown').addEventListener('click', function() {
                updateFilterDropdown();
            });

            function updateFilterDropdown() {
                const selectedDate = document.getElementById('dateFilterDropdown').value;
                // Mengubah teks tombol untuk menampilkan tanggal yang dipilih
                const formattedDate = new Date(selectedDate).toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'long',
                    year: 'numeric'
                });
                document.getElementById('dateDisplay').innerText = formattedDate;

                // Fetch dan update data berdasarkan tanggal yang dipilih
                fetch(`/data-filter?date=${selectedDate}`)
                    .then(response => response.json())
                    .then(data => {
                        // console.log('Data Filter:', data);
                        // Kosongkan tabel sebelum menambahkan data baru
                        const tbody = document.querySelector('table tbody');
                        tbody.innerHTML = '';

                        // Pastikan data memiliki struktur yang benar
                        if (data && data.data && Array.isArray(data.data) && data.data.length > 0) {
                            const totals = {};
                            const lines = ['A', 'B', 'C', 'D', 'E'];

                            // Inisialisasi totals untuk semua line
                            lines.forEach(line => {
                                totals[line] = {
                                    shift1: 0,
                                    shift2: 0,
                                    shift3: 0,
                                    total: 0
                                };
                            });

                            // Hitung total per line dan shift
                            data.data.forEach(item => {
                                if (lines.includes(item.line)) {
                                    const shiftKey = `shift${item.shift.slice(-1)}`;
                                    totals[item.line][shiftKey] += parseFloat(item.total_weight);
                                    totals[item.line].total += parseFloat(item.total_weight);
                                }
                            });

                            // Tampilkan data ke dalam tabel
                            lines.forEach(line => {
                                const shifts = totals[line];
                                const row = document.createElement('tr');
                                row.innerHTML = `
                                    <td class="pt-0 px-0 b-0 border-b">
                                        <div class="flex items-center">
                                            <div class="w-10 h-50 rounded ${getLineColor(line)}"></div>
                                            <span class="text-fade text-3xl ml-2 font-semibold">${line}</span>
                                        </div>
                                    </td>
                                    <td class="text-right b-0 pt-0 px-0 border-b">
                                        <span class="text-fade text-3xl mr-2">${shifts.shift2.toLocaleString('id-ID')} kg</span>
                                    </td>
                                    <td class="text-right b-0 pt-0 px-0 border-b">
                                        <span class="text-fade text-3xl mr-2">${shifts.shift3.toLocaleString('id-ID')} kg</span>
                                    </td>
                                    <td class="text-right b-0 pt-0 px-0 border-b">
                                        <span class="text-fade text-3xl mr-2">${shifts.shift1.toLocaleString('id-ID')} kg</span>
                                    </td>
                                    <td class="text-right b-0 pt-0 px-0 border-b">
                                        <span class="text-fade text-3xl flex justify-end mr-2">${shifts.total.toLocaleString('id-ID')} kg</span>
                                    </td>
                                `;
                                tbody.appendChild(row);

                                // Update Gauge Chart
                                updateGaugeChart(line, shifts.total);
                            });

                            // Tambahkan baris total per shift
                            addTotalPerShiftRow(totals, tbody);

                            // Tambahkan baris grand total
                            addGrandTotalRow(totals, tbody);
                        } else {
                            // Update Gauge Chart to 0 if no data
                            lines.forEach(line => {
                                updateGaugeChart(line, 0); // Set to 0 if no data
                            });
                        }
                    })
                    .catch(error => console.error('Error fetching filtered data:', error));
            }

            function getLineColor(line) {
                const colors = {
                    'A': 'bg-blue-400',
                    'B': 'bg-red-400',
                    'C': 'bg-purple-300',
                    'D': 'bg-green-300',
                    'E': 'bg-orange-300'
                };
                return colors[line] || '';
            }

            function updateGaugeChart(line, totalWeight) {
                if (myCharts[line]) {
                    myCharts[line].setOption({
                        series: [{
                            axisLine: {
                                lineStyle: {
                                    width: 30,
                                    color: [
                                        [0.3, '#e74c3c'],
                                        [0.7, '#f1c40f'],
                                        [1, '#3498db']
                                    ]
                                }
                            },
                            data: [{
                                value: totalWeight.toFixed(2)
                            }]
                        }]
                    });
                }
            }

            function addTotalPerShiftRow(totals, tbody) {
                const totalPerShift = Object.values(totals).reduce((acc, curr) => {
                    acc.shift1 += curr.shift1;
                    acc.shift2 += curr.shift2;
                    acc.shift3 += curr.shift3;
                    return acc;
                }, {
                    shift1: 0,
                    shift2: 0,
                    shift3: 0
                });

                const totalPerShiftRow = document.createElement('tr');
                totalPerShiftRow.innerHTML = `
                    <td class="text-right text-3xl" colspan="1"><strong>Total</strong></td>
                    <td class="text-right text-3xl"><strong>${totalPerShift.shift2.toLocaleString('id-ID')} kg</strong></td>
                    <td class="text-right text-3xl"><strong>${totalPerShift.shift3.toLocaleString('id-ID')} kg</strong></td>
                    <td class="text-right text-3xl"><strong>${totalPerShift.shift1.toLocaleString('id-ID')} kg</strong></td>
                    <td class="text-left text-3xl flex justify-end mr-2"><strong>${(totalPerShift.shift1 + totalPerShift.shift2 + totalPerShift.shift3).toLocaleString('id-ID')} kg</strong></td>
                `;
                tbody.appendChild(totalPerShiftRow);
            }

            function addGrandTotalRow(totals, tbody) {
                const grandTotal = Object.values(totals).reduce((acc, curr) => acc + curr.total, 0);
                const grandTotalRow = document.createElement('tr');
                tbody.appendChild(grandTotalRow);
            }

            // Fungsi untuk mengatur opsi chart
            function setBarChartOption(label, actualData, standardData, actualHeight) {
                var option;

                const rawData = actualData;
                const grid = {
                    left: 100,
                    right: 100,
                    top: 50,
                    bottom: 50
                };

                // Deteksi mode gelap
                const isDarkMode = localStorage.getItem('darkMode');

                const series = ['A', 'B', 'C', 'D', 'E'].map((name, sid) => {
                    return {
                        name,
                        type: 'bar',
                        stack: 'total',
                        barWidth: '60%',
                        label: {
                            show: false
                        },
                        data: rawData[sid],
                        markLine: {
                            data: [{
                                yAxis: standardData[0], // Tambahkan threshold di sumbu Y
                                label: {
                                    formatter: function(params) {
                                        return params.value.toLocaleString(
                                            'id-ID'); // Memformat angka ribuan dengan koma
                                    },
                                    color: '#A9A9A9', // Warna label berdasarkan mode
                                    fontSize: 20 // Tambahkan properti fontSize di sini
                                }
                            }],
                            lineStyle: {
                                color: 'red',
                                type: 'dashed',
                            }
                        }
                    };
                });

                option = {
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: {
                            type: 'shadow'
                        },
                        formatter: function(params) {
                            let tooltipText = '';
                            let total = 0;

                            // Mengonversi param.value ke angka dan menjumlahkannya ke total
                            params.forEach(param => {
                                const value = parseFloat(param.value); // Konversi string ke angka
                                total += value; // Tambahkan nilai ke total
                                tooltipText +=
                                    `<span style="color:${param.color}">${param.seriesName}: ${value.toLocaleString('id-ID')}</span><br/>`;
                            });

                            // Menampilkan total di bagian akhir tooltip
                            tooltipText +=
                                `<span style="color:#000;font-weight:bold">Total: ${total.toLocaleString('id-ID')}</span>`;

                            return tooltipText;
                        }
                    },
                    color: ['#609CFA', '#F87171', '#D8B4FE', '#86EFAC', '#FDBA74'],
                    legend: {
                        selectedMode: true,
                        textStyle: {
                            fontSize: 20, // Sesuaikan ukuran font di sini
                            color: '#A9A9A9', // Warna label berdasarkan mode
                        }
                    },
                    grid,
                    yAxis: {
                        type: 'value',
                        min: 0,
                        max: Math.round(Math.max(actualHeight, standardData) * 1.03),
                        axisLabel: {
                            fontSize: 17,
                            formatter: function(value) {
                                return value.toLocaleString('id-ID'); // Memformat angka dengan titik
                            },
                            color: '#A9A9A9', // Warna label berdasarkan mode
                        }
                    },
                    xAxis: {
                        type: 'category',
                        data: label,
                        axisLabel: {
                            fontSize: 15,
                            color: '#A9A9A9', // Warna label berdasarkan mode
                        }
                    },
                    series
                };

                // Menggunakan opsi yang telah ditentukan untuk menampilkan chart
                option && barChart.setOption(option);
            }

            // Update Bar Chart berdasarkan bulan dan minggu yang dipilih
            document.getElementById('monthFilterBar').addEventListener('change', updateBarChart);
            document.getElementById('yearFilterBar').addEventListener('change', updateBarChart);

            function updateBarChart() {
                const month = document.getElementById('monthFilterBar').value;
                const year = document.getElementById('yearFilterBar').value;

                // Fetch dan update data bar chart berdasarkan bulan dan minggu yang dipilih
                fetch(`/bar-data?month=${month}&year=${year}`)
                    .then(response => response.json())
                    .then(data => {

                        // Perbarui Perbandingan Berat dan Kuantitas
                        document.getElementById('weightThisMonth').innerText =
                            `${(data.weightThisMonth / 1000).toLocaleString('id-ID')} Ton`;
                        document.getElementById('weightComparison').className = "text-success mb-0 mt-10";
                        document.getElementById('weightComparison').innerHTML =
                            ` ${data.weightComparison}`;
                        document.getElementById('qtyThisMonth').innerText =
                            `${data.qtyThisMonth.toLocaleString('id-ID')} Pcs`;
                        document.getElementById('qtyComparison').className = "text-success mb-0 mt-10";
                        document.getElementById('qtyComparison').innerHTML =
                            `<i class="fa-solid fa-arrow-down text-danger"></i>  ${data.qtyComparison} since last month`;

                        // Fungsi untuk mengekstrak angka dari string
                        function extractNumber(str) {
                            const match = str.match(/-?\d+(\.\d+)?/);
                            return match ? parseFloat(match[0]) : NaN;
                        }

                        // Parsing nilai untuk memastikan perbandingan angka
                        const weightComparisonValue = extractNumber(data.weightComparison);
                        const qtyComparisonValue = extractNumber(data.qtyComparison);

                        // Cek apakah weightComparison menunjukkan penurunan
                        if (weightComparisonValue < 0) {
                            document.getElementById('weightComparison').className = "text-danger mb-0 mt-10";
                            document.getElementById('weightComparison').innerHTML =
                                `<i class="fa-solid fa-arrow-down text-danger"></i>  ${data.weightComparison} since last month`;
                        } else {
                            document.getElementById('weightComparison').className = "text-success mb-0 mt-10";
                            document.getElementById('weightComparison').innerHTML =
                                `<i class="fa-solid fa-arrow-up text-success"></i>  ${data.weightComparison} since last month`;
                        }

                        // Cek apakah qtyComparison menunjukkan penurunan
                        if (qtyComparisonValue < 0) {
                            document.getElementById('qtyComparison').className = "text-danger mb-0 mt-10";
                            document.getElementById('qtyComparison').innerHTML =
                                `<i class="fa-solid fa-arrow-down text-danger"></i>  ${data.qtyComparison} since last month`;
                        } else {
                            document.getElementById('qtyComparison').className = "text-success mb-0 mt-10";
                            document.getElementById('qtyComparison').innerHTML =
                                `<i class="fa-solid fa-arrow-up text-success"></i>  ${data.qtyComparison} since last month`;
                        }

                        // Memastikan data yang diterima tidak undefined
                        if (data && data.labels && data.actual_qty && data.standard_qty && data.actual_height) {

                            setBarChartOption([...new Set(data.labels)], data.actual_qty, data.standard_qty.map(Number),
                                data.actual_height); // Mengatur opsi chart

                        } else {
                            console.error('Data tidak valid:', data);
                        }
                    })
                    .catch(error => console.error('Error fetching bar chart data:', error));
            }


            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('yearFilterYear').addEventListener('change', updateYearChart);
            });

            function updateYearChart() {
                const year = document.getElementById('yearFilterYear').value;

                if (!year) {
                    console.error('Parameter tahun tidak ada');
                    return;
                }

                fetch(`/year-data?year=${year}`)
                    .then(response => response.json())
                    .then(data => {
                        // console.log('Data Grafik Tahunan:', data);

                        // Isi Data Perbandingan
                        document.getElementById('yearProduction').innerText =
                            `${(data.thisYearTotal / 1000).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} Ton`;

                        const comparisonElement = document.getElementById('yearComparison');
                        if (comparisonElement) {
                            if (data.yearly_comparison.includes('Up')) {
                                comparisonElement.className = "text-success mb-0 mt-10";
                                comparisonElement.innerHTML =
                                    `<i class="fa-solid fa-arrow-up text-success"></i> ${data.yearly_comparison} since last year to date`;
                            } else {
                                comparisonElement.className = "text-danger mb-0 mt-10";
                                comparisonElement.innerHTML =
                                    `<i class="fa-solid fa-arrow-down text-danger"></i> ${data.yearly_comparison} since last year to date`;
                            }
                        } else {
                            console.error('Element with ID yearComparison not found.');
                        }

                        // Memastikan data yang diterima valid
                        if (data && data.labels && data.actual_qty && typeof data.standard_qty === 'number' && data
                            .actual_height) {
                            const actualData = ['A', 'B', 'C', 'D', 'E'].map(line => data.actual_qty[line].map(Number));

                            setYearChartOption(
                                data.labels,
                                actualData,
                                data.standard_qty,
                                parseFloat(data.actual_height)
                            );
                        } else {
                            console.error('Data tidak valid:', data);
                        }
                    })
                    .catch(error => console.error('Kesalahan saat mengambil data grafik tahunan:', error));
            }

            function setYearChartOption(labels, actualData, standardQty, actualHeight) {
                var option;

                const grid = {
                    left: 100,
                    right: 100,
                    top: 50,
                    bottom: 50
                };

                const isDarkMode = localStorage.getItem('darkMode');

                const series = ['A', 'B', 'C', 'D', 'E'].map((name, sid) => {
                    return {
                        name,
                        type: 'bar',
                        stack: 'total',
                        barWidth: '70%',
                        label: {
                            show: false
                        },
                        data: actualData[sid],
                        markLine: {
                            data: [{
                                yAxis: standardQty, // Menggunakan standard_qty sebagai angka
                                label: {
                                    formatter: function(params) {
                                        return params.value.toLocaleString('id-ID');
                                    },
                                    color: '#A9A9A9',
                                    fontSize: 18
                                }
                            }],
                            lineStyle: {
                                color: 'red',
                                type: 'dashed',
                            }
                        }
                    };
                });

                option = {
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: {
                            type: 'shadow'
                        },
                        formatter: function(params) {
                            let tooltipText = '';
                            let total = 0;
                            params.forEach(param => {
                                const value = parseFloat(param.value);
                                total += value;
                                tooltipText +=
                                    `<span style="color:${param.color}">${param.seriesName}: ${param.value.toLocaleString('id-ID')}</span><br/>`;
                            });
                            tooltipText += `<span style="color:#000;font-weight:bold">Total: ${total.toLocaleString('id-ID')}</span>`;
                            return tooltipText;
                        }
                    },
                    color: ['#609CFA', '#F87171', '#D8B4FE', '#86EFAC', '#FDBA74'],
                    legend: {
                        selectedMode: true,
                        textStyle: {
                            fontSize: 20,
                            color: '#A9A9A9',
                        }
                    },
                    grid,
                    yAxis: {
                        type: 'value',
                        min: 0,
                        max: Math.round(Math.max(actualHeight, standardQty) * 1.03),
                        axisLabel: {
                            fontSize: 17,
                            formatter: function(value) {
                                return value.toLocaleString('id-ID'); // Memformat angka dengan titik
                            },
                            color: '#A9A9A9', // Warna label berdasarkan mode
                        }
                    },
                    xAxis: {
                        type: 'category',
                        data: labels,
                        axisLabel: {
                            fontSize: 15,
                            color: '#A9A9A9',
                        }
                    },
                    series
                };

                // Menggunakan opsi yang telah ditentukan untuk menampilkan chart
                option && yearChart.setOption(option);
            }
        </script>
    @endpush

</x-app-layout>
