@component('mail::message')
# Permintaan Persetujuan Tugas Baru

Halo Tim Departemen {{ $task->department->department_name }},

Sebuah tugas baru telah diajukan dan membutuhkan persetujuan Anda:

**ID Job:** {{ $task->id_job }}
**Pengaju:** {{ $task->pengaju->name }}
**Area:** {{ $task->area }}
**List Job:** {{ $task->list_job }}

Mohon untuk meninjau dan memberikan persetujuan atau penolakan melalui tautan di bawah ini:

@component('mail::button', ['url' => $approveUrl, 'color' => 'success'])
Setujui Tugas
@endcomponent

@component('mail::button', ['url' => $rejectUrl, 'color' => 'error'])
Tolak Tugas
@endcomponent

Jika Anda menolak, Anda akan diminta untuk memberikan alasan.

Terima kasih,
{{ config('app.name') }}
@endcomponent