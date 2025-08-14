<x-app-layout>
    @section('title')
        Dashboard Keselamatan & Kesehatan Kerja
    @endsection
    <style>
        .dashboard-container {
            background-color: #FFFFFF;
            /* Dashboard container is white */
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            padding: 0;
            gap: 0;
            color: #333;
            overflow: hidden;
            height: 100%;
            /* Full height of the viewport */
            border: 5px solid black;
            /* THICK BLACK BORDER */
        }

        .header {
            background-color: #004d99;
            /* Header background is blue */
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 35px 20px;
            /* Internal header padding */
            color: white;
            border-top-left-radius: 5px;
            border-top-right-radius: 5px;
            margin-bottom: 0;
        }

        .header h1 {
            margin: 0;
            font-size: 2.0em;
            color: white;
            text-align: center;
            flex-grow: 1;
            padding: 0 10px;
        }

        .header .logo-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header .date-time {
            font-size: 1.4em;
            font-weight: bold;
            text-align: right;
            line-height: 1.3;
            color: white;
            align-self: flex-end;
            justify-self: flex-end;
        }

        /* NEW: Wrapper for all cards below the header */
        .main-cards-wrapper {
            padding: 20px;
            /* Padding for the content area, creating space from the border */
            display: grid;
            /* This is where the main 3-column grid is defined */
            grid-template-columns: 1fr 1.5fr 1fr;
            gap: 20px;
            /* Gap between cards */
            border-bottom-left-radius: 5px;
            border-bottom-right-radius: 5px;
            grid-template-rows: auto 1fr;
            /* First row auto height, second row fills remaining space */
        }

        /* Base card properties - common to all cards */
        .card {
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100px;
        }

        /* Styles for cards with the new split-background design */
        .split-bg-card {
            background-color: transparent;
            border: 2px solid #1769b3;
            overflow: hidden;
            padding: 0;
            color: #1769b3;
            justify-content: flex-start;
        }

        .split-bg-card .card-title-section {
            background-color: #004d99;
            color: white;
            padding: 10px 20px;
            font-size: 1.1em;
            font-weight: normal;
            text-align: center;
            border-top-left-radius: 6px;
            border-top-right-radius: 6px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 50px;
        }

        .split-bg-card .card-value-section {
            background-color: white;
            color: #1769b3;
            padding: 15px 20px;
            text-align: center;
            flex-grow: 1;
            display: flex;
            flex-direction: row;
            /* Horizontal alignment for number and label */
            justify-content: center;
            align-items: baseline;
            border-bottom-left-radius: 6px;
            border-bottom-right-radius: 6px;
        }

        .split-bg-card .card-value-section .value-number {
            font-size: 3.5em;
            font-weight: bold;
            margin: 0;
            line-height: 1;
        }

        .split-bg-card .card-value-section .value-label {
            font-size: 1.2em;
            font-weight: normal;
            margin-top: 0;
            margin-left: 8px;
            /* Space between number and label */
        }


        /* Styles for cards with solid blue background (Weather, Slogan) */
        .solid-blue-card {
            background-color: #1769b3;
            color: white;
        }

        .weather-card {
            grid-column: 3 / 4;
            grid-row: 1;
            min-height: 180px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .weather-card .value,
        .weather-card .condition {
            color: #fcf8f8;
            font-size: 2.2em;
        }

        .weather-card .value {
            font-size: 3em;
            font-weight: bold;
            margin: 0;
            padding: 0;
            border: none;
            background-color: transparent;
        }

        .weather-icon-container {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }

        .weather-icon-container img {
            width: 50px;
            height: 50px;
            margin-right: 10px;
        }


        /* Styles for the green Best Record card */
        .best-record-card {
            grid-column: 3 / 4;
            grid-row: 2 / span 2;
            /* Spans from Row 2 down to Row 3 of main-cards-wrapper */
            align-self: stretch;
            background-color: #28a745;
            color: white;
            padding: 20px;
        }

        .best-record-card h3 {
            font-size: 1.5em;
            color: white;
        }

        .best-record-card .value {
            font-size: 4em;
            color: white;
            background-color: transparent;
            border: none;
            padding: 0;
            margin: 10px 0;
        }

        .best-record-card .label {
            color: white;
        }


        /* ROW 1 (Visual Row 2 on image) - Relative to .main-cards-wrapper grid */
        .total-days-card {
            grid-column: 1 / 2;
            grid-row: 1;
            min-height: 180px;
        }

        .safety-icons-row {
            grid-column: 2 / 3;
            grid-row: 1;
            border-radius: 8px;
            padding: 15px;
            display: flex;
            justify-content: space-around;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            min-height: 180px;
        }

        .safety-icon-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            color: white;
            /* ENSURE TEXT IS WHITE */
            font-size: 0.9em;
            font-weight: bold;
        }

        .safety-icon-circle {
            background-color: #004d99;
            border-radius: 50%;
            width: 80px;
            height: 80px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
            transition: transform 0.35s cubic-bezier(.68, -0.55, .27, 1.55);
        }

        .safety-icon-item:hover .safety-icon-circle {
            transform: scale(1.18) rotate(-12deg);
        }

        .safety-icon-circle img {
            width: 50px;
            height: 50px;
        }

        /* Animasi floating untuk cards */
        @keyframes floatCard {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }

        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); }
            50% { box-shadow: 0 8px 25px rgba(23, 105, 179, 0.3); }
        }

        @keyframes bounceIcon {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }

        @keyframes rotateWeather {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes slideInNumber {
            0% { transform: scale(0.8) rotate(5deg); opacity: 0.7; }
            50% { transform: scale(1.1) rotate(-2deg); opacity: 1; }
            100% { transform: scale(1) rotate(0deg); opacity: 1; }
        }

        @keyframes weatherPulse {
            0%, 100% { transform: scale(1) brightness(1); }
            25% { transform: scale(1.1) brightness(1.2); }
            50% { transform: scale(0.95) brightness(0.9); }
            75% { transform: scale(1.05) brightness(1.1); }
        }

        .card:nth-child(1) {
            animation: floatCard 6s ease-in-out infinite;
            animation-delay: 0s;
        }

        .card:nth-child(3) {
            animation: floatCard 5s ease-in-out infinite;
            animation-delay: 1s;
        }

        .safety-icons-row {
            animation: pulseGlow 4s ease-in-out infinite;
        }

        .safety-icon-circle {
            animation: bounceIcon 3s ease-in-out infinite;
        }

        .safety-icon-circle:nth-child(1) { animation-delay: 0s; }
        .safety-icon-circle:nth-child(2) { animation-delay: 0.5s; }
        .safety-icon-circle:nth-child(3) { animation-delay: 1s; }
        .safety-icon-circle:nth-child(4) { animation-delay: 1.5s; }

        .weather-icon-container img {
            animation: rotateWeather 20s linear infinite;
        }

        .value-number {
            animation: slideInNumber 2s ease-out;
        }

        .best-record-card {
            animation: floatCard 7s ease-in-out infinite;
            animation-delay: 2s;
        }

        /* Animasi khusus untuk target days card */
        .target-days-card {
            animation: floatCard 6.5s ease-in-out infinite;
            animation-delay: 0.8s;
        }

        /* Animasi continuous untuk accident date */
        .last-accident-date {
             animation: floatCard 6.5s ease-in-out infinite;
            animation-delay: 0.8s;
        }

        /* Animasi untuk weather icon */
        .weather-icon-container {
            animation: weatherPulse 4s ease-in-out infinite;
        }

        /* ROW 2 (Visual Row 3 on image) - Relative to .main-cards-wrapper grid */
        .stacked-cards-group {
            grid-column: 1 / 2;
            grid-row: 2 / span 1;
            display: flex;
            flex-direction: column;
            gap: 20px;
            align-self: stretch;
        }

        .working-days-card,
        .target-days-card {
            min-height: 100px;
        }

        .middle-col-stacked-content {
            grid-column: 2 / 3;
            grid-row: 2 / span 1;
            display: flex;
            flex-direction: column;
            gap: 20px;
            justify-content: flex-start;
            align-self: stretch;
        }

        .accident-metrics-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .accident-metrics-group .card {
            min-height: 100px;
        }

        .accident-metrics-group .card h3 {
            font-size: 1.2em;
        }

        .accident-metrics-group .card .value-number {
            font-size: 3.0em;
        }

        .accident-metrics-group .card .value-label {
            display: none;
        }

        .accident-metrics-group .card:last-child .value-number {
            font-size: 3.5em;
            line-height: 1.2;
            padding: 8px 10px;
        }

        .safety-slogan {
            background-color: #1769b3;
            padding: 15px 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 80px;
        }

        .safety-slogan h2 {
            margin: 0;
            font-size: 2.5em;
            color: white;
            line-height: 1.2;
        }

        .safety-slogan p {
            margin-top: 5px;
            font-size: 1.5em;
            color: white;
        }


        /* Adjustments for smaller screens */
        @media (max-width: 992px) {
            .dashboard-container {
                display: block;
                padding: 0;
            }

            .header {
                border-radius: 0;
            }

            .main-cards-wrapper {
                padding: 20px;
                grid-template-columns: 1fr 1fr;
                gap: 20px;
                grid-template-rows: auto;
            }

            .header,
            .safety-icons-row,
            .weather-card,
            .stacked-cards-group,
            .middle-col-stacked-content,
            .best-record-card {
                grid-column: auto;
                grid-row: auto;
            }

            .header {
                grid-column: 1 / -1;
            }

            .safety-icons-row {
                grid-column: 1 / -1;
            }

            .weather-card {
                grid-column: 1 / -1;
            }

            .stacked-cards-group {
                grid-column: 1 / -1;
            }

            .middle-col-stacked-content {
                grid-column: 1 / -1;
            }

            .best-record-card {
                grid-column: 1 / -1;
            }

            .accident-metrics-group {
                grid-column: auto;
                grid-row: auto;
                margin-top: 0;
            }
        }

        @media (max-width: 768px) {
            .main-cards-wrapper {
                grid-template-columns: 1fr;
            }

            .header h1 {
                font-size: 1.5em;
            }

            .header .date-time {
                font-size: 0.9em;
            }

            .safety-icons-row {
                flex-wrap: wrap;
            }

            .accident-metrics-group {
                grid-template-columns: 1fr;
            }
        }
    </style>
    </head>

    <body>
        <div class="dashboard-container">
            <div class="header">
                <div class="logo-container">
                    <img src="{{ asset('assets/images/logowhite.png') }}" alt="HSE Logo" style="height:100px;width:auto;">
                </div>
                <h1 style="font-size: 3.0em; font-weight: bold">DASHBOARD KESELAMATAN & KESEHATAN KERJA</h1>
                <div class="logo-container">
                    <img src="{{ asset('assets/images/logo/k3.png') }}" alt="K3 Logo" style="height:100px;width:auto;">
                    <div class="date-time">
                        <span id="current-date">{{ \Carbon\Carbon::now()->format('d F Y') }}</span><br>
                        <span id="current-time">{{ \Carbon\Carbon::now()->format('H:i') }}</span>
                    </div>
                </div>
            </div>

            <div class="main-cards-wrapper">
                <div class="card total-days-card split-bg-card">
                    <div class="card-title-section">
                        <h3>TOTAL HARI TANPA KECELAKAAN</h3>
                    </div>
                    <div class="card-value-section">
                        <div class="value-number" id="total-days-without-accident" style="font: bold 5.5em Arial, sans-serif; line-height: 1.2;">
                            {{ $total_days_without_accident_until_this_month }}</div>
                        <div class="value-label">HARI</div>
                    </div>
                </div>

                <div class="safety-icons-row solid-blue-card">
                    <div class="safety-icon-item">
                        <div class="safety-icon-circle" style="font-size:3.5em;">
                            ⛑️
                        </div>
                        <span>HELM</span>
                    </div>
                    <div class="safety-icon-item">
                        <div class="safety-icon-circle" style="font-size:3.5em;">
                            🦺
                        </div>
                        <span>ROMPI</span>
                    </div>
                    <div class="safety-icon-item">
                        <div class="safety-icon-circle" style="font-size:3.5em;">
                            👞
                        </div>
                        <span>SEPATU</span>
                    </div>
                    <div class="safety-icon-item">
                        <div class="safety-icon-circle" style="font-size:3.5em;">
                            😷
                        </div>
                        <span>MASKER</span>
                    </div>
                </div>

                <div class="card weather-card solid-blue-card">
                    <div class="weather-icon-container mr-10" style="gap: 16px; display: flex; align-items: center;">
                        {!! $weather_icon_url !!}
                        <div class="value temp" id="current-temperature">{{ $current_temperature }}°C</div>
                    </div>
                    <div class="condition" id="weather-condition">{{ $weather_condition }}</div>
                </div>

                <div class="stacked-cards-group">
                    <div class="card working-days-card split-bg-card">
                        <div class="card-title-section">
                            <h3>TOTAL HARI KERJA SAMPAI BULAN INI</h3>
                        </div>
                        <div class="card-value-section">
                            <div class="value-number" id="total-working-days">
                                {{ $total_working_days_until_this_month }}</div>
                            <div class="value-label">HARI</div>
                        </div>
                    </div>

                    <div class="card target-days-card split-bg-card">
                        <div class="card-title-section">
                            <h3>TARGET HARI KERJA TAHUN INI</h3>
                        </div>
                        <div class="card-value-section">
                            <div class="value-number" id="target-working-days">{{ $target_working_days_this_year }}
                            </div>
                            <div class="value-label">HARI</div>
                        </div>
                    </div>
                </div>

                <div class="middle-col-stacked-content">
                    <div class="accident-metrics-group">
                        <div class="card split-bg-card">
                            <div class="card-title-section">
                                <h3>KECELAKAAN BULAN INI</h3>
                            </div>
                            <div class="card-value-section">
                                <div class="value-number" id="accidents-this-month" style="font-size: 2.5em;line-height: 1.2;">
                                    {{ $accidents_this_month }}</div>
                                <div class="value-label"></div>
                            </div>
                        </div>
                        <div class="card split-bg-card last-accident-date">
                            <div class="card-title-section">
                                <h3>KECELAKAAN KERJA TERAKHIR</h3>
                            </div>
                            <div class="card-value-section">
                                <div class="value-number" id="last-accident-date"
                                    style="font-size: 2.5em; line-height: 1.2;">{{ $last_accident_date }}</div>
                                <div class="value-label"></div>
                            </div>
                        </div>
                    </div>

                    <div class="card safety-slogan solid-blue-card">
                        <h2>BEKERJA AMAN, <br> PULANG SELAMAT</h2>
                        <p>KESELAMATAN ADALAH TANGGUNG JAWAB SEMUA ORANG</p>
                    </div>
                </div>

                <div class="card best-record-card green-bg">
                    <h3 style="font-weight: 500;">REKOR TERBAIK TANPA KECELAKAAN</h3>
                    <div class="value" id="record-days-without-accident" style="font-size: 4.5em; font-weight: bold;">{{ $record_days_without_accident }}</div>
                    <div class="label" style="font-size: 2.5rem;">HARI</div>
                </div>
            </div>
        </div>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script>
            $(document).ready(function() {
                function updateDashboard() {
                    $.ajax({
                        url: "{{ url('dashboard/safety-board/api/safety-data') }}",
                        method: 'GET',
                        success: function(data) {
                            $('#total-days-without-accident').text(data
                                .total_days_without_accident_until_this_month);
                            $('#total-working-days').text(data.total_working_days_until_this_month);
                            $('#target-working-days').text(data.target_working_days_this_year);
                            $('#accidents-this-month').text(data.accidents_this_month);
                            $('#last-accident-date').text(data.last_accident_date);
                            $('#record-days-without-accident').text(data.record_days_without_accident);

                            // Update current date and time in header
                            var dateTimeParts = data.current_time.split(' ');
                            $('#current-date').text(dateTimeParts[0] + ' ' + dateTimeParts[1] + ' ' +
                                dateTimeParts[2]);
                            $('#current-time').text(dateTimeParts[3]);

                            // Update weather data
                            $('#current-temperature').text(data.current_temperature + '°C');
                            $('#weather-condition').text(data.weather_condition);
                            $('#weather-icon').attr('src', data.weather_icon_url); // Update icon source
                        },
                        error: function(xhr, status, error) {
                            console.error('Error fetching data:', error);
                            // Fallback weather data if API fails
                            $('#current-temperature').text('N/A');
                            $('#weather-condition').text('Tidak Tersedia');
                            $('#weather-icon').attr('src', 'https://placehold.co/50x50/FFF/000?text=❓');
                        }
                    });
                }

                // Initial call
                updateDashboard();

                // Set interval for every 10 seconds (10000 milliseconds)
                setInterval(updateDashboard, 10000);
            });
        </script>
    </body>

</x-app-layout>
