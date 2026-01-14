<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .header { background-color: #ef4444; color: white; padding: 15px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { padding: 20px; }
        .details-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .details-table th, .details-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .details-table th { background-color: #f3f4f6; }
        .btn { display: inline-block; background-color: #2563eb; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 20px; }
        .footer { margin-top: 20px; font-size: 12px; color: #666; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>⚠️ Job Overdue Alert</h2>
        </div>
        
        <div class="content">
            <p>Hello,</p>
            <p>This is an automated notification to inform you that the following job has exceeded the <strong>3-day SLA limit</strong> in its current stage.</p>

            <table class="details-table">
                <tr>
                    <th>Job ID</th>
                    <td><strong>{{ $job->id_job }}</strong></td>
                </tr>
                <tr>
                    <th>Description</th>
                    <td>{{ Str::limit($job->list_job, 50) }}</td>
                </tr>
                <tr>
                    <th>Current Status</th>
                    <td style="text-transform: uppercase;">{{ str_replace('_', ' ', $job->status) }}</td>
                </tr>
                <tr>
                    <th>Current Department</th>
                    <td>{{ $job->latestRoute->toDepartment->department_name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Last Updated</th>
                    <td>{{ \Carbon\Carbon::parse($job->last_stage_update)->format('d M Y, H:i') }}</td>
                </tr>
                <tr>
                    <th>Overdue By</th>
                    <td style="color: #ef4444; font-weight: bold;">
                        {{ \Carbon\Carbon::now()->diffInDays($job->last_stage_update) }} Days
                    </td>
                </tr>
            </table>

            <p>Please take immediate action to move this job to the next stage or update its status.</p>

            <center>
                <a href="{{ route('jobs.index') }}" class="btn">View Job Board</a>
            </center>
        </div>

        <div class="footer">
            <p>Marsho Job Kanban System &copy; {{ date('Y') }}</p>
        </div>
    </div>
</body>
</html>