<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container { max-width: 600px; margin: 0 auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; }
        .header { background-color: #ea580c; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .info-box { background-color: #fff7ed; border-left: 4px solid #ea580c; padding: 15px; margin: 15px 0; }
        .label { font-weight: bold; font-size: 12px; color: #666; text-transform: uppercase; }
        .value { margin-bottom: 10px; font-size: 16px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>⛔ JOB CANCELLED</h2>
        </div>
        <div class="content">
            <p>Hello Team,</p>
            <p>The following job currently assigned to your department has been <strong>CANCELLED</strong> by the requester. Please stop any ongoing work related to this ticket.</p>

            <div class="info-box">
                <div class="label">Job ID</div>
                <div class="value">{{ $job->id_job }}</div>

                <div class="label">Description</div>
                <div class="value">{{ $job->list_job }}</div>

                <div class="label">Cancelled By</div>
                <div class="value">{{ $cancelledBy }}</div>

                <div class="label">Reason</div>
                <div class="value" style="color: #c2410c; font-weight: bold;">{{ $reason }}</div>
            </div>

            <p style="font-size: 12px; color: #888;">This is an automated message from Marsho Job System.</p>
        </div>
    </div>
</body>
</html>