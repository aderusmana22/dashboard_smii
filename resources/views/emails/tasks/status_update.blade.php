@component('mail::message')
# Pembaruan Status Tugas: {{ $task->id_job }}

Halo {{ $recipientName }},

{{ $messageBody }}

**Detail Tugas:**
- **ID Job:** {{ $task->id_job }}
- **Departemen Tujuan:** {{ $task->department->department_name }}
- **Area:** {{ $task->area }}
- **Status Saat Ini:** {{ Illuminate\Support\Str::upper(str_replace('_', ' ', $task->status)) }} {{-- More readable status --}}

@if($task->status === \App\Models\Task::STATUS_REJECTED && $task->rejection_reason)
**Alasan Penolakan:**
{{ $task->rejection_reason }}
@endif

Anda dapat melihat detail tugas di sistem Kanban.

Terima kasih,
{{ config('app.name') }}
@endcomponent