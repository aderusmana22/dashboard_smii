<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $emailSubject ?? 'Pembaruan Status Tugas' }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
        .email-container { max-width: 600px; margin: 20px auto; border: 1px solid #ddd; background-color: #ffffff; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { background-color: #A78734; color: white; padding: 15px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: bold; }
        .sub-header { background-color: #B99B4A; color: white; padding: 10px 20px; text-align: center; }
        .sub-header.approved { background-color: #28a745; }
        .sub-header.rejected { background-color: #dc3545; }
        .sub-header.completed { background-color: #17a2b8; }
        .sub-header.cancelled { background-color: #ffc107; color: #333; }
        .sub-header.open { background-color: #007bff; }
        .sub-header.overdue { background-color: #dc3545; } /* Specific for overdue concept if used */
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
        .reason { background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 15px 0; } /* For general reasons */
        .reason.rejection { background-color: #f8d7da; border-left-color: #dc3545; } /* Specific for rejection */
        .reason.cancellation { background-color: #fff3cd; border-left-color: #ffc107; } /* Specific for cancellation */
        .reason p { text-align: left; white-space: pre-line; margin:0; }
        .footer { font-size: 12px; color: #777; text-align: center; padding: 20px; border-top: 1px solid #eee; margin-top: 20px; }
        .footer p { margin: 5px 0; }
        .overdue-deadline { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header"><h1>{{ $company_name ?? config('app.company_name', 'Perusahaan Anda') }}</h1></div>
        @php
            $subHeaderClass = '';
            if (isset($action_status)) {
                switch ($action_status) {
                    case \App\Models\JobApprovalDetail::STATUS_APPROVED: $subHeaderClass = 'approved'; break;
                    case \App\Models\JobApprovalDetail::STATUS_REJECTED: $subHeaderClass = 'rejected'; break;
                    case \App\Models\Task::STATUS_COMPLETED: $subHeaderClass = 'completed'; break;
                    case \App\Models\Task::STATUS_CANCELLED: $subHeaderClass = 'cancelled'; break;
                    case \App\Models\Task::STATUS_OPEN: $subHeaderClass = 'open'; break;
                    case 'overdue': $subHeaderClass = 'overdue'; break; // If you implement overdue notifications
                }
            }
        @endphp
        <div class="sub-header {{ $subHeaderClass }}">
            <h2>{{ $app_subname ?? 'Pembaruan Tugas' }}</h2>
        </div>
        <div class="content">
            <p>Dear <strong>{{ $recipient_name ?? 'Pengguna' }}</strong>,</p>
            <p>{!! $message_body ?? 'Berikut adalah pembaruan mengenai tugas terkait.' !!}</p>

            <div class="info">
                <p><strong>Job ID:</strong> {{ $task_id_job ?? 'N/A' }}<br>
                @if(!isset($action_status) || $action_status !== \App\Models\Task::STATUS_CANCELLED || (isset($action_status) && $action_status === \App\Models\Task::STATUS_CANCELLED && $recipient_name !== ($requester_name ?? '')))
                    <strong>Requester:</strong> {{ $requester_name ?? 'N/A' }}<br>
                @endif
                <strong>Departemen Tujuan:</strong> {{ $department_name ?? 'N/A' }}<br>
                <strong>Location/Area:</strong> {{ $task_location ?? 'N/A' }}<br>
                <strong>Status Saat Ini:</strong>
                    <span class="status-text status-{{ strtolower(str_replace(' ', '_', $current_task_status_text ?? '')) }}">
                        {{ $current_task_status_text ?? 'N/A' }}
                    </span><br>
                @if(isset($action_status) && $action_status === 'overdue' && isset($task) && property_exists($task, 'original_deadline') && $task->original_deadline)
                    <strong>Original Deadline:</strong> <span class="overdue-deadline">{{ \Carbon\Carbon::parse($task->original_deadline)->format('d M Y') }}</span><br>
                @endif
                <strong>Description:</strong><br>{{ $task_description ?? 'Tidak ada deskripsi.' }}</p>
            </div>

            @if(isset($rejection_reason) && $rejection_reason)
            <div class="reason rejection">
                <p><strong>Alasan Penolakan:</strong><br>{{ $rejection_reason }}</p>
            </div>
            @endif

            @if(isset($cancel_reason) && $cancel_reason)
            <div class="reason cancellation">
                <p><strong>Alasan Pembatalan:</strong><br>{{ $cancel_reason }}</p>
            </div>
            @endif

            @if(isset($action_status))
                @if($action_status === \App\Models\JobApprovalDetail::STATUS_APPROVED || ($action_status === \App\Models\Task::STATUS_OPEN && $task->wasChanged('status') && $task->getOriginal('status') === \App\Models\Task::STATUS_PENDING_APPROVAL) )
                    <p>Departemen terkait akan melanjutkan proses sesuai prosedur internal.</p>
                @elseif($action_status === \App\Models\Task::STATUS_CANCELLED && isset($recipient_name) && $recipient_name !== ($requester_name ?? ''))
                    <p>Tidak ada tindakan lebih lanjut yang diperlukan untuk tugas ini.</p>
                @elseif($action_status === \App\Models\Task::STATUS_COMPLETED)
                    <p>Tidak ada tindakan lebih lanjut yang diperlukan. Terima kasih atas koordinasinya.</p>
                @elseif($action_status === 'overdue')
                    <p>Mohon segera ambil tindakan untuk tugas ini. Kami memerlukan pembaruan status, alasan keterlambatan, dan estimasi tanggal penyelesaian (ETA) baru dalam <strong>24 jam</strong> ke depan.</p>
                    <p>Jika ada kendala, harap segera laporkan ke [Manajer/Kontak Relevan].</p>
                @endif
            @endif

            <p>Anda dapat melihat detail tugas lebih lanjut di sistem {{ config('app.subname', 'Marsho JobBoard') }}.</p>
            <p>Terima kasih.</p>
            <p>Regards,<br>Sistem Notifikasi {{ config('app.name') }}</p>
        </div>
        <div class="footer">
            <p>&copy; {{ $footer_year ?? date('Y') }} {{ $company_name ?? config('app.company_name', 'Perusahaan Anda') }}. All rights reserved.</p>
            <p>Ini adalah email yang dibuat secara otomatis. Mohon untuk tidak membalas email ini.</p>
        </div>
    </div>
</body>
</html>