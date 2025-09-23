<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kecelakaan Kerja Disetujui Penuh</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333333;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #4CAF50;
            color: #ffffff;
            padding: 15px;
            text-align: center;
            border-radius: 8px 8px 0 0;
            margin: -30px -30px 30px -30px;
        }
        .button {
            display: inline-block;
            background-color: #4CAF50;
            color: #ffffff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .footer {
            margin-top: 30px;
            font-size: 0.9em;
            color: #777777;
            text-align: center;
            border-top: 1px solid #eeeeee;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Laporan Kecelakaan Kerja Disetujui Penuh</h2>
        </div>
        <p>Halo <strong>{{ $laporan->pembuatLaporan->name ?? 'Pembuat Laporan' }}</strong>,</p>
        <p>Kami ingin memberitahukan bahwa laporan kecelakaan kerja Anda dengan nomor form <strong>{{ $laporan->nomor_form }}</strong> telah <strong>disetujui sepenuhnya</strong> oleh semua pihak yang berwenang.</p>
        <p>Anda dapat melihat detail laporan yang telah disetujui melalui tautan di bawah ini:</p>
        <p style="text-align: center;">
            <a href="{{ $laporanUrl }}" class="button">Lihat Laporan</a>
        </p>
        <p>Terima kasih atas perhatian Anda.</p>
        <div class="footer">
            <p>Ini adalah email otomatis, mohon tidak membalas email ini.</p>
            <p>&copy; {{ date('Y') }} PT Sinar Meadow International Indonesia</p>
        </div>
    </div>
</body>
</html>