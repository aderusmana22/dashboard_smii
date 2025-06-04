<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $email_subject ?? 'Task Approval Request' }}</title>
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
        
        /* Button Container - uses text-align to center the inner table */
        .button-container-wrapper { text-align: center; margin: 25px auto; }
        
        /* Button styling */
        .button { 
            padding: 12px 20px; 
            text-decoration: none !important; /* Important to override Gmail's default link styling */
            color: #fff !important; /* Important for color */
            border-radius: 5px; 
            font-weight: bold; 
            text-align: center; 
            display: inline-block; /* Allows padding and width */
            min-width: 120px; /* Give buttons a minimum width */
            transition: background-color 0.3s ease; 
        }
        .approve { background-color: #28a745; }
        .approve:hover { background-color: #218838; }
        .reject { background-color: #dc3545; }
        .reject:hover { background-color: #c82333; }
        
        .footer { font-size: 12px; color: #777; text-align: center; padding: 20px; border-top: 1px solid #eee; margin-top: 20px; }
        .footer p { margin: 5px 0; }

        /* Outlook specific fix for button text color if needed, but !important usually handles it */
        /* .button a { color: #ffffff !important; text-decoration: none !important; } */
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header"><h1>{{ $company_name ?? config('app.company_name', 'Your Company') }}</h1></div>
        <div class="sub-header"><h2>{{ $app_subname ?? config('app.subname', 'JobBoard Application') }}</h2></div>
        <div class="content">
            <p>Dear <strong>{{ $approver_name ?? 'Approver' }}</strong> ({{ $department_name ?? 'N/A' }} Department Team),</p>
            <p>A new task has been submitted and requires your approval:</p>
            <div class="info">
                <p><strong>Job ID:</strong> {{ $task_id_job ?? 'N/A' }}<br>
                <strong>Requester:</strong> {{ $requester_name ?? 'N/A' }}<br>
                <strong>Target Department:</strong> {{ $department_name ?? 'N/A' }}<br>
                <strong>Location/Area:</strong> {{ $task_location ?? 'N/A' }}<br>
                <strong>Description:</strong><br>{{ $task_description ?? 'No description provided.' }}</p>
            </div>
            <p>Please review and approve or reject the task using the links below:</p>
            
            <!-- Button Container using a table for robust centering and spacing -->
            <div class="button-container-wrapper">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="margin: 0 auto;"> <!-- Centering table -->
                    <tr>
                        <td align="center" style="padding: 0 10px;"> <!-- Padding creates space between buttons -->
                            <a href="{{ $approveUrl ?? '#' }}" class="button approve">Approve Task</a>
                        </td>
                        <td align="center" style="padding: 0 10px;"> <!-- Padding creates space between buttons -->
                            <a href="{{ $rejectUrl ?? '#' }}" class="button reject">Reject Task</a>
                        </td>
                    </tr>
                </table>
            </div>

            <p>If you choose to reject, you will be prompted to provide a reason for rejection.</p>
            <p>If you are unable to click the buttons above, please copy and paste the following URLs into your browser:<br>
                Approve: {{ $approveUrl ?? '#' }}<br>
                Reject: {{ $rejectUrl ?? '#' }}
            </p>
            <p>Thank you for your attention and cooperation.</p>
            <p>Regards,<br>The {{ config('app.name') }} Notification System</p>
        </div>
        <div class="footer">
            <p>© {{ $footer_year ?? date('Y') }} {{ $company_name ?? config('app.company_name', 'Your Company') }}. All rights reserved.</p>
            <p>This is an automatically generated email. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>