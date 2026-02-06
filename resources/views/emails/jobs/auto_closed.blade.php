<!DOCTYPE html>
<html>
<head>
    <title>Job Auto-Closed Notification</title>
    {{-- Salin <style> dari template email Anda yang lain untuk konsistensi --}}
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { background-color: #a48d53; color: white; padding: 10px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { padding: 20px; }
        .content p { line-height: 1.6; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #777; }
        .job-details { border-left: 4px solid #c7b07b; padding-left: 15px; margin: 20px 0; }
        .job-details strong { display: inline-block; width: 120px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Job Auto-Closed</h2>
        </div>
        <div class="content">
            <p>Hello {{ $user->name }},</p>
            <p>This is an automated notification to inform you that a job you were involved with has been automatically closed by the system because it has been in the 'Completed' status for more than 2 working days.</p>
            
            <div class="job-details">
                <p><strong>Job ID:</strong> {{ $job->id_job }}</p>
                <p><strong>Requester:</strong> {{ $job->pengaju->name }}</p>
                <p><strong>Description:</strong> {{ Str::limit($job->list_job, 150) }}</p>
                <p><strong>Completed On:</strong> {{ \Carbon\Carbon::parse($job->tanggal_job_selesai)->format('d M Y') }}</p>
                <p><strong>Closed On:</strong> {{ \Carbon\Carbon::parse($job->closed_at)->format('d M Y') }}</p>
            </div>

            <p>This job is now archived. No further action is needed.</p>
        </div>
        <div class="footer">
            <p>Marsho Job Kanban System &copy; {{ date('Y') }}</p>
        </div>
    </div>
</body>
</html>