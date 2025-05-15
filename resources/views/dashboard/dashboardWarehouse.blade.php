<x-app-layout>
    @section('title')
        Dashboard Warehouse & Shipment
    @endsection

    <!-- WAREHOUSE OCCUPANCY -->
    <div class="grid grid-cols-5" style="column-gap: 0.75rem /* 12px */;">

        <div style="grid-column: span 5/span 5;">
            <div class="card border-2 border-success rounded-2xl">
                <div class="box-header flex justify-center items-center relative">
                    <h3 class="text-3xl font-medium">Finished Goods / Outward Warehouse</h3>
                </div>
                <div class="box-body grid grid-cols-5" style="column-gap: 0.75rem /* 12px */;">
                    <div>
                        <div class="box-header flex flex-col justify-start items-center">
                            <h3 class="box-title m-0 text-3xl">G2 | <span class="text-blue-500">16°C</span></h3>
                            <h3 id="G216DegreePallet" class="box-title m-0 text-2xl">0 PP</h3>
                        </div>
                        <div id="G216Degree" style="height:350px;"></div>
                        <div class="flex justify-center items-center">
                            <p id="G216DegreeCapacity" class="text-2xl">Capacity: 0 Ton</p>
                        </div>
                    </div>
                    <div>
                        <div class="box-header flex flex-col justify-start items-center">
                            <h3 class="box-title m-0 text-3xl">G2 | <span class="text-green-500">25°C</span></h3>
                            <h3 id="G225DegreePallet" class="box-title m-0 text-2xl">0 PP</h3>
                        </div>
                        <div id="G225Degree" style="height:350px;"></div>
                        <div class="flex justify-center items-center">
                            <p id="G225DegreeCapacity" class="text-2xl">Capacity: 0 Ton</p>
                        </div>
                    </div>
                    <div>
                        <div class="box-header flex flex-col justify-start items-center">
                            <h3 class="box-title m-0 text-3xl">G3 | <span class="text-green-500">25°C</span></h3>
                            <h3 id="G325DegreePallet" class="box-title m-0 text-2xl">0 PP</h3>
                        </div>
                        <div id="G325Degree" style="height:350px;"></div>
                        <div class="flex justify-center items-center">
                            <p id="G325DegreeCapacity" class="text-2xl">Capacity: 0 Ton</p>
                        </div>
                    </div>
                    <div>
                        <div class="box-header flex flex-col justify-start items-center">
                            <h3 class="box-title m-0 text-3xl">G3 | <span style="color: #FB923C;">Ambient</span></h3>
                            <h3 id="G3AmbientPallet" class="box-title m-0 text-2xl">0 PP</h3>
                        </div>
                        <div id="G3Ambient" style="height:350px;"></div>
                        <div class="flex justify-center items-center">
                            <p id="G3AmbientCapacity" class="text-2xl">Capacity: 0 Ton</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 items-start">
                        <div class="flex justify-center self-center text-3xl col-span-2 " style=""> G3 Act. Temp.
                        </div>
                        <div class="flex justify-center text-3xl">Atas</div>
                        <div class="flex text-4xl">
                            <p id="tempAtas"></p>
                        </div>
                        <div class="flex justify-center text-3xl">Tengah</div>
                        <div class="flex text-4xl">
                            <p id="tempTengah"></p>
                        </div>
                        <div class="flex justify-center text-3xl">Bawah</div>
                        <div class="flex text-4xl">
                            <p id="tempBawah"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- DISPATCH -->
    <div class="grid grid-cols-2 gap-x-2 mb-4">
        <!-- Area Chart -->
        <div class="col-span-3" stlye="margin-top:10px;">
            <div class="box rounded-2xl">
                <div class="box-body analytics-info">
                    <div class="flex py-2" style="width:100%; justify-content:space-between;">
                        <div class="text-3xl font-medium">Production vs Monthly Dispatch</div>
                        <div>
                            <!-- Daftar Tahun -->
                            <select id="yearFilterArea" class="mr-2 bg-gray-500 text-xl rounded text-black">
                                @php
                                    $currentYear = date('Y'); // Mengambil tahun saat ini
                                @endphp
                                <option value="" class="" disabled selected hidden>{{ $currentYear }}
                                </option>

                                @for ($i = $currentYear; $i >= $currentYear - 10; $i--)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                            <!-- Daftar bulan -->
                            <select id="monthFilterArea" class="mr-2 bg-gray-500 text-xl rounded text-black">
                                <option value="" class="" disabled selected hidden>{{ date('F') }}
                                </option>
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}">{{ date('F', mktime(0, 0, 0, $i, 1)) }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="flex">
                        <canvas id="myBarChartMonthly" width="100%" height="48"
                            style="max-width:48%; height:800px;"></canvas>
                        <canvas id="myAreaChart" width="100%" height="48" style="max-width:48%;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>


    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-annotation/0.5.7/chartjs-plugin-annotation.min.js">
        </script>
        <script src="https://cdn.jsdelivr.net/npm/echarts@5.5.1/dist/echarts.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@0.7.0"></script>

        <script>
            // Mengambil dark mode
            const darkModeStorage = localStorage.getItem('darkMode');

            const myCharts = {}; // Init for gauge charts
            var myLineChart; // Init for daily line chart
            var myLineChartMonthly; // Init for monthly line chart

            document.addEventListener('DOMContentLoaded', function() {

                // Set nilai default untuk yearFilterArea ke tahun saat ini
                const currentYear = new Date().getFullYear();
                const yearFilter = document.getElementById('yearFilterArea');
                if (yearFilter) {
                    yearFilter.value = currentYear; // Set nilai dropdown ke tahun saat ini
                }

                // Panggil fungsi updateLineChart untuk memuat data awal
                updateLineChart();
                // Set font color based on dark mode
                setFontColor();

                createGaugeChart();
                createDailyChart();
                createMonthlyChart();



                // Set interval untuk memperbarui data setiap 5 detik
                setInterval(function() {
                    updateLineChart();
                    getWarehouseData();
                }, 5000); // 5000 milidetik = 5 detik

                // Panggil getWarehouseData() saat halaman dimuat
                getWarehouseData();

                // Membuat semua chart ECharts responsif
                makeChartsResponsive();
            });

            // Fungsi untuk mengatur warna font berdasarkan mode gelap
            function setFontColor() {
                const elements = document.querySelectorAll('.text-black, .text-white'); // Ganti dengan kelas yang sesuai
                elements.forEach(element => {
                    if (darkModeStorage === 'enabled') {
                        element.classList.remove('text-black');
                        element.classList.add('text-white');
                    } else {
                        element.classList.remove('text-white');
                        element.classList.add('text-black');
                    }
                });
            }

            // Fungsi untuk membuat chart ECharts responsif
            function makeChartsResponsive() {
                window.addEventListener('resize', function() {
                    Object.values(myCharts).forEach(chart => chart.resize());
                });
            }

            // Mendapatkan data warehouse
            function getWarehouseData() {
                fetch('/warehouse-data')
                    .then(response => response.json())
                    .then(data => {
                        // console.log('Warehouse Data:', data);
                        const warehouseData = data.warehouse_data || {};
                        const temperatureData = data.temperature_data || {};

                        const updateElement = (id, value, unit) => {
                            const element = document.getElementById(id);
                            if (element) {
                                element.innerHTML = `${value} ${unit}`;
                            } else {
                                console.error(`Element with ID ${id} not found.`);
                            }
                        };

                        const updateTemperatureElement = (id, value) => {
                            const element = document.getElementById(id);
                            if (element) {
                                element.innerHTML = `${value}°C`;
                            } else {
                                console.error(`Element with ID ${id} not found.`);
                            }
                        };

                        // Ambil suhu dari temperature_data untuk setiap area
                        const getTemperatureValue = (temperatureData, areaName) => {
                            if (!Array.isArray(temperatureData)) {
                                console.error('temperatureData is not an array');
                                return '0';
                            }
                            const device = temperatureData.find(device => device.area_name === areaName && device
                                .device_status === "IN");
                            return device ? device.device_value :
                                '0'; // Mengembalikan '0' jika tidak ada perangkat yang valid
                        };

                        // Pastikan temperatureData['G3 Ambience'] adalah array
                        const g3AmbienceTemps = temperatureData['G3 Ambience'] || [];

                        // Update suhu untuk Atas, Tengah, dan Bawah
                        updateTemperatureElement('tempAtas', getTemperatureValue(g3AmbienceTemps, 'Warehouse G3 Atas'));
                        updateTemperatureElement('tempTengah', getTemperatureValue(g3AmbienceTemps, 'Warehouse G3 Tengah'));
                        updateTemperatureElement('tempBawah', getTemperatureValue(g3AmbienceTemps, 'Warehouse G3 Bawah'));

                        const callCreateGaugeChart = (id, data, tempData) => {
                            if (data) {
                                if (tempData) {
                                    const validTemps = tempData.filter(device => device.device_status === "IN");
                                    const actualTemp = validTemps.length > 0 ?
                                        validTemps.reduce((sum, device) => sum + device.device_value, 0) / validTemps
                                        .length :
                                        null;
                                    createGaugeChart(id, data.pallet_occupancy, Math.round(data.total_ton),
                                        actualTemp, Math.round(data.total_pallet));
                                } else {
                                    // console.warn(`Temperature data for ${id} not found.`);
                                    createGaugeChart(id, data.pallet_occupancy, Math.round(data.total_ton), null);
                                }
                            } else {
                                // console.error(`Data for ${id} not found.`);
                                createGaugeChart(id, 0, 0, null); // Default values if data is not found
                            }
                        };

                        if (warehouseData['G2 16 C']) {
                            updateElement('G216DegreePallet', warehouseData['G2 16 C'].total_pallet_rack.toLocaleString(
                                'id-ID'), 'PP');
                            updateElement('G216DegreeCapacity',
                                `Capacity: ${warehouseData['G2 16 C'].total_estimated_tonnage.toLocaleString('id-ID')}`,
                                'Ton');
                            callCreateGaugeChart('G216Degree', warehouseData['G2 16 C'], temperatureData['G2 16 C']);
                        } else {
                            updateElement('G216DegreePallet', '0', 'PP');
                            updateElement('G216DegreeCapacity', 'Capacity: 0', 'Ton');
                            callCreateGaugeChart('G216Degree', null, null);
                        }

                        if (warehouseData['G2 25 C']) {
                            updateElement('G225DegreePallet', warehouseData['G2 25 C'].total_pallet_rack.toLocaleString(
                                'id-ID'), 'PP');
                            updateElement('G225DegreeCapacity',
                                `Capacity: ${warehouseData['G2 25 C'].total_estimated_tonnage}`, 'Ton');
                            callCreateGaugeChart('G225Degree', warehouseData['G2 25 C'], temperatureData['G2 25 C']);
                        } else {
                            updateElement('G225DegreePallet', '0', 'PP');
                            updateElement('G225DegreeCapacity', 'Capacity: 0', 'Ton');
                            callCreateGaugeChart('G225Degree', null, null);
                        }

                        if (warehouseData['G3 25 C']) {
                            updateElement('G325DegreePallet', warehouseData['G3 25 C'].total_pallet_rack.toLocaleString(
                                'id-ID'), 'PP');
                            updateElement('G325DegreeCapacity',
                                `Capacity: ${warehouseData['G3 25 C'].total_estimated_tonnage.toLocaleString('id-ID')}`,
                                'Ton');
                            callCreateGaugeChart('G325Degree', warehouseData['G3 25 C'], temperatureData['G3 25 C']);
                        } else {
                            updateElement('G325DegreePallet', '0', 'PP');
                            updateElement('G325DegreeCapacity', 'Capacity: 0', 'Ton');
                            callCreateGaugeChart('G325Degree', null, null);
                        }

                        if (warehouseData['G3 Ambience']) {
                            updateElement('G3AmbientPallet', warehouseData['G3 Ambience'].total_pallet_rack.toLocaleString(
                                'id-ID'), 'PP');
                            updateElement('G3AmbientCapacity',
                                `Capacity: ${warehouseData['G3 Ambience'].total_estimated_tonnage.toLocaleString('id-ID')}`,
                                'Ton');

                            // Menghitung rata-rata suhu untuk G3 Ambience
                            const g3AmbienceTemps = temperatureData['G3 Ambience'].filter(device =>
                                device.area_name === "Warehouse G3 Atas" ||
                                device.area_name === "Warehouse G3 Tengah" ||
                                device.area_name === "Warehouse G3 Bawah"
                            ).filter(device => device.device_status === "IN");
                            callCreateGaugeChart('G3Ambient', warehouseData['G3 Ambience'], g3AmbienceTemps);
                        } else {
                            updateElement('G3AmbientPallet', '0', 'PP');
                            updateElement('G3AmbientCapacity', 'Capacity: 0', 'Ton');
                            callCreateGaugeChart('G3Ambient', null, null);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching warehouse data:', error);
                    });
            }

            function createGaugeChart(temp, occupancy, total_ton, actualTemp, total_pallet) {
                const gauge = document.getElementById(`${temp}`);
                if (!gauge) {
                    // console.error(`Element with ID ${temp} not found.`);
                    return;
                }
                let normalText;

                // jika darkmode, init dark
                if (darkModeStorage === 'enabled') {
                    myCharts[temp] = echarts.init(gauge, 'dark');
                    normalText = 'grey';
                } else {
                    myCharts[temp] = echarts.init(gauge);
                    normalText = 'grey';
                }

                var number = (occupancy).toFixed(1); // Use occupancy value
                let bgColor =
                    number >= 90 ? '#FF5E5C' :
                    number >= 70 ? '#FFB95C' :
                    number <= 10 ? '#FF5E5C' :
                    number <= 20 ? '#FFB95C' :
                    ''; // default

                let txtColor =
                    number >= 90 ? 'white' :
                    number >= 70 ? 'black' :
                    number <= 10 ? 'white' :
                    number <= 20 ? 'black' :
                    normalText; // default

                let tempColor =
                    temp === "G1Ambient" ? '#F97316' :
                    temp === "G216Degree" ? '#3B82F6' :
                    temp === "G225Degree" ? '#22C55E' :
                    temp === "G325Degree" ? '#22C55E' :
                    temp === "G3Ambient" ? '#FB923C' :
                    'orange'; // default

                let data2 = {
                    value: actualTemp !== null ? actualTemp.toFixed(1) : 'N/A',
                    // name: 'Act. Temp.',
                    itemStyle: {
                        color: tempColor
                    },
                    title: {
                        fontSize: 20,
                        offsetCenter: ['0%', '30%']
                    },
                    detail: {
                        show: true,
                        fontSize: 25,
                        color: tempColor,
                        formatter: '{value}°C',
                        offsetCenter: ['0%', '55%']
                    }
                };

                const gaugeData = [{
                    value: number,
                    name: 'Occupancy',
                    itemStyle: {
                        color: tempColor
                    },
                    title: {
                        fontSize: window.innerWidth > 768 ? 25 : 15,
                        offsetCenter: ['0%', '-40%'],
                        color: tempColor
                    },
                    detail: {
                        valueAnimation: true,
                        fontSize: window.innerWidth > 768 ? 30 : 20,
                        formatter: '{value}%',
                        offsetCenter: ['0%', '-10%'],
                        lineHeight: 25,
                        width: 100,
                        height: 20,
                        color: tempColor,
                        rich: {}
                    }
                }];
                if (temp !== "G1Ambient") {
                    gaugeData.push(data2);
                }
                const option = {
                    series: [{
                        type: 'gauge',
                        startAngle: 90,
                        endAngle: -270,
                        pointer: {
                            show: false
                        },
                        progress: {
                            show: true,
                            overlap: true,
                            roundCap: false,
                            clip: false,
                            width: 20
                        },
                        axisLine: {
                            lineStyle: {
                                shadowColor: 'black',
                                shadowBlur: 4,
                                shadowOffsetX: 0,
                                shadowOffsetY: 0,
                                width: 20
                            }
                        },
                        splitLine: {
                            show: false
                        },
                        axisTick: {
                            show: false
                        },
                        axisLabel: {
                            show: false
                        },
                        data: gaugeData,
                    }],
                    title: {
                        text: `Actual: ${total_ton.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 })} Ton | ${total_pallet.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 })} PP`,
                        left: 'center',
                        top: '1%',
                        bottom: '5%',
                        textStyle: {
                            fontSize: window.innerWidth > 768 ? 20 : 15,
                            color: tempColor
                        }
                    }
                };

                option && myCharts[temp].setOption(option);
            }


            // create line chart for daily
            function createDailyChart() {
                var dailyChart = document.getElementById("myAreaChart");
                myLineChart = new Chart(dailyChart, {
                    type: 'line',
                    data: {
                        labels: [],
                        datasets: [{
                            label: "Dispatch (In Tonnage)",
                            lineTension: 0.3,
                            backgroundColor: "rgba(2,117,216,0.2)",
                            borderColor: "rgba(2,117,216,1)",
                            pointRadius: 5,
                            pointBackgroundColor: "rgba(2,117,216,1)",
                            pointBorderColor: "rgba(255,255,255,0.8)",
                            pointHoverRadius: 5,
                            pointHoverBackgroundColor: "rgba(2,117,216,1)",
                            pointHitRadius: 50,
                            pointBorderWidth: 2,
                            data: [],
                        }],
                    },
                    options: {
                        scales: {
                            xAxes: [{
                                time: {
                                    unit: 'date'
                                },
                                gridLines: {
                                    display: false
                                }
                            }],
                            yAxes: [{
                                ticks: {
                                    min: 0,
                                    max: 100,
                                    maxTicksLimit: 5
                                },
                                gridLines: {
                                    color: "rgba(0, 0, 0, .125)",
                                },
                                scaleLabel: {
                                    display: true,
                                    labelString: '(Tonnage)',
                                },
                            }]
                        },
                        legend: {
                            display: false
                        }
                    }
                });
            }


            // create line chart for monthly
            function createMonthlyChart() {
                var monthlyChart = document.getElementById("myBarChartMonthly");
                myLineChartMonthly = new Chart(monthlyChart, {
                    type: 'line',
                    data: {
                        labels: [],
                        datasets: [{
                            label: "Dispatch (In Tonnage)",
                            lineTension: 0.3,
                            backgroundColor: "rgba(2,117,216,0.2)",
                            borderColor: "rgba(2,117,216,1)",
                            pointRadius: 5,
                            pointBackgroundColor: "rgba(2,117,216,1)",
                            pointBorderColor: "rgba(255,255,255,0.8)",
                            pointHoverRadius: 5,
                            pointHoverBackgroundColor: "rgba(2,117,216,1)",
                            pointHitRadius: 50,
                            pointBorderWidth: 2,
                            data: [],
                        }],
                    },
                    options: {
                        title: {
                            display: true,
                            text: 'Monthly Dispatch',
                            fontSize: 25,
                            color: 'grey'
                        },
                        scales: {
                            xAxes: [{
                                time: {
                                    unit: 'date'
                                },
                                gridLines: {
                                    display: false
                                }
                            }],
                            yAxes: [{
                                ticks: {
                                    min: 0,
                                    max: 100,
                                    maxTicksLimit: 5
                                },
                                gridLines: {
                                    color: "rgba(0, 0, 0, .125)",
                                }
                            }]
                        },
                        legend: {
                            display: false
                        }
                    }
                });
            }

            // set new data for daily line chart
            function setDailyChartOption(label, ton) {
                myLineChart.data.labels = label;
                myLineChart.data.datasets[0].data = ton;
                myLineChart.options = {
                    title: {
                        display: true,
                        text: 'Daily Dispatch',
                        fontSize: 25,
                        fontColor: '#007bff',
                    },
                    plugins: {
                        datalabels: {
                            anchor: 'end',
                            align: 'top',
                            color: '#A9A9A9', // abu-abu
                            font: {
                                weight: 'bold',
                                size: 16
                            },
                            formatter: function(value) {
                                return Math.round(value); // bulatkan tanpa koma
                            }
                        }
                    },
                    scales: {
                        xAxes: [{
                            time: {
                                unit: 'date'
                            },
                            gridLines: {
                                display: false
                            },
                            ticks: {
                                fontSize: 16
                            }
                        }],
                        yAxes: [{
                            ticks: {
                                min: 0,
                                max: Math.round(Math.max(...ton) * 1.1),
                                maxTicksLimit: 5,
                                fontSize: 22 // diperbesar dari 20 ke 28
                            },
                            gridLines: {
                                color: "rgba(0, 0, 0, .125)",
                            },
                            scaleLabel: {
                                display: true,
                                labelString: '(Tonnage)',
                                fontSize: 20 // diperbesar dari default
                            },
                        }]
                    },
                    annotation: {
                        annotations: [{
                            type: 'line',
                            mode: 'horizontal',
                            scaleID: 'y-axis-0',
                            value: Math.max(...ton),
                            borderColor: 'red',
                            borderWidth: 1,
                            label: {
                                enabled: true,
                                content: 'Threshold',
                                position: 'center'
                            }
                        }]
                    },
                    legend: {
                        display: false
                    },
                    tooltips: {
                        titleFontSize: 22,
                        bodyFontSize: 20,
                        displayColors: false,
                        backgroundColor: '#FFF',
                        titleFontColor: '#0066ff',
                        bodyFontColor: '#000',
                    }
                };
                myLineChart.update();
            }

            // set new data for monthly line chart
            let myBarChartMonthly;

            function setMonthlyChartOption(data, year) {
                const labels = data.map(item => item.month);
                const dispatchData = data.map(item => parseFloat(item.total_dispatch).toFixed(2));
                const productionData = data.map(item => parseFloat(item.total_production).toFixed(2));

                if (myBarChartMonthly) {
                    myBarChartMonthly.destroy();
                }

                const ctx = document.getElementById("myBarChartMonthly").getContext("2d");

                myBarChartMonthly = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                                label: "Production (In Tonnage)",
                                backgroundColor: "rgba(92,184,92,0.5)",
                                borderColor: "rgba(92,184,92,1)",
                                borderWidth: 2,
                                barThickness: 30,
                                maxBarThickness: 50,
                                data: productionData,
                                yAxisID: "yProduction",
                                datalabels: {
                                    anchor: 'end',
                                    align: 'top',
                                    color: '#A9A9A9', // abu-abu
                                    font: {
                                        weight: 'bold',
                                        size: 16
                                    },
                                    formatter: val => Math.round(val) // bulatkan tanpa koma
                                }
                            },
                            {
                                label: "Dispatch (In Tonnage)",
                                backgroundColor: "rgba(2,117,216,0.5)",
                                borderColor: "rgba(2,117,216,1)",
                                borderWidth: 2,
                                barThickness: 30,
                                maxBarThickness: 50,
                                data: dispatchData,
                                yAxisID: "yDispatch",
                                datalabels: {
                                    anchor: 'end',
                                    align: 'top',
                                    color: '#A9A9A9', // abu-abu
                                    font: {
                                        weight: 'bold',
                                        size: 16
                                    },
                                    formatter: val => Math.round(val) // bulatkan tanpa koma
                                }
                            }
                        ],
                    },
                    options: {
                        plugins: {
                            datalabels: {
                                anchor: 'end',
                                align: 'top',
                                color: '#A9A9A9',
                                font: {
                                    weight: 'bold',
                                    size: 35
                                },
                                formatter: function(value) {
                                    return value;
                                }
                            }
                        },
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltips: {
                            titleFontSize: 22,
                            bodyFontSize: 20,
                            displayColors: false,
                            backgroundColor: '#FFF',
                            titleFontColor: '#0066ff',
                            bodyFontColor: '#000',
                        },
                        scales: {
                            xAxes: [{
                                gridLines: {
                                    display: false,
                                },
                                ticks: {
                                    fontSize: 16,
                                },
                            }],
                            yAxes: [{
                                    id: 'yProduction',
                                    type: 'linear',
                                    position: 'right',
                                    display: false,
                                    ticks: {
                                        beginAtZero: true,
                                        fontSize: 18,
                                    },
                                    gridLines: {
                                        drawOnChartArea: false,
                                    },
                                },
                                {
                                    id: 'yDispatch',
                                    type: 'linear',
                                    position: 'left',
                                    display: true,
                                    ticks: {
                                        beginAtZero: true,
                                        fontSize: 20,
                                    },
                                    scaleLabel: {
                                        display: true,
                                        labelString: '(Tonnage)',
                                        fontSize: 18 // diperbesar dari default
                                    },
                                }
                            ],
                        },
                        animation: {
                            duration: 0,
                        },
                    },
                    plugins: [ChartDataLabels] // <-- ini penting agar plugin aktif
                });
            }



            // Update berdasarkan bulan dan minggu yang dipilih
            document.getElementById('monthFilterArea').addEventListener('change', updateLineChart);
            document.getElementById('yearFilterArea').addEventListener('change', updateLineChart);

            // Update daily dan monthly chart
            function updateLineChart() {
                const year = document.getElementById('yearFilterArea').value;
                const month = document.getElementById('monthFilterArea').value;

                // Fetch data untuk chart bulanan dan harian
                fetch(`/area-data?year=${year}&month=${month}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.monthlyData) {
                            setMonthlyChartOption(data.monthlyData, year); // Kirim tahun ke fungsi
                        }
                        if (data && data.labels && data.tons) {
                            setDailyChartOption(data.labels, data.tons.map(value => parseInt(value.replace(/\./g, ''),
                                10)));
                        }
                    })
                    .catch(error => console.error('Error fetching area chart data:', error));
            }
        </script>
    @endpush

</x-app-layout>
