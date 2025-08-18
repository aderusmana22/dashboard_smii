<x-app-layout>
    @section('title')
        Dashboard Keselamatan & Kesehatan Kerja
    @endsection
    <style>
        /* --- STRUKTUR DASAR --- */
        html, body {
            height: 100%;
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f0f2f5; /* Memberi sedikit warna latar belakang halaman */
        }

        .dashboard-container {
            margin-top: 10px;
            display: flex;
            flex-direction: column;
            background-color: #FFFFFF;
            border-radius: 15px; /* Sedikit lebih besar */
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            color: #333;
            overflow: hidden;
            border: 8px solid black;
            box-sizing: border-box;
        }

        .header {
            background-color: #004d99;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px; /* Padding disesuaikan */
            color: white;
            flex-shrink: 0; /* Mencegah header menyusut */
        }

        .header h1 {
            margin: 0;
            font-size: 3em; /* Diperbesar */
            font-weight: bold;
            color: white;
            text-align: center;
            flex-grow: 1;
            padding: 0 20px;
        }

        .header .logo-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header .logo-container img {
            height: 90px; /* Logo diperbesar */
            width: auto;
        }

        .header .date-time {
            font-size: 1.6em; /* Diperbesar */
            font-weight: bold;
            text-align: right;
            line-height: 1.4;
            color: white;
        }

        .main-cards-wrapper {
            flex-grow: 1; /* Mengisi sisa ruang yang tersedia */
            padding: 25px;
            display: grid;
            grid-template-columns: 1fr 1.7fr 1fr; /* Kolom tengah lebih lebar */
            gap: 25px;
            grid-template-rows: auto 1fr;
            overflow-y: auto; /* Memungkinkan scroll jika konten terlalu banyak */
        }

        .card {
            border-radius: 12px; /* Sudut lebih tumpul */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 120px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        /* --- GAYA KARTU --- */
        .split-bg-card {
            background-color: transparent;
            border: 3px solid #1769b3;
            overflow: hidden;
            padding: 0;
            color: #1769b3;
            justify-content: flex-start;
        }

        .split-bg-card .card-title-section {
            background-color: #004d99;
            color: white;
            padding: 15px 25px;
            font-size: 1.4em; /* Diperbesar */
            font-weight: 600;
            text-align: center;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 60px;
        }

        .split-bg-card .card-value-section {
            background-color: white;
            color: #1769b3;
            padding: 20px;
            text-align: center;
            flex-grow: 1;
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: baseline;
        }

        .split-bg-card .card-value-section .value-number {
            font-size: 4.5em; /* Diperbesar */
            font-weight: bold;
            margin: 0;
            line-height: 1;
            transition: opacity 0.3s ease-in-out;
        }

        .split-bg-card .card-value-section .value-label {
            font-size: 1.5em; /* Diperbesar */
            font-weight: normal;
            margin-left: 12px;
        }

        .solid-blue-card {
            background-color: #1769b3;
            color: white;
        }

        .weather-card {
            grid-column: 3 / 4;
            grid-row: 1;
            min-height: 180px;
        }

        .weather-card .value {
            font-size: 4em; /* Diperbesar */
            font-weight: bold;
        }
        .weather-card .condition {
            font-size: 2.5em; /* Diperbesar */
        }

        .weather-icon-container {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }

        .weather-icon-container svg {
            width: 70px; /* Ikon diperbesar */
            height: 70px;
            margin-right: 15px;
        }

        .best-record-card {
            grid-column: 3 / 4;
            grid-row: 2 / span 1;
            align-self: stretch;
            background-color: #28a745;
            color: white;
            padding: 25px;
        }

        .best-record-card h3 {
            font-size: 1.6em; /* Diperbesar */
            color: white;
            font-weight: 600;
        }

        .best-record-card .value {
            font-size: 6em; /* Diperbesar */
            color: white;
            font-weight: bold;
            margin: 15px 0;
        }

        .best-record-card .label {
            font-size: 2.8em; /* Diperbesar */
            color: white;
        }

        .total-days-card .value-number {
            font-size: 7em !important; /* Paling besar dan penting */
        }

        .total-days-card {
            grid-column: 1 / 2;
            grid-row: 1;
            min-height: 180px;
        }

        .safety-icons-row {
            grid-column: 2 / 3;
            grid-row: 1;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            justify-content: space-around;
            align-items: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            min-height: 180px;
            position: relative;
            overflow: hidden;
        }

        .safety-icon-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            color: white;
            font-size: 1.2em; /* Diperbesar */
            font-weight: bold;
        }

        .safety-icon-circle {
            background-color: #004d99;
            border-radius: 50%;
            width: 100px; /* Diperbesar */
            height: 100px; /* Diperbesar */
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: transform 0.35s cubic-bezier(.68, -0.55, .27, 1.55);
            font-size: 4.5em; /* Emoji diperbesar */
        }

        .safety-icon-item:hover .safety-icon-circle {
            transform: scale(1.18) rotate(-12deg);
        }

        /* --- KUMPULAN KARTU BERTUMPUK --- */
        .stacked-cards-group, .middle-col-stacked-content {
            grid-row: 2 / span 1;
            display: flex;
            flex-direction: column;
            gap: 25px;
            align-self: stretch;
        }

        .stacked-cards-group { grid-column: 1 / 2; }
        .middle-col-stacked-content { grid-column: 2 / 3; }

        .accident-metrics-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .accident-metrics-group .value-number {
            font-size: 3.5em !important; /* Disesuaikan */
        }

        .safety-slogan {
            background-color: #1769b3;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            flex-grow: 1; /* Mengisi ruang tersedia di kolomnya */
        }

        .safety-slogan h2 {
            margin: 0;
            font-size: 3em; /* Diperbesar */
            color: white;
            line-height: 1.3;
            font-weight: 700;
        }
        .safety-slogan p {
            font-size: 1.2em;
            color: #e0e0e0;
            margin-top: 10px;
        }

        /* --- [BARU] GAYA MARQUEE --- */
        .marquee-container {
            width: 100%;
            background-color: #004d99;
            color: white;
            overflow: hidden;
            white-space: nowrap;
            box-sizing: border-box;
            padding: 15px 0; /* Diperbesar */
            border-top: 4px solid #FFFFFF;
            border-bottom: 4px solid #FFFFFF;
            flex-shrink: 0; /* Mencegah marquee menyusut */
            margin-bottom: 15px;
        }

        .marquee-content {
            display: inline-block;
            padding-left: 100%;
            animation: marquee 50s linear infinite; /* Durasi disesuaikan */
        }

        .marquee-content span {
            font-size: 2em; /* Diperbesar */
            font-weight: bold;
            margin-right: 150px; /* Jarak antar teks diperbesar */
        }

        @keyframes marquee {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }

        /* --- ANIMASI (TETAP SAMA) --- */
        @keyframes slideUpFadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes floatCard { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-10px); } }
        @keyframes pulseGlow { 0%, 100% { opacity: 0; transform: scale(0.9); } 50% { opacity: 1; transform: scale(1.1); } }
        .safety-icons-row::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%; border-radius: 12px; box-shadow: 0 8px 40px rgba(23, 105, 179, 0.5); opacity: 0; animation: pulseGlow 4s ease-in-out infinite; z-index: -1; }
        @keyframes bounceIcon { 0%, 20%, 50%, 80%, 100% { transform: translateY(0); } 40% { transform: translateY(-12px); } 60% { transform: translateY(-6px); } }
        @keyframes rotateWeather { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        @keyframes slideInNumber { from { transform: translateY(20px) scale(0.9); opacity: 0; } to { transform: translateY(0) scale(1); opacity: 1; } }
        .main-cards-wrapper > * { animation: slideUpFadeIn 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94) both; }
        .main-cards-wrapper > *:nth-child(1) { animation-delay: 0.1s; }
        .main-cards-wrapper > *:nth-child(2) { animation-delay: 0.2s; }
        .main-cards-wrapper > *:nth-child(3) { animation-delay: 0.3s; }
        .main-cards-wrapper > *:nth-child(4) { animation-delay: 0.4s; }
        .main-cards-wrapper > *:nth-child(5) { animation-delay: 0.5s; }
        .main-cards-wrapper > *:nth-child(6) { animation-delay: 0.6s; }
        .total-days-card { animation: floatCard 6s cubic-bezier(0.445, 0.05, 0.55, 0.95) infinite, slideUpFadeIn 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94) both; animation-delay: 0.1s, 0.1s; }
        .best-record-card { animation: floatCard 7s cubic-bezier(0.445, 0.05, 0.55, 0.95) infinite, slideUpFadeIn 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94) both; animation-delay: 2s, 0.6s; }
        .safety-icon-circle { animation: bounceIcon 3s ease-in-out infinite; }
        .safety-icon-item:nth-child(1) .safety-icon-circle { animation-delay: 0s; }
        .safety-icon-item:nth-child(2) .safety-icon-circle { animation-delay: 0.3s; }
        .safety-icon-item:nth-child(3) .safety-icon-circle { animation-delay: 0.6s; }
        .safety-icon-item:nth-child(4) .safety-icon-circle { animation-delay: 0.9s; }
        .weather-icon-container svg { animation: rotateWeather 25s linear infinite; }
        .value-number { animation: slideInNumber 1s ease-out forwards; animation-delay: 0.5s; }

        /* --- MEDIA QUERIES (Disederhanakan untuk tata letak yang lebih besar) --- */
        @media (max-width: 1200px) {
            .main-cards-wrapper {
                grid-template-columns: 1fr 1fr;
                grid-template-rows: auto auto auto; /* Penyesuaian baris */
            }
            .header h1 { font-size: 2.2em; }
            .header .date-time { font-size: 1.2em; }
            .safety-icons-row, .middle-col-stacked-content {
                grid-column: 1 / -1; /* Membentang penuh */
            }
            .best-record-card {
                grid-column: 2 / 3;
                grid-row: 1 / 2;
            }
        }

        @media (max-width: 768px) {
            .main-cards-wrapper {
                grid-template-columns: 1fr; /* Semua jadi satu kolom */
                padding: 15px;
                gap: 15px;
            }
            .header { flex-direction: column; padding: 15px; }
            .header h1 { font-size: 1.8em; order: 2; }
            .header .logo-container { order: 1; }
            .header .date-time { display: none; } /* Sembunyikan tanggal di mobile */
            .safety-icons-row { flex-wrap: wrap; gap: 10px; }
            .safety-icon-circle { width: 80px; height: 80px; font-size: 3.5em; }
            .accident-metrics-group { grid-template-columns: 1fr; }
        }
    </style>
    </head>

    <body>
        <div class="dashboard-container">
            <!-- HEADER -->
            <div class="header">
                <div class="logo-container">
                    <img src="{{ asset('assets/images/logowhite.png') }}" alt="HSE Logo">
                </div>
                <h1>DASHBOARD KESELAMATAN & KESEHATAN KERJA</h1>
                <div class="logo-container">
                    <img src="{{ asset('assets/images/logo/k3.png') }}" alt="K3 Logo">
                    <div class="date-time">
                        <span id="current-date">{{ \Carbon\Carbon::now()->format('d F Y') }}</span><br>
                        <span id="current-time">{{ \Carbon\Carbon::now()->format('H:i') }}</span>
                    </div>
                </div>
            </div>

            <!-- KONTEN UTAMA -->
            <div class="main-cards-wrapper">
                <!-- Kolom 1 -->
                <div class="card total-days-card split-bg-card">
                    <div class="card-title-section">
                        <h3>TOTAL HARI TANPA KECELAKAAN</h3>
                    </div>
                    <div class="card-value-section">
                        <div class="value-number" id="total-days-without-accident">{{ $total_days_without_accident_until_this_month }}</div>
                        <div class="value-label">HARI</div>
                    </div>
                </div>

                <!-- Kolom 2 -->
                <div class="safety-icons-row solid-blue-card">
                    <div class="safety-icon-item">
                        <div class="safety-icon-circle">⛑️</div>
                        <span>HELM</span>
                    </div>
                    <div class="safety-icon-item">
                        <div class="safety-icon-circle">🦺</div>
                        <span>ROMPI</span>
                    </div>
                    <div class="safety-icon-item">
                        <div class="safety-icon-circle">🥾</div>
                        <span>SEPATU</span>
                    </div>
                    <div class="safety-icon-item">
                        <div class="safety-icon-circle">😷</div>
                        <span>MASKER</span>
                    </div>
                </div>

                <!-- Kolom 3 -->
                <div class="card weather-card solid-blue-card">
                    <div class="weather-icon-container">
                        <div id="weather-icon-wrapper">{!! $weather_icon_url !!}</div>
                        <div class="value temp" id="current-temperature">{{ $current_temperature }}°C</div>
                    </div>
                    <div class="condition" id="weather-condition">{{ $weather_condition }}</div>
                </div>

                <!-- Kolom 1, Baris 2 -->
                <div class="stacked-cards-group">
                    <div class="card working-days-card split-bg-card">
                        <div class="card-title-section">
                            <h3>TOTAL HARI KERJA SAMPAI BULAN INI</h3>
                        </div>
                        <div class="card-value-section">
                            <div class="value-number" id="total-working-days">{{ $total_working_days_until_this_month }}</div>
                            <div class="value-label">HARI</div>
                        </div>
                    </div>
                    <div class="card target-days-card split-bg-card">
                        <div class="card-title-section">
                            <h3>TARGET HARI KERJA TAHUN INI</h3>
                        </div>
                        <div class="card-value-section">
                            <div class="value-number" id="target-working-days">{{ $target_working_days_this_year }}</div>
                            <div class="value-label">HARI</div>
                        </div>
                    </div>
                </div>

                <!-- Kolom 2, Baris 2 -->
                <div class="middle-col-stacked-content">
                    <div class="accident-metrics-group">
                        <div class="card split-bg-card">
                            <div class="card-title-section">
                                <h3>KECELAKAAN BULAN INI</h3>
                            </div>
                            <div class="card-value-section">
                                <div class="value-number" id="accidents-this-month">{{ $accidents_this_month }}</div>
                            </div>
                        </div>
                        <div class="card split-bg-card last-accident-date">
                            <div class="card-title-section">
                                <h3>KECELAKAAN KERJA TERAKHIR</h3>
                            </div>
                            <div class="card-value-section">
                                <div class="value-number" id="last-accident-date">{{ $last_accident_date }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="card safety-slogan solid-blue-card">
                        <h2>BEKERJA AMAN,<br>PULANG SELAMAT</h2>
                        <p>KESELAMATAN ADALAH TANGGUNG JAWAB SEMUA ORANG</p>
                    </div>
                </div>

                <!-- Kolom 3, Baris 2 -->
                <div class="card best-record-card">
                    <h3>REKOR TERBAIK TANPA KECELAKAAN</h3>
                    <div class="value" id="record-days-without-accident">{{ $record_days_without_accident }}</div>
                    <div class="label">HARI</div>
                </div>
            </div>

            <!-- MARQUEE -->
            <div class="marquee-container">
                <div class="marquee-content">
                    {{-- Ulangi konten beberapa kali agar animasi berjalan mulus --}}
                    @for ($i = 0; $i < 3; $i++)
                        @foreach ($marquee_texts as $text)
                            <span>{{ trim($text) }}</span>
                        @endforeach
                    @endfor
                </div>
            </div>
        </div>


        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
$(document).ready(function() {
    const $currentDate = $('#current-date');
    const $currentTime = $('#current-time');

    // Fungsi untuk update jam realtime
    function updateClock() {
        const now = new Date();
        const day = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
        const time = now.toLocaleTimeString('id-ID', { hour12: false });

        $currentDate.text(day);
        $currentTime.text(time);
    }

    // Update clock setiap 1 detik
    setInterval(updateClock, 1000);
    updateClock(); // panggil langsung biar ga nunggu 1 detik pertama

    // -------- Dashboard API --------
    const $totalDays = $('#total-days-without-accident');
    const $totalWorkingDays = $('#total-working-days');
    const $targetWorkingDays = $('#target-working-days');
    const $accidentsThisMonth = $('#accidents-this-month');
    const $lastAccidentDate = $('#last-accident-date');
    const $recordDays = $('#record-days-without-accident');
    const $currentTemp = $('#current-temperature');
    const $weatherCondition = $('#weather-condition');
    const $weatherIconWrapper = $('#weather-icon-wrapper');

    function updateTextWithAnimation(element, newText) {
        if (element.text() != newText) {
            element.fadeOut(300, function() {
                $(this).text(newText).fadeIn(300);
            });
        }
    }

    function updateDashboard() {
        $.ajax({
            url: "{{ url('dashboard/safety-board/api/safety-data') }}",
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                updateTextWithAnimation($totalDays, data.total_days_without_accident_until_this_month);
                updateTextWithAnimation($totalWorkingDays, data.total_working_days_until_this_month);
                updateTextWithAnimation($targetWorkingDays, data.target_working_days_this_year);
                updateTextWithAnimation($accidentsThisMonth, data.accidents_this_month);
                updateTextWithAnimation($lastAccidentDate, data.last_accident_date);
                updateTextWithAnimation($recordDays, data.record_days_without_accident);

                // Update cuaca
                updateTextWithAnimation($currentTemp, data.current_temperature + '°C');
                updateTextWithAnimation($weatherCondition, data.weather_condition);

                if ($weatherIconWrapper.html() !== data.weather_icon_url) {
                    $weatherIconWrapper.fadeOut(300, function() {
                        $(this).html(data.weather_icon_url).fadeIn(300);
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching data:', error);
                $currentTemp.text('N/A');
                $weatherCondition.text('Tidak Tersedia');
            },
            complete: function() {
                setTimeout(updateDashboard, 10000);
            }
        });
    }

    setTimeout(updateDashboard, 1500);
});
</script>

    </body>
</x-app-layout>