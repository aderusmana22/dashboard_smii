<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $emailSubject ?? 'Task Status Update' }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
        .email-container { max-width: 600px; margin: 20px auto; border: 1px solid #ddd; background-color: #ffffff; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { background-color: #A78734; color: white; padding: 15px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: bold; }
        .sub-header { background-color: #B99B4A; color: white; padding: 10px 20px; text-align: center; }
        .sub-header h2 { margin: 0; font-size: 18px; font-weight: normal; }
        .content { padding: 25px; }
        .content p { line-height: 1.6; margin: 10px 0; }
        .info { background-color: #f9f9f9; border-left: 5px solid #A78734; padding: 15px; margin: 20px 0; }
        .info p { text-align: left; white-space: pre-line; margin: 5px 0; font-size: 14px; }
        .info strong { font-weight: bold; color: #555; }
        .info .status-text { font-weight: bold; }
        .info .status-approved { color: #28a745; }
        .info .status-rejected { color: #dc3545; }
        .info .status-completed { color: #17a2b8; }
        .info .status-cancelled { color: #ffc107; }
        .info .status-open { color: #007bff; }
        .reason { background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 15px 0; }
        .reason.rejection { background-color: #f8d7da; border-left-color: #dc3545; }
        .reason.cancellation { background-color: #fff3cd; border-left-color: #ffc107; }
        .reason p { text-align: left; white-space: pre-line; margin:0; }
        .footer { font-size: 12px; color: #777; text-align: center; padding: 20px; border-top: 1px solid #eee; margin-top: 20px; }
        .footer p { margin: 5px 0; }
        .overdue-deadline { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header"><h1>{{ $company_name ?? config('app.company_name', 'Your Company') }}</h1></div>
        <div class="sub-header">
            <h2>{{ $app_subname ?? 'Marsho JobBoard' }}</h2>
        </div>
        <div class="content">
            <p>Dear <strong>{{ $recipient_name ?? 'User' }}</strong>,</p>
            
            {{-- This $message_body will be constructed in your PHP code --}}
            {{-- For cancellation, it should be something like:
                 "Job ID: {{ $task_id_job }} for the {{ $department_name }} department has been cancelled."
                 (if $recipient_name is the requester)
                 or
                 "Job ID: {{ $task_id_job }} for the {{ $department_name }} department, requested by {{ $requester_name }}, has been cancelled."
                 (if $recipient_name is not the requester, and you want to inform them who requested it)
            --}}
            <p>{!! $message_body !!}</p>

            <div class="info">
                <p><strong>Job ID:</strong> {{ $task_id_job ?? 'N/A' }}<br>
                {{-- Conditional display of Requester, useful if the main message doesn't always include it --}}
                @if(isset($requester_name) && (!isset($is_requester_recipient) || !$is_requester_recipient) )
                    <strong>Requester:</strong> {{ $requester_name }}<br>
                @endif
                <strong>Target Department:</strong> {{ $department_name ?? 'N/A' }}<br>
                <strong>Location/Area:</strong> {{ $task_location ?? 'N/A' }}<br>
                <strong>Current Status:</strong>
                    <span class="status-text status-{{ strtolower(str_replace(' ', '_', $current_task_status_text ?? '')) }}">
                        {{ $current_task_status_text ?? 'N/A' }}
                    </span><br>
                @if(isset($action_status) && $action_status === 'overdue' && isset($task) && property_exists($task, 'original_deadline') && $task->original_deadline)
                    <strong>Original Deadline:</strong> <span class="overdue-deadline">{{ \Carbon\Carbon::parse($task->original_deadline)->format('d M Y') }}</span><br>
                @endif
                <strong>Description:</strong><br>{{ $task_description ?? 'No description provided.' }}</p>
            </div>

            @if(isset($rejection_reason) && $rejection_reason)
            <div class="reason rejection">
                <p><strong>Reason for Rejection:</strong><br>{{ $rejection_reason }}</p>
            </div>
            @endif

            {{-- This section for cancellation reason remains, which is good --}}
            @if(isset($cancel_reason) && $cancel_reason)
            <div class="reason cancellation">
                <p><strong>Reason for Cancellation:</strong><br>{{ $cancel_reason }}</p>
            </div>
            @endif

            @if(isset($action_status))
                @if($action_status === \App\Models\JobApprovalDetail::STATUS_APPROVED || ($action_status === \App\Models\Task::STATUS_OPEN && isset($task) && $task->wasChanged('status') && $task->getOriginal('status') === \App\Models\Task::STATUS_PENDING_APPROVAL) )
                    <p>The relevant department will proceed according to internal procedures.</p>
                @elseif($action_status === \App\Models\Task::STATUS_CANCELLED && isset($recipient_name) && $recipient_name !== ($requester_name ?? ''))
                     {{-- This message is for users OTHER than the requester about a cancellation --}}
                    <p>No further action is required from you for this task.</p>
                @elseif($action_status === \App\Models\Task::STATUS_CANCELLED && isset($recipient_name) && $recipient_name === ($requester_name ?? ''))
                     {{-- This message is for the REQUESTER about THEIR cancellation --}}
                    <p>You have successfully cancelled this task.</p>
                @elseif($action_status === \App\Models\Task::STATUS_COMPLETED)
                    <p>No further action is required. Thank you for your coordination.</p>
                @elseif($action_status === 'overdue')
                    <p>Please take immediate action on this task. We require a status update, reason for delay, and a new estimated time of completion (ETC) within the next <strong>24 hours</strong>.</p>
                    <p>If there are any issues, please report them immediately to [Manager/Relevant Contact].</p>
                @endif
            @endif

            <p>You can view more task details in the {{ config('app.subname', 'Marsho JobBoard') }} system.</p>
            <p>Thank you.</p>
            <p>Regards,<br>The {{ config('app.name') }} Notification System</p>
        </div>
        <div class="footer">
            <p>© {{ $footer_year ?? date('Y') }} {{ $company_name ?? config('app.company_name', 'Your Company') }}. All rights reserved.</p>
            <p>This is an automatically generated email. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>