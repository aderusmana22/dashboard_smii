<x-app-layout>
@section('title', 'Laporan Daftar Job Kanban')

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Laporan Daftar Job Kanban</h3>
                </div>
                <div class="card-body">
                    {{-- Filter Section --}}
                    <form method="GET" action="{{ route('reports.tasks.list') }}" class="mb-4">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label for="search_filter" class="form-label">Pencarian</label>
                                <input type="text" class="form-control" id="search_filter" name="search_filter" value="{{ $request->input('search_filter') }}" placeholder="ID Job, Area, List Job, Pengaju...">
                            </div>

                            <div class="col-md-2">
                                <label for="status_filter" class="form-label">Status Task</label>
                                <select class="form-select" id="status_filter" name="status_filter">
                                    <option value="">Semua Status</option>
                                    @if(isset($taskStatusesForFilter))
                                        @foreach($taskStatusesForFilter as $key => $value)
                                            <option value="{{ $key }}" {{ $request->input('status_filter') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                        @endforeach
                                    @else
                                        <option value="" disabled>Data status tidak tersedia</option>
                                    @endif
                                </select>
                            </div>

                            @if(Auth::user()->isSuperAdmin() || Auth::user()->isAdminProject())
                                <div class="col-md-3">
                                    <label for="department_filter" class="form-label">Departemen Tujuan</label>
                                    <select class="form-select" id="department_filter" name="department_filter">
                                        <option value="">Semua Departemen</option>
                                        @if(isset($departmentsForFilter))
                                            @foreach($departmentsForFilter as $id => $name)
                                                <option value="{{ $id }}" {{ $request->input('department_filter') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                            @endforeach
                                        @else
                                            <option value="" disabled>Data departemen tidak tersedia</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="pengaju_filter" class="form-label">Pengaju</label>
                                    <select class="form-select" id="pengaju_filter" name="pengaju_filter">
                                        <option value="">Semua Pengaju</option>
                                        @if(isset($usersForFilter))
                                            @foreach($usersForFilter as $id => $name)
                                                <option value="{{ $id }}" {{ $request->input('pengaju_filter') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                            @endforeach
                                        @else
                                            <option value="" disabled>Data user tidak tersedia</option>
                                        @endif
                                    </select>
                                </div>
                            @else
                                {{-- User biasa hanya bisa filter departemennya sendiri jika ada, atau tasks yg diajukan --}}
                                @if(Auth::user()->department_id && isset($departmentsForFilter))
                                <div class="col-md-3">
                                    <label for="department_filter" class="form-label">Departemen Tujuan</label>
                                    <select class="form-select" id="department_filter" name="department_filter">
                                        <option value="">Semua (Yang Diajukan Saya)</option>
                                        <option value="{{ Auth::user()->department_id }}" {{ $request->input('department_filter') == Auth::user()->department_id ? 'selected' : '' }}>
                                            {{ Auth::user()->department->department_name ?? 'Departemen Saya' }}
                                        </option>
                                        @foreach($departmentsForFilter as $id => $name)
                                            @if($id != Auth::user()->department_id)
                                            <option value="{{ $id }}" {{ $request->input('department_filter') == $id ? 'selected' : '' }}>
                                                {{ $name }} (Hanya yang saya ajukan)
                                            </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                            @endif


                            <div class="col-md-2">
                                <label for="date_from_filter" class="form-label">Dari Tanggal Dibuat</label>
                                <input type="date" class="form-control" id="date_from_filter" name="date_from_filter" value="{{ $request->input('date_from_filter') }}">
                            </div>

                            <div class="col-md-2">
                                <label for="date_to_filter" class="form-label">Sampai Tanggal Dibuat</label>
                                <input type="date" class="form-control" id="date_to_filter" name="date_to_filter" value="{{ $request->input('date_to_filter') }}">
                            </div>

                            <div class="col-md-auto">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="{{ route('reports.tasks.list') }}" class="btn btn-secondary">Reset</a>
                            </div>
                        </div>
                    </form>

                    {{-- Export Button --}}
                    @php
                        $exportParams = $request->all(); // Ambil semua parameter filter saat ini
                    @endphp
                    <div class="mb-3">
                        <a href="{{ route('reports.tasks.export', $exportParams) }}" class="btn btn-success">
                            <i class="fas fa-file-excel"></i> Export ke Excel
                        </a>
                    </div>


                    {{-- Task List Table --}}
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID Job</th>
                                    <th>Pengaju</th>
                                    <th>Dept. Tujuan</th>
                                    <th>Area</th>
                                    <th>List Job</th>
                                    <th>Tgl Mulai</th>
                                    <th>Tgl Selesai</th>
                                    <th>Status Task</th>
                                    <th>Diproses Oleh (Approver)</th>
                                    <th>Status Approval</th>
                                    <th>Tgl Proses Approval</th>
                                    <th>Catatan Approval</th>
                                    <th>Alasan Batal</th>
                                    <th>Ditutup Oleh</th>
                                    <th>Tgl Ditutup</th>
                                    <th>Tgl Dibuat</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($tasks) && $tasks->count() > 0)
                                    @foreach ($tasks as $task)
                                        @php
                                            $processedApproval = $task->processedApprovalDetail(); // Helper method dari model Task
                                        @endphp
                                        <tr>
                                            <td>{{ $task->id_job }}</td>
                                            <td>{{ $task->pengaju->name ?? 'N/A' }} <small>({{ $task->pengaju->nik ?? '' }})</small></td>
                                            <td>{{ $task->department->department_name ?? 'N/A' }}</td>
                                            <td>{{ $task->area }}</td>
                                            <td>
                                                <span title="{{ $task->list_job }}">
                                                    {{ Str::limit($task->list_job, 50) }}
                                                </span>
                                            </td>
                                            <td>{{ $task->tanggal_job_mulai ? \Carbon\Carbon::parse($task->tanggal_job_mulai)->format('d M Y') : '' }}</td>
                                            <td>{{ $task->tanggal_job_selesai ? \Carbon\Carbon::parse($task->tanggal_job_selesai)->format('d M Y') : '' }}</td>
                                            <td>
                                                <span class="badge {{ $task->status_badge_class ?? 'bg-secondary' }}">
                                                    {{ $task->status_text ?? $task->status }}
                                                </span>
                                            </td>
                                            <td>{{ $processedApproval && $processedApproval->approver ? ($processedApproval->approver->name . ' (' . $processedApproval->approver->nik . ')') : '-' }}</td>
                                            <td>
                                                @if($processedApproval)
                                                <span class="badge {{ $processedApproval->status_badge_class ?? 'bg-secondary' }}">
                                                    {{ $processedApproval->status_text ?? $processedApproval->status }}
                                                </span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $processedApproval && $processedApproval->processed_at ? \Carbon\Carbon::parse($processedApproval->processed_at)->format('d M Y H:i') : '-' }}</td>
                                            <td>{{ $processedApproval->notes ?? '-' }}</td>
                                            <td>{{ $task->cancel_reason ?? '-' }}</td>
                                            <td>{{ $task->penutup->name ?? '-' }} <small>({{ $task->penutup->nik ?? '' }})</small></td>
                                            <td>{{ $task->closed_at ? \Carbon\Carbon::parse($task->closed_at)->format('d M Y H:i') : '-' }}</td>
                                            <td>{{ $task->created_at->format('d M Y H:i') }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="16" class="text-center">
                                            @if(isset($tasks))
                                                Tidak ada data job ditemukan.
                                            @else
                                                Data job tidak dapat dimuat. Pastikan controller mengirimkan variabel 'tasks'.
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-3">
                        @if(isset($tasks))
                            {{ $tasks->appends($request->all())->links() }}
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Jika menggunakan Select2 untuk dropdown filter
        // $('.form-select').select2({ theme: 'bootstrap-5' });

        // Untuk datepicker jika menggunakan (misal Bootstrap Datepicker atau lainnya)
        // $('#date_from_filter, #date_to_filter').datepicker({
        //     format: 'yyyy-mm-dd',
        //     autoclose: true,
        //     todayHighlight: true
        // });
    });
</script>
@endpush

@push('styles')
{{-- Jika menggunakan Select2 atau Datepicker, tambahkan CSS-nya di sini --}}
{{-- <link href="path/to/select2.min.css" rel="stylesheet" /> --}}
{{-- <link href="path/to/bootstrap-datepicker.min.css" rel="stylesheet" /> --}}
<style>
    .badge.bg-pending-approval { background-color: #ffc107; color: #000; }
    .badge.bg-open { background-color: #0dcaf0; color: #000; }
    .badge.bg-on-progress { background-color: #0d6efd; } /* Anda mungkin perlu menambahkan status ini di model Task */
    .badge.bg-completed { background-color: #198754; }
    .badge.bg-rejected { background-color: #dc3545; }
    .badge.bg-cancelled { background-color: #6c757d; }
    .badge.bg-closed { background-color: #212529; }
    /* Untuk JobApprovalDetail */
    .badge.bg-pending { background-color: #ffc107; color: #000;} /* Sama dengan pending_approval atau beda */
    .badge.bg-approved { background-color: #198754; }
    /* .badge.bg-rejected sudah ada di atas */
    .badge.bg-superseded { background-color: #adb5bd; color: #000; }
</style>
@endpush
</x-app-layout>