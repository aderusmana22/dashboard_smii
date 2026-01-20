<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Overdue Notification</title>
    <style>
        /* Reset & General */
        body { margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; line-height: 1.5; color: #333333; background-color: #f9f9f9; }
        
        /* Container */
        .container { max-width: 800px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.1); border: 1px solid #ddd; }
        
        /* Header Section (Branding Standard) */
        .header-main { background-color: #a48d53; color: #ffffff; padding: 25px 20px; text-align: center; font-size: 24px; font-weight: bold; }
        .header-sub { background-color: #c7b07b; color: #ffffff; padding: 15px; text-align: center; font-size: 18px; }
        .brand-tag { background-color: #e8d697; color: #4a3b18; padding: 2px 6px; border-radius: 2px; font-weight: bold; margin-right: 5px; }
        
        /* Content Section */
        .content { padding: 30px; }
        
        /* Alert Title */
        .alert-title { color: #ef4444; font-size: 22px; font-weight: bold; margin-bottom: 20px; display: block; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        
        /* Job Details (Left Border Style - Signature Look) */
        .job-details-box { border-left: 5px solid #a48d53; padding-left: 15px; margin: 25px 0; }
        .detail-row { margin-bottom: 12px; }
        .detail-label { font-weight: bold; color: #555; width: 140px; display: inline-block; vertical-align: top;}
        .detail-value { display: inline-block; max-width: 75%; vertical-align: top; }

        /* Highlight Colors */
        .text-overdue { color: #ef4444; font-weight: bold; font-size: 1.1em; }
        .text-status { text-transform: uppercase; font-weight: bold; color: #333; }
        
        /* Button */
        .btn { display: inline-block; background-color: #a48d53; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; margin-top: 10px; font-weight: bold; }
        .btn:hover { background-color: #8c7846; }

        /* Footer */
        .footer { background-color: #f9f9f9; padding: 30px 20px; text-align: center; font-size: 12px; color: #888; border-top: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header Utama -->
        <div class="header-main">
            PT. Sinar Meadow International Indonesia
        </div>

        <!-- Sub Header -->
        <div class="header-sub">
            <span class="brand-tag">Marsho</span> JobBoard
        </div>
        
        <div class="content">
            <p>Hello,</p>
            <p>This is an automated notification to inform you that the following job has exceeded the <strong>3-day SLA limit</strong> in its current stage.</p>

            <!-- Detail Job (Menggantikan Table agar seragam dengan desain lain) -->
            <div class="job-details-box">
                <div class="detail-row">
                    <span class="detail-label">Job ID:</span> 
                    <span class="detail-value"><strong>{{ $job->id_job }}</strong></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Description:</span>
                    <span class="detail-value">{{ Str::limit($job->list_job, 80) }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Current Status:</span>
                    <span class="detail-value text-status">{{ str_replace('_', ' ', $job->status) }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Current Dept:</span>
                    <span class="detail-value">{{ $job->latestRoute->toDepartment->department_name ?? 'N/A' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Last Updated:</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($job->last_stage_update)->format('d M Y, H:i') }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Overdue By:</span>
                    <span class="detail-value text-overdue">
                        {{ \Carbon\Carbon::now()->diffInDays($job->last_stage_update) }} Days
                    </span>
                </div>
            </div>

            <p>Please take immediate action to move this job to the next stage or update its status.</p>

            <center>
                <a href="{{ route('jobs.index') }}" class="btn">View Job Board</a>
            </center>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Marsho Job Kanban System &copy; {{ date('Y') }} PT. Sinar Meadow International Indonesia.</p>
        </div>
    </div>
</body>
</html>