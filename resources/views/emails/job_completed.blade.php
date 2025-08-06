<!DOCTYPE html>
<html>
<head>
    <title>Pekerjaan Telah Selesai</title>
</head>
<body>
    <h1>Pekerjaan Telah Selesai</h1>
    <p>Halo {{ $job->pengaju->name }},</p>
    <p>Pekerjaan yang Anda ajukan dengan ID <strong>{{ $job->id_job }}</strong> telah selesai.</p>
    <p><strong>Detail Pekerjaan:</strong></p>
    <p>{{ $job->list_job }}</p>
    <p>Terima kasih.</p>
</body>
</html>