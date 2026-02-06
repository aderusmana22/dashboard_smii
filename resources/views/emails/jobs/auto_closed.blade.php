<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Job Auto-Closed Notification</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 700px; margin: auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border: 1px solid #e1e1e1; }
        .header { background-color: #a48d53; color: white; padding: 25px; text-align: center; }
        .header h2 { margin: 0; font-size: 22px; text-transform: uppercase; letter-spacing: 1.5px; }
        
        .content { padding: 30px; }
        .intro { margin-bottom: 25px; font-size: 15px; line-height: 1.6; }
        
        /* Job Summary Box */
        .job-summary { background-color: #fcfaf5; border: 1px solid #eee; border-radius: 6px; padding: 20px; margin-bottom: 30px; }
        .summary-title { font-weight: bold; color: #a48d53; border-bottom: 1px solid #ebdcb2; padding-bottom: 8px; margin-bottom: 15px; display: block; text-transform: uppercase; font-size: 13px; }
        .job-details p { margin: 10px 0; font-size: 14px; line-height: 1.5; }
        .job-details strong { display: inline-block; width: 150px; color: #555; }

        /* Timeline/History Style */
        .history-section { margin-top: 30px; }
        .history-title { font-weight: bold; font-size: 15px; margin-bottom: 20px; color: #333; display: block; border-left: 4px solid #a48d53; padding-left: 10px; text-transform: uppercase; }
        
        .timeline-item { border-left: 2px solid #e9e9e9; margin-left: 10px; padding-left: 25px; padding-bottom: 25px; position: relative; }
        .timeline-item::before { content: ''; position: absolute; left: -7px; top: 0; width: 12px; height: 12px; background: #c7b07b; border-radius: 50%; border: 2px solid #fff; }
        .timeline-date { font-size: 11px; color: #999; display: block; margin-bottom: 4px; }
        .timeline-user { font-weight: bold; font-size: 13px; color: #444; }
        .timeline-note { font-size: 13px; color: #666; margin-top: 6px; background: #f9f9f9; padding: 10px; border-radius: 4px; white-space: pre-line; border: 1px solid #f0f0f0; }

        .footer { text-align: center; padding: 25px; font-size: 12px; color: #888; background: #f9f9f9; border-top: 1px solid #eee; }
        .btn-view { display: inline-block; padding: 12px 24px; background-color: #a48d53; color: #ffffff !important; text-decoration: none; border-radius: 4px; font-weight: bold; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Job Closed & Archived</h2>
        </div>
        
        <div class="content">
            <p class="intro">Hello <strong>{{ $user->name }}</strong>,<br>
            The following job has been <strong>Closed and Archived</strong>. All activity records are now stored in the system history.</p>
            
            <div class="job-summary">
                <span class="summary-title">Primary Information</span>
                <div class="job-details">
                    <p><strong>Job ID:</strong> <span style="font-size: 16px; font-weight: bold; color: #a48d53;">{{ $job->id_job }}</span></p>
                    <p><strong>Requester (From):</strong> {{ $job->pengaju->name }}</p>
                    <p><strong>Latest Department:</strong> {{ $job->latestRoute->toDepartment->department_name ?? 'N/A' }}</p>
                    <p><strong>Job Description:</strong> {{ $job->list_job }}</p>
                    <p><strong>Completion Date:</strong> {{ \Carbon\Carbon::parse($job->tanggal_job_selesai)->format('d M Y') }}</p>
                    <p><strong>Archived Date:</strong> {{ \Carbon\Carbon::parse($job->closed_at)->format('d M Y, H:i') }}</p>
                </div>
            </div>

            <div class="history-section">
                <span class="history-title">Activity History</span>
                
                @php
                    /** 
                     * Merge Routes and Notes to display a unified chronological timeline.
                     * We exclude attachments as requested.
                     */
                    $activities = collect();
                    
                    foreach($job->routes as $route) {
                        $activities->push([
                            'date' => $route->created_at,
                            'user' => $route->creator->name ?? 'System',
                            'note' => $route->note
                        ]);
                    }
                    
                    foreach($job->notes as $note) {
                        $activities->push([
                            'date' => $note->created_at,
                            'user' => $note->creator->name ?? 'System',
                            'note' => $note->note
                        ]);
                    }
                    
                    // Sort from newest to oldest
                    $sortedActivities = $activities->sortByDesc('date');
                @endphp

                @forelse($sortedActivities as $activity)
                    <div class="timeline-item">
                        <span class="timeline-date">{{ \Carbon\Carbon::parse($activity['date'])->format('d M Y, H:i') }}</span>
                        <div class="timeline-user">{{ $activity['user'] }}</div>
                        <div class="timeline-note">{{ $activity['note'] }}</div>
                    </div>
                @empty
                    <p style="font-size: 13px; color: #999; text-align: center;">No activity logs found.</p>
                @endforelse
            </div>

            <center>
                <a href="{{ route('jobs.index') }}" class="btn-view">Open Job Board</a>
            </center>
        </div>

        <div class="footer">
            <p>This is an automated message sent by Marsho Job Kanban System.<br>
            &copy; {{ date('Y') }} PT. Sinar Meadow International Indonesia</p>
        </div>
    </div>
</body>
</html>