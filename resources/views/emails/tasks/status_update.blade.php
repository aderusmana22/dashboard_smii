{{-- File: resources/views/emails/tasks/status_update.blade.php (Markdown version) --}}
@component('mail::message')
# Pembaruan Status Tugas: {{ $task->id_job ?? 'N/A' }}

Halo {{ $recipientName ?? 'Pengguna' }},

{!! $messageBody !!} {{-- Use {!! !!} if messageBody might contain HTML, otherwise {{ }} --}}

**Detail Tugas:**
- **ID Job:** {{ $task->id_job ?? 'N/A' }}
- **Pengaju:** {{ $task->pengaju->name ?? 'N/A' }}
- **Departemen Tujuan:** {{ $task->department->department_name ?? 'N/A' }}
- **Area/Lokasi:** {{ $task->area ?? 'N/A' }}
- **Status Saat Ini:** {{ Illuminate\Support\Str::upper(str_replace('_', ' ', $task->status ?? 'N/A')) }}

@if(isset($task->status) && $task->status === \App\Models\Task::STATUS_REJECTED && !empty($rejection_reason))
**Alasan Penolakan:**
{{ $rejection_reason }}
@endif

@if(isset($task->status) && $task->status === \App\Models\Task::STATUS_CANCELLED && !empty($cancel_reason))
**Alasan Pembatalan:**
{{ $cancel_reason }}
@endif

Anda dapat melihat detail tugas di sistem {{ config('app.subname', 'Marsho JobBoard') }}.

Terima kasih,
Sistem Notifikasi {{ config('app.name') }}
@endcomponent