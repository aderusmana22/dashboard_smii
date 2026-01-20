<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Cancelled Notification</title>
    <style>
        /* Reset & General */
        body { margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; line-height: 1.5; color: #333333; background-color: #f9f9f9; }
        
        /* Container */
        .container { max-width: 800px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.1); border: 1px solid #ddd; }
        
        /* Header Section (Sama persis dengan template approved) */
        .header-main { background-color: #a48d53; color: #ffffff; padding: 25px 20px; text-align: center; font-size: 24px; font-weight: bold; }
        .header-sub { background-color: #c7b07b; color: #ffffff; padding: 15px; text-align: center; font-size: 18px; }
        .brand-tag { background-color: #e8d697; color: #4a3b18; padding: 2px 6px; border-radius: 2px; font-weight: bold; margin-right: 5px; }
        
        /* Content Section */
        .content { padding: 30px; }
        
        /* Status Title */
        .status-title { color: #ef4444; font-size: 22px; font-weight: bold; margin-bottom: 20px; display: block; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        
        /* Job Details (Left Border Style - Signature Look) */
        .job-details-box { border-left: 5px solid #a48d53; padding-left: 15px; margin: 20px 0; }
        .detail-row { margin-bottom: 12px; }
        .detail-label { font-weight: bold; color: #333; width: 120px; display: inline-block; vertical-align: top;}
        .detail-value { display: inline-block; max-width: 80%; vertical-align: top; }

        /* Reason Highlight */
        .reason-text { color: #c2410c; font-weight: bold; }
        
        /* Footer */
        .footer { background-color: #f9f9f9; padding: 30px 20px; text-align: center; font-size: 12px; color: #888; border-top: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header Utama (Branding PT) -->
        <div class="header-main">
            PT. Sinar Meadow International Indonesia
        </div>

        <!-- Sub Header (Marsho JobBoard) -->
        <div class="header-sub">
            <span class="brand-tag">Marsho</span> JobBoard
        </div>
        
        <div class="content">

            <p>Hello Team,</p>
            <p>The following job currently assigned to your department has been <strong>CANCELLED</strong> by the requester. Please stop any ongoing work related to this ticket.</p>

            <!-- Detail Job dengan Style Border Kiri -->
            <div class="job-details-box">
                <div class="detail-row">
                    <span class="detail-label">Job ID:</span> 
                    <span class="detail-value">{{ $job->id_job }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Description:</span>
                    <span class="detail-value">{{ $job->list_job }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Cancelled By:</span>
                    <span class="detail-value">{{ $cancelledBy }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Reason:</span>
                    <span class="detail-value reason-text">{{ $reason }}</span>
                </div>
            </div>

            <p style="font-size: 13px; color: #666; margin-top: 30px;">
                This is an automated message from Marsho Job System.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>&copy; {{ date('Y') }} PT. Sinar Meadow International Indonesia. All rights reserved.</p>
        </div>
    </div>
</body>
</html>