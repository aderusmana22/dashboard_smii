{{-- File: resources/views/emails/tasks/approval_request.blade.php (Markdown version) --}}
@component('mail::message')
# Permintaan Persetujuan Tugas Baru

Halo Tim Departemen {{ $task->department->department_name ?? 'N/A' }},

Sebuah tugas baru telah diajukan dan membutuhkan persetujuan Anda:

**ID Job:** {{ $task->id_job ?? 'N/A' }}
**Pengaju:** {{ $task->pengaju->name ?? 'N/A' }}
**Departemen Tujuan:** {{ $task->department->department_name ?? 'N/A' }}
**Area/Lokasi:** {{ $task->area ?? 'N/A' }}
**List Job/Deskripsi:**