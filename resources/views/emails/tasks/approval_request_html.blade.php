<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $email_subject ?? 'Permintaan Persetujuan Tugas' }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
        .email-container { max-width: 600px; margin: 20px auto; border: 1px solid #ddd; background-color: #ffffff; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { background-color: #A78734; color: white; padding: 15px 20px; text-align: center;}
        .header h1 { margin: 0; font-size: 24px; font-weight: bold; }
        .sub-header { background-color: #B99B4A; /* Slightly lighter shade for sub-header */ color: white; padding: 10px 20px; text-align: center;}
        .sub-header h2 { margin: 0; font-size: 18px; font-weight: normal; }
        .content { padding: 25px; }
        .content p { line-height: 1.6; margin: 10px 0; }
        .info { background-color: #f9f9f9; border-left: 5px solid #A78734; padding: 15px; margin: 20px 0; }
        .info p { text-align: left; white-space: pre-line; margin: 5px 0; font-size: 14px; }
        .info strong { font-weight: bold; color: #555; }
        .button-container { display: flex; justify-content: center; gap: 20px; margin: 25px auto; max-width: 400px; }
        .button { flex-basis: 150px; padding: 12px 20px; text-decoration: none; color: #fff !important; border-radius: 5px; font-weight: bold; text-align: center; display: inline-block; transition: background-color 0.3s ease; }
        .approve { background-color: #28a745; }
        .approve:hover { background-color: #218838; }
        .reject { background-color: #dc3545; }
        .reject:hover { background-color: #c82333; }
        .footer { font-size: 12px; color: #777; text-align: center; padding: 20px; border-top: 1px solid #eee; margin-top: 20px; }
        .footer p { margin: 5px 0; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header"><h1>{{ $company_name ?? config('app.company_name', 'Perusahaan Anda') }}</h1></div>
        <div class="sub-header"><h2>{{ $app_subname ?? config('app.subname', 'Aplikasi JobBoard') }}</h2></div>
        <div class="content">
            <p>Dear <strong>{{ $approver_name ?? 'Approver' }}</strong> (Tim Departemen {{ $department_name ?? 'N/A' }}),</p>
            <p>Sebuah tugas baru telah diajukan dan membutuhkan persetujuan Anda:</p>
            <div class="info">
                <p><strong>Job ID:</strong> {{ $task_id_job ?? 'N/A' }}<br>
                <strong>Requester:</strong> {{ $requester_name ?? 'N/A' }}<br>
                <strong>Departemen Tujuan:</strong> {{ $department_name ?? 'N/A' }}<br>
                <strong>Location/Area:</strong> {{ $task_location ?? 'N/A' }}<br>
                <strong>Description:</strong><br>{{ $task_description ?? 'Tidak ada deskripsi.' }}</p>
            </div>
            <p>Mohon untuk meninjau dan memberikan persetujuan atau penolakan melalui tautan di bawah ini:</p>
            <div class="button-container">
                <a href="{{ $approveUrl ?? '#' }}" class="button approve">Setujui Tugas</a>
                <a href="{{ $rejectUrl ?? '#' }}" class="button reject">Tolak Tugas</a>
            </div>
            <p>Jika Anda memilih untuk menolak, Anda akan diminta untuk memberikan alasan penolakan.</p>
            <p>Jika Anda tidak dapat mengakses tombol di atas, silakan salin dan tempel URL berikut ke browser Anda:<br>
                Setujui: {{ $approveUrl ?? '#' }}<br>
                Tolak: {{ $rejectUrl ?? '#' }}
            </p>
            <p>Terima kasih atas perhatian dan kerjasamanya.</p>
            <p>Regards,<br>Sistem Notifikasi {{ config('app.name') }}</p>
        </div>
        <div class="footer">
            <p>&copy; {{ $footer_year ?? date('Y') }} {{ $company_name ?? config('app.company_name', 'Perusahaan Anda') }}. All rights reserved.</p>
            <p>Ini adalah email yang dibuat secara otomatis. Mohon untuk tidak membalas email ini.</p>
        </div>
    </div>
</body>
</html>