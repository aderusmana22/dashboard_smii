<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kecelakaan Kerja Ditolak</title>
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
            background-color: #dc3545; /* Merah untuk ditolak */
            color: #ffffff;
            padding: 15px;
            text-align: center;
            border-radius: 8px 8px 0 0;
            margin: -30px -30px 30px -30px;
        }
        .button {
            display: inline-block;
            background-color: #007bff; /* Biru untuk revisi */
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
            <h2>Laporan Kecelakaan Kerja Ditolak</h2>
        </div>
        <p>Halo <strong>{{ $laporan->pembuatLaporan->name ?? 'Pembuat Laporan' }}</strong>,</p>
        <p>Kami ingin memberitahukan bahwa laporan kecelakaan kerja Anda dengan nomor form <strong>{{ $laporan->nomor_form }}</strong> telah <strong>ditolak</strong>.</p>
        @php
            // Eager load relasi user untuk menghindari query tambahan dan memastikan data ada
            $laporan->load('approvalHistories.user'); 
            
            $lastRejection = $laporan->approvalHistories->where('action', 'rejected')->last();
            $rejectorName = ($lastRejection && $lastRejection->user) ? $lastRejection->user->name : 'Seorang approver';
        @endphp
        <p>Laporan ini ditolak oleh <strong>{{ $rejectorName }}</strong>.</p>

        <p><strong>Alasan Penolakan:</strong></p>
        <p style="background-color: #f8d7da; border-left: 5px solid #dc3545; padding: 10px; margin-left: 20px;">
            {{ $rejectionReason }}
        </p>
        <p>Mohon periksa kembali laporan Anda dan lakukan revisi yang diperlukan.</p>
        <p style="text-align: center;">
            <a href="{{ $reviseUrl }}" class="button">Revisi Laporan</a>
        </p>
        <p>Jika Anda memiliki pertanyaan, silakan hubungi tim HSE.</p>
        <div class="footer">
            <p>Ini adalah email otomatis, mohon tidak membalas email ini.</p>
            <p>&copy; {{ date('Y') }} PT Sinar Meadow International Indonesia</p>
        </div>
    </div>
</body>
</html>