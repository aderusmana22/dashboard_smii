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

        /* --- GAYA MARQUEE YANG DIPERBAIKI --- */
.marquee {
    --gap: 3rem; /* Sesuaikan jarak antar teks */
    display: flex;
    overflow: hidden;
    user-select: none;
    gap: var(--gap);
    background-color: #004d99;
    color: white;
    padding: 15px 0;
    border-top: 4px solid #FFFFFF;
    border-bottom: 4px solid #FFFFFF;
    margin-bottom: 15px;
}

.marquee__content {
    flex-shrink: 0;
    display: flex;
    justify-content: space-around;
    gap: var(--gap);
    min-width: 100%;
    animation: scroll 20s linear infinite; /* Durasi disesuaikan */
}

.marquee__content span {
    font-size: 2em; /* Diperbesar */
    font-weight: bold;
    white-space: nowrap; /* Pastikan teks tidak terpotong */
}

@keyframes scroll {
  from {
    transform: translateX(0);
  }
  to {
    transform: translateX(calc(-100% - var(--gap)));
  }
}

/* Jeda animasi saat kursor diarahkan */
.marquee:hover .marquee__content {
  animation-play-state: paused;
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
                        <div class="safety-icon-circle">
                    <svg xmlns="http://www.w3.org/2000/svg" width="128" height="128" viewBox="0 0 128 128"><path fill="#784d30" d="m20.85 59.93l86.68.3v14.18c0 23.01-17.45 41.65-38.88 41.65h-9.32c-21.42 0-38.78-18.64-38.88-41.55zm-7.74-8.03l-.6 22.51c0 27.37 20.93 49.59 46.81 49.59h9.32c25.88 0 46.81-22.21 46.81-49.59V52.3z"/><path fill="#b0805c" d="M71.42 124H56.35c-4.66 0-8.33-3.77-8.33-8.33v-.6c0-1.69 1.29-2.98 2.98-2.98h25.79c1.69 0 2.98 1.29 2.98 2.98v.6c-.02 4.56-3.78 8.33-8.35 8.33"/><path fill="#4f3320" d="M112.11 63.02c-1.46-.6-3.04-.64-4.59-.31v11.71c0 4.64-.72 9.1-2.03 13.28c.58-.92 1.21-1.83 2.04-2.53c1.89-1.59 4.79-1.83 6.92-.59c.01 0 .01.01.02.02c.65-3.28.99-6.68.99-10.17v-8.59c-.87-1.23-1.96-2.25-3.35-2.82M22.06 86.19a44.8 44.8 0 0 1-1.61-11.68l.19-6.8c-.76-1.79-1.98-3.53-3.85-3.95c-1.39-.31-2.83.22-4.01 1.02l-.25 9.63c0 2.83.24 5.61.67 8.31c3.21.18 6.36 1.42 8.86 3.47"/><path fill="#c62828" d="M63.99 49.38c23.47 0 59.4 11.54 59.4 11.54c.57 2.03.1 5.02.1 5.02c0 9.15-26.64 16.56-59.5 16.56s-59.5-7.42-59.5-16.56c0 0-.38-1.67.07-4.77c-.01 0 37.45-11.79 59.43-11.79"/><path fill="#f44336" d="M117.01 54.96c-1.21-15.3-9.97-40.35-36.36-47.87v-.41C80.65 5.19 79.3 4 77.61 4H50.39c-1.69 0-3.04 1.19-3.04 2.68v.39C21.07 14.5 12.23 39.3 11 54.65c-1.54 2.36-6.51 4.31-6.51 7.1c0 9.15 26.64 16.56 59.5 16.56s59.5-7.42 59.5-16.56c.01-3.59-5.16-4.17-6.48-6.79"/><path fill="#c62828" d="M39.5 38.84c2.82-3.47 6.03-9.08 7.21-17.46c.59-4.15.63-11.94.63-14.3a49 49 0 0 0-8.72 3.37c1.07 2.89 2.44 7.43 2.27 13.32c-.17 5.68-1.69 10.52-3.19 13.98c-.49 1.13 1.02 2.05 1.8 1.09m48.88 0c.78.96 2.29.04 1.8-1.09c-1.5-3.46-3.02-8.3-3.19-13.98c-.17-5.92 1.21-10.48 2.28-13.36c-2.68-1.35-5.6-2.48-8.77-3.37c-.1 2.33-.37 10.25.66 14.33c2.16 8.56 4.4 14.01 7.22 17.47"/><path fill="#ff7555" d="M33.64 31.53a46.4 46.4 0 0 0-7.54 7.74c-1.03 1.34-2.14 4.02-4.42 3.51s-2.22-2.67-1.76-4.78c1.2-5.4 3.98-11.02 8.58-14.18c2.94-2.02 5.3-1.93 6.82.18c2.18 3.02.89 5.44-1.68 7.53m5.41 36.02c8.29.91 6.86 3.44-1.22 4c-8.07.55-22.53-2.19-25.75-6.21c-1.61-2 .87-5 4.49-3.58c7.22 2.84 7.37 4.13 22.48 5.79"/><path fill="#80ff00" d="M80.27 38.32h-9.08c-.85 0-1.53-.72-1.53-1.61v-9.54c0-.89-.69-1.61-1.53-1.61h-8.26c-.85 0-1.53.72-1.53 1.61v9.54c0 .89-.69 1.61-1.53 1.61h-9.08c-.85 0-1.53.72-1.53 1.61v8.67c0 .89.69 1.61 1.53 1.61h9.08c.85 0 1.53.72 1.53 1.61v9.54c0 .89.69 1.61 1.53 1.61h8.26c.85 0 1.53-.72 1.53-1.61v-9.54c0-.89.69-1.61 1.53-1.61h9.08c.85 0 1.53-.72 1.53-1.61v-8.67c0-.89-.68-1.61-1.53-1.61"/></svg>    
                    </div>
                        <span>HELM</span>
                    </div>
                    <div class="safety-icon-item">
                        <div class="safety-icon-circle">
                            <svg xmlns="http://www.w3.org/2000/svg" width="128" height="128" viewBox="0 0 128 128"><path fill="#e64a19" d="M43.86 11.94S57.2 5.25 64 5.25s20.14 6.69 20.14 6.69v103.52s-7.87-3.22-20.14-3.22s-20.14 3.22-20.14 3.22z"/><path fill="#ff9100" d="M57.92 56.45c0-7.67-3.33-12.66-4.72-15.91c-2.26-5.31-3.22-10.81-3.41-16.38c-.18-5.39 2.15-13.38 5.23-17.86c-8.39 3.55-29.34 14.3-29.55 14.56c0 0 .61 19-1.68 27.84C20.12 62.88 12.2 68.86 12.2 68.86v48.4c6.71 2.22 24.96 4.35 36.26 5.52c2.04.21 6.47-.54 8.05-1.84c1.67-1.37 2.71-3.77 2.74-5.93c.23-19.42-1.33-50.88-1.33-58.56"/><path fill="#ff0" d="M12.23 105.94c13.87 4.17 41.09 5.89 46.95 4.86l-.19-8.12c-5.86-.02-32.74-.43-46.77-5.28zm46.76-21.58s-7.17.35-17.81-.71l1.31-71.46c-3.21 1.48-8.33 4.16-8.33 4.16l-.99 66.59c-7.47-.8-15.36-2.05-20.95-3.98v8.54c13.87 4.17 41.11 6.51 46.96 5.48z"/><path fill="#616161" d="M11.63 68.91c-.08-1.19-.05-1.66.37-2.13c.36-.41.77-.9 1.11-1.33c.98-1.22 1.93-2.46 2.83-3.74c2.08-2.94 4.06-6.1 5.23-9.53c2.24-6.6 3.15-13.6 3.37-20.56c.17-5.2-.45-10.04-.45-10.04s-.21-.44 1.03-1.09s1.76-.4 1.76-.4c.32 1.86.58 5.18.66 6.9c.78 15.38-2.62 23.58-3.5 26.17c-2.66 7.83-10.09 16.49-11.8 17.25c.02-.01-.52-.31-.61-1.5"/><path fill="#ff9100" d="M70.08 56.45c0-7.67 3.33-12.66 4.72-15.91c2.26-5.31 3.22-10.81 3.41-16.38c.18-5.39-2.15-13.38-5.23-17.86c8.39 3.55 29.34 14.3 29.55 14.56c0 0-.61 19 1.68 27.84c3.67 14.18 11.59 20.16 11.59 20.16l-.05 48.1c-12.84 3.32-25.32 5.5-36.06 5.79c-2.05.05-6.05.23-8.21-1.81c-2.52-2.38-2.63-3.32-2.65-5.48c-.22-19.42 1.25-51.33 1.25-59.01"/><path fill="#ff0" d="M115.73 105.94c-13.87 4.17-41.05 5.89-46.9 4.86l.19-8.12c5.86-.02 32.7-.43 46.73-5.28zm-20.89-23l-.99-66.59s-5.12-2.68-8.33-4.16l1.31 71.46c-8.4 1.1-17.81 1.22-17.81 1.22l-.19 8.12c5.37.65 33.05-1.31 46.92-5.48v-8.54c-5.6 1.92-13.44 3.17-20.91 3.97"/><path fill="#616161" d="M116.37 68.91c.08-1.19.05-1.66-.37-2.13c-.36-.41-.77-.9-1.11-1.33c-.98-1.22-1.93-2.46-2.83-3.74c-2.08-2.94-4.06-6.1-5.23-9.53c-2.24-6.6-3.15-13.6-3.37-20.56c-.17-5.2.45-10.04.45-10.04s.21-.44-1.03-1.09s-1.76-.4-1.76-.4c-.32 1.86-.58 5.18-.66 6.9c-.78 15.38 2.62 23.58 3.5 26.17c2.66 7.83 10.09 16.49 11.8 17.25c-.02-.01.52-.31.61-1.5m-.62 46.03c-.36.16-1.43.4-1.94.48c-1.47.22-18.33 4.14-34.49 5.31c-1.62.07-4.78.15-7.32-1.75c-1.07-.8-1.49-2.61-1.48-3.56c.07-6.28 1.27-55.9 1.29-56.41c0-.01.45-6.97 2.13-12.1c2.72-8.33 5.98-19.77 6.04-26.96c.04-5.23-1.29-9.24-3.94-11.91C73.35 5.33 68.14 4 64 4s-9.35 1.33-12.03 4.03c-2.66 2.68-3.98 6.69-3.94 11.91c.06 7.19 3.31 18.63 6.04 26.96c1.68 5.13 2.12 12.09 2.13 12.1c.01.51 1.22 50.13 1.29 56.41c.01.94-.41 2.75-1.48 3.56c-2.53 1.91-5.7 1.83-7.32 1.75c-16.17-1.17-33-4.58-34.49-5c-.5-.14-1.58-.31-1.94-.48c0 0-.28-.03-.22 1.29c.03.79.1 1.25.31 1.51c.3.38.82.44 1.6.72c1.73.61 8.43 1.57 17.09 3.11c5.46.97 13.68 2.04 16.6 2.09c3.86.07 7.71.33 10.95-2.76c1.42-1.35 1.96-3.02 1.94-4.78c-.07-6.3-1.27-56.97-1.29-57.54c-.02-.3-.47-7.41-2.27-12.92c-3.81-11.65-5.85-20.66-5.89-26.04c-.03-4.39.99-7.67 3.06-9.75c1.99-2.01 5.4-3.03 9.87-3.11c4.47.08 7.87 1.1 9.87 3.11c2.07 2.08 3.09 5.36 3.06 9.75c-.04 5.38-2.08 14.39-5.89 26.04c-1.8 5.51-2.25 12.62-2.27 12.92c-.01.57-1.22 51.24-1.29 57.54c-.02 1.77.52 3.44 1.94 4.78c3.25 3.09 7.09 2.83 10.95 2.76c2.92-.05 11.14-1.12 16.6-2.09c8.66-1.53 15.35-2.83 17.09-3.39c.9-.29 1.3-.34 1.6-.72c.2-.26.27-.72.31-1.51c.05-1.31-.23-1.31-.23-1.31"/></svg>
                        </div>
                        <span>ROMPI</span>
                    </div>
                    <div class="safety-icon-item">
                        <div class="safety-icon-circle"><svg xmlns="http://www.w3.org/2000/svg" width="128" height="128" viewBox="0 0 128 128"><path fill="#263238" d="M12.78 29.74v-9.19c.98-1.14 3.82-3.48 10.83-4.16c1.86-.18 3.66-.27 5.35-.27c6.68 0 14.85.63 18.15 3.35c1.79 1.48 3.25 2.64 3.8 3.52L35.49 35.12z"/><path fill="#784d30" d="M118.95 94.9s-3.7-13.45-9.94-15.32c-4.67-1.4-9.57-1.61-14.4-1.94c-3.25-.22-6.3-1.09-8.75-3.31c-2.62-2.37-14.79-17.24-19.89-24.67c-1.93-2.81-.25-7.49.63-10.89c.88-3.41 2-6.85 1.77-10.42c-.26-3.87-2.2-6.47-8.3-7.26c-4.7-.61-7.03-.38-7.03-.38c-3.54-4.36-13.82-7.02-23.17-6.48c-6.17.36-14.64 1.19-19.46 5.52c-3.08 2.77-4.19 8.96-.27 13.17C6.88 35 6.5 38.99 7.4 41.89c.89 2.87 2.91 4.02 3.01 4.86c.1.81.44 8.81.82 11.96c.43 3.59-1.39 7.01-2.56 10.43c-.86 2.5-1.45 7.13-1.22 10.62c.07 1-.03 1.84.19 2.58c.22.73.75 1.37 1.14 1.69c7.91 6.45 24.66 10.61 28.2 12.7c3.54 2.08 21.02 14.15 32.26 16.03c11.24 1.87 25.7 1.47 38.6-6.65c12.9-8.15 11.11-11.21 11.11-11.21M25.04 18.62c14.97-.98 20.68 2.04 22.54 3.99c0 0-5.29 1.07-7.07 4.49c-.78 1.49-1.75 3.18-1.69 4.87c-3.51-3.06-7.2-2.42-11.52-2.7c-5.83-.38-12.48-2.16-12.31-5.72c.18-3.94 8.01-4.8 10.05-4.93"/><path fill="none" d="M34.86 29.25c-1.08 3.29.16 6.85 1.4 10.09c1.31 3.43 2.62 6.87 3.92 10.3"/><path fill="#a06841" d="M93.94 96.39c.89-.61 1.76-1.31 2.27-2.25s.62-2.19-.01-3.06c-.72-1-2.16-1.24-3.36-.93s-2.23 1.04-3.3 1.66c-1.39.8-4.83 2.12-7.95 2.98c-3.09.86-4.98 2.6-4.48 4.48c.85 3.21 6.28 1.73 8.08 1.25c3-.8 6.17-2.36 8.75-4.13M23.66 65.37c1.63 2.37 1.34 6.02-.93 7.78c-1.2.93-2.74 1.27-4.03 2.06c-.77.47-2.71 2.51-4.81 1.98c-1.19-.3-1.7-1.74-1.92-2.94c-.67-3.63.47-7.59 3.14-10.25c2.43-2.43 6.86-1.09 8.55 1.37"/><path fill="#e2a610" d="M123.63 100.16c.75-2.26.26-4.77-.46-6.98c-.55-.93-2.06-1.17-3.02-1.55c-.27-.09-2.53-.52-2.59-.7c.3.98.8 1.93.91 2.96c.07 1.43-4.13 6.99-12.78 10.99c-21.95 10.14-35.57 5.95-39.03 5.37c-10.91-1.99-21.99-9.38-29.43-14c-7.86-3.59-19.61-6.32-27.06-11.83c-.93-.66-1.96-1.3-2.39-2.42c0 0-.1-.25-.14-.51c-.07-.51-.2-1.75-.2-1.75c-1.56.96-3.39 2.4-3.37 4.43L4 91.15c-.01.7.28 1.37.8 1.84c1.12 1.03 3.1 2.28 4.55 3.15c.86.51 1.81.32 2.02-.72q.12-.585.3-1.17c.41-1.49 1.4-.52 2.45-.09c.36.17.81.32.8.78v3.22c0 .88.52 1.66 1.34 1.99c1.29.35 3.63 2.1 4.94.79c.43-.96.27-2.27.98-3.08c.53-.32 1.9.39 2.41.58c.79.3.18 3.16.15 3.53c-.07.89.39 1.62 1.25 1.95l2.74 1.04c.58.22 1.75.22 2.18-.33c.53-.87.36-2.04.86-2.92c.61-.84 2.11.31 2.76.64c1.18.57 2.54 1.4 3.64 2.13c3.24 2.08 5.03 2.96 9.94 5.57c.2.11.33.31.34.53l.17 3.19c.2 2.13 3 2.53 4.52 3.54c.58.29 1.69.41 2.11-.21c.37-.69.4-1.53.79-2.23c.39-.83 1.34-.6 2.03-.34c2.83.72 1.8 2.37 1.85 4.55c.05.81.83 1.54 1.59 1.77c1.41.42 3.16.84 4.08 1.05c2.24.51 2.1-1.88 2.24-3.2c.05-.58.14-1.6.99-1.5c.77.09 1.54.17 2.32.24c.32.03.57.28.59.6l.15 2.85c.06 1.12.98 2.02 2.09 2.05c1.55.04 3.83.06 5.6.04c.91.02 1.75-.74 1.67-1.69c.06-1.56-.93-3.25 1.13-3.79c.76-.24 2.91-.51 3.77-.55c.75-.03.85.54.96 1.66c.08.76.07 1.65.63 2.16c.4.37 1.01.39 1.55.31c12.84-1.84 28.22-9.4 34.35-20.92"/><path fill="none" stroke="#c62828" stroke-linecap="round" stroke-miterlimit="10" stroke-width="3.067" d="M44.21 50.49c2.66 3.84 19.02.28 24.34 2.77m-19.73 7.45c1.9 2.88 18.85-2.59 24.69-.98m-18.44 9.65c2.35 2.72 15.96-4.61 23.69-3.14"/><path fill="none" stroke="#c62828" stroke-miterlimit="10" stroke-width="3.067" d="M62.31 76.5c1.73 3.3 15.38-5.46 22.96-2.24c.87.37 1.75.9.89 1.29c-6.12 2.77-14.23 9.37-17.18 7.27"/><path fill="none" stroke="#f44336" stroke-linecap="round" stroke-miterlimit="10" stroke-width="3.067" d="M67.32 82.23c.48-6.63 6.51-12.4 11.43-15.99m-18.46 9.3c0-5.74 10.26-14.36 13.22-15.81m-20.49 8.46c2.35-6.47 9.95-11.85 15.52-14.94m-20.83 5.28c3.72-5.59 11.69-9.35 17.43-11.19m-20.67.56c4.89-5.52 13.88-6.96 21.2-6.09"/><path fill="#a06841" d="M39.54 34.97c1.04 4.38 1.04 9.15.86 13.58c4.84-4.16 5.05-11.14 4.86-17.08c-.11-1.21.11-2.51 1.7-3.53c5.16-3.33 10.69-4.64 16.51-4.26c2.33.16 4 1.23 4 1.23c-1.15-2.25-3.5-3.91-7.69-4.26c-5.59-.46-12.14.05-16.93 3.23c-4.01 2.68-5.26 6.78-3.31 11.09"/><path fill="#78a3ac" d="m44.21 50.49l3.21.88c.53.14 1.01-.34.87-.86l-1.19-4.46c-.14-.53-.8-.7-1.19-.31l-2.36 2.4a2.49 2.49 0 0 0-3.07-1.74l-.99.27c-1.14.31-1.81 1.5-1.5 2.64l.84 3.03c.31 1.14 1.5 1.81 2.64 1.5l.99-.27a2.505 2.505 0 0 0 1.75-3.08M63.7 86.61l2.54 1.85c.96.7 2.3.49 3-.47l.61-.83c.81-1.11.57-2.68-.55-3.49l3.06-1.31c.5-.21.58-.89.14-1.22l-3.71-2.75a.701.701 0 0 0-1.12.49l-.35 3.35a2.497 2.497 0 0 0-3.49.55l-.61.83c-.69.96-.48 2.3.48 3m-7.72-7.36l2.2 2.25c.83.85 2.19.87 3.04.04l.74-.72c.99-.96 1.01-2.54.04-3.53l3.24-.78c.53-.13.72-.78.34-1.18L62.39 72a.704.704 0 0 0-1.19.3l-.9 3.24a2.487 2.487 0 0 0-3.53-.04l-.74.72c-.86.82-.87 2.18-.05 3.03m-7.18-9.04l1.75 2.62c.66.99 1.99 1.25 2.98.59l.86-.57c1.15-.77 1.45-2.32.69-3.46l3.32-.18c.54-.03.85-.64.55-1.09l-2.53-3.86a.707.707 0 0 0-1.23.08l-1.48 3.02a2.5 2.5 0 0 0-3.46-.69l-.86.57a2.14 2.14 0 0 0-.59 2.97m-6.3-9.4l1.44 2.8a2.153 2.153 0 0 0 2.89.93l.92-.47a2.49 2.49 0 0 0 1.08-3.36l3.32.2c.54.03.92-.54.67-1.02l-2.07-4.12a.706.706 0 0 0-1.23-.06l-1.82 2.83a2.49 2.49 0 0 0-3.36-1.08l-.92.47c-1.05.53-1.46 1.83-.92 2.88"/><path fill="#4e342e" d="M28.51 41.23c-7.76 0-17.44-2.68-18.91-6.11c-.65-1.53.55-2.24.55-2.24c4.18 3.44 11.04 5.37 18.87 5.28c.84-.01 1.54.63 1.57 1.46c.04.87-.65 1.59-1.52 1.6c-.19 0-.38.01-.56.01m-.37 12.23c-6.8 0-12.98-1.45-17.57-4.05c0 0 .19-2.03-.46-2.74c-.95-1.05-1.61-2.61-1.61-2.61c4 4.05 11.35 6.38 19.83 6.33c.84 0 1.53.64 1.56 1.47c.03.86-.65 1.59-1.5 1.59c-.09.01-.17.01-.25.01"/><path fill="#a06841" d="M23.87 18.73c-1.68.13-3.58.55-5.16 1.14c-1.4.52-3.06 1.33-3.52 2.74c-.55 1.7.45 3.02 1.63 3.92c1.66 1.42 5.86 1.54 5.14 4.24c-1.02 3.81-9.09 1.56-11.03-.31c-6.01-5.36-1.5-11.74 4.85-13.85c3.03-1.01 6.41-1.78 9.17-1.73c.67.01 2.87.21 2.67 1.91c-.17 1.48-1.57 1.77-3.75 1.94M12.43 39.42c1.52.85 4.87 1.28 5.07 3.01c.31 2.61-4.26 2.42-6.02 1.53c-1.34-.47-2.87-1.72-3.26-3.53c-.47-2.14.94-2.84 4.21-1.01"/></svg></div>
                        <span>SEPATU</span>
                    </div>
                    <div class="safety-icon-item">
                        <div class="safety-icon-circle"><svg xmlns="http://www.w3.org/2000/svg" width="128" height="128" viewBox="0 0 128 128"><radialGradient id="SVGJCmBzb8w" cx="63.6" cy="2931.01" r="56.96" gradientTransform="translate(0 -2868.11)" gradientUnits="userSpaceOnUse"><stop offset=".5" stop-color="#fde030"/><stop offset=".919" stop-color="#f7c02b"/><stop offset="1" stop-color="#f4a223"/></radialGradient><path fill="url(#SVGJCmBzb8w)" d="M63.6 118.8c-27.9 0-58-17.5-58-55.9S35.7 7 63.6 7c15.5 0 29.8 5.1 40.4 14.4c11.5 10.2 17.6 24.6 17.6 41.5s-6.1 31.2-17.6 41.4c-10.6 9.3-25 14.5-40.4 14.5"/><path fill="#eb8f00" d="M111.49 29.67c5.33 8.6 8.11 18.84 8.11 30.23c0 16.9-6.1 31.2-17.6 41.4c-10.6 9.3-25 14.5-40.4 14.5c-18.06 0-37.04-7.35-48.18-22.94c10.76 17.66 30.99 25.94 50.18 25.94c15.4 0 29.8-5.2 40.4-14.5c11.5-10.2 17.6-24.5 17.6-41.4c0-12.74-3.47-24.06-10.11-33.23"/><path fill="#eee" d="M28.19 91.17c-7.35 3.63-13.9 3.06-13.9 3.06c2.05 3.76 4.08 5.57 6.09 7.56c0 0 3.71.33 8.23-1.64s6.93-12.61-.42-8.98m.31-21.95c-13.75-.93-22.82-9.4-22.82-9.4c-.46 4.97.23 9.7.23 9.7s6.51 8.11 22.33 9c12.05.68 11.74-8.52.26-9.3m70.43 21.95c7.35 3.63 13.9 3.06 13.9 3.06c-2.05 3.76-4.08 5.57-6.09 7.56c0 0-3.71.33-8.23-1.64c-4.52-1.98-6.93-12.61.42-8.98m-.31-21.95c13.75-.93 22.82-9.4 22.82-9.4c.46 4.97-.23 9.7-.23 9.7s-6.51 8.11-22.33 9c-12.05.68-11.75-8.52-.26-9.3"/><path fill="#f5f5f5" d="M100.98 96.87V79.19l-.01-8.55c-.07-1.74-1.16-2.32-3.39-3.13c-5-1.82-20.14-6.33-33.58-6.34h-.02c-13.44 0-28.59 4.52-33.58 6.34c-2.23.81-3.31 1.39-3.39 3.13L27 79.19v17.68c0 .55-.16 3.3 3.16 4.9c3.69 1.78 18.76 7.35 33.83 7.35s30.15-5.57 33.83-7.35c3.32-1.6 3.16-4.35 3.16-4.9"/><path fill="#808080" d="M30.4 67.5c-.76.13-1.44.49-1.88.98c-.42.51-.65 1.11-.69 1.77c-.06.63.01 1.4.05 2.14l.13 2.26c.2 3.02.31 6.06.35 9.1c.03 3.04-.01 6.08-.2 9.13c-.05.77-.08 1.51-.16 2.31c-.09.76-.16 1.47-.16 2.18c-.02 1.41.2 2.88 1.43 3.89c-.76-.19-1.46-.74-1.93-1.43s-.76-1.49-.96-2.29c-.19-.8-.27-1.61-.35-2.37c-.09-.73-.14-1.5-.22-2.26c-.29-3.04-.43-6.08-.46-9.13s.04-6.1.32-9.16c.15-1.55.25-3.02.73-4.7c.29-.82.84-1.63 1.6-2.08a3.18 3.18 0 0 1 2.4-.34m67 0c.75-.2 1.62-.11 2.39.33c.76.45 1.31 1.26 1.6 2.08c.48 1.68.58 3.15.73 4.7c.28 3.06.35 6.11.32 9.16s-.17 6.09-.46 9.13c-.07.75-.13 1.53-.22 2.26c-.08.76-.16 1.58-.35 2.37c-.2.79-.49 1.59-.96 2.29c-.47.69-1.17 1.24-1.93 1.43c1.23-1.01 1.45-2.48 1.43-3.89c0-.72-.07-1.42-.16-2.18c-.08-.79-.11-1.54-.16-2.31c-.19-3.05-.23-6.09-.2-9.13c.04-3.04.15-6.07.35-9.1l.13-2.26c.04-.74.11-1.51.05-2.14c-.04-.66-.26-1.26-.69-1.77c-.43-.48-1.1-.84-1.87-.97"/><path fill="#422b0d" d="M44.67 41.94c-4.19 0-8 3.54-8 9.42s3.81 9.42 8 9.42s8-3.54 8-9.42s-3.81-9.42-8-9.42"/><path fill="#896024" d="M44.28 45.87c-1.03-.72-2.58-.49-3.58.95c-1 1.45-.67 2.97.36 3.69s2.58.49 3.58-.95c1-1.45.67-2.97-.36-3.69"/><path fill="#422b0d" d="M83.02 41.94c-4.19 0-8 3.54-8 9.42s3.81 9.42 8 9.42s8-3.54 8-9.42s-3.81-9.42-8-9.42"/><path fill="#896024" d="M82.63 45.87c-1.03-.72-2.58-.49-3.58.95c-1 1.45-.67 2.97.36 3.69s2.58.49 3.58-.95c1.01-1.45.68-2.97-.36-3.69"/></svg></div>
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
                    <div class="value" id="record-days-without-accident">{{ number_format($record_days_without_accident) }}</div>
                    <div class="label">HARI</div>
                </div>
            </div>

            <!-- MARQUEE -->
            <div class="marquee">
                <div class="marquee__content">
                    @foreach ($marquee_texts as $text)
                        <span>{{ trim($text) }}</span>
                    @endforeach
                </div>

                <div aria-hidden="true" class="marquee__content">
                    @foreach ($marquee_texts as $text)
                        <span>{{ trim($text) }}</span>
                    @endforeach
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

    // Fungsi format angka ribuan
    function formatNumber(num) {
        return Number(num).toLocaleString('en-US'); // otomatis pakai koma ribuan
    }

    // Fungsi update teks dengan animasi dan format angka
    function updateTextWithAnimation(element, newText, isNumber=false) {
        let formattedText = newText;
        if (isNumber && !isNaN(newText)) {
            formattedText = formatNumber(newText);
        }

        if (element.text() != formattedText) {
            element.fadeOut(300, function() {
                $(this).text(formattedText).fadeIn(300);
            });
        }
    }

    function updateDashboard() {
        $.ajax({
            url: "{{ url('dashboard/safety-board/api/safety-data') }}",
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                updateTextWithAnimation($totalDays, data.total_days_without_accident_until_this_month, true);
                updateTextWithAnimation($totalWorkingDays, data.total_working_days_until_this_month, true);
                updateTextWithAnimation($targetWorkingDays, data.target_working_days_this_year, true);
                updateTextWithAnimation($accidentsThisMonth, data.accidents_this_month, true);
                updateTextWithAnimation($lastAccidentDate, data.last_accident_date); // tanggal, ga perlu format angka
                updateTextWithAnimation($recordDays, data.record_days_without_accident, true);

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