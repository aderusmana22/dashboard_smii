<x-app-layout>
@section('title', 'Job Kanban List Report')

<style>
    table.table.table-bordered td,
    table.table.table-bordered th {
        border: 1px solid rgb(102, 110, 117) !important;
    }

    div.card-header {
        border-bottom: 1px solid rgb(102, 110, 117) !important;
    }

    [data-bs-theme="dark"] .table-bordered th,
    [data-bs-theme="dark"] .table-bordered td {
        border-color: #495057 !important;
    }

    .badge.bg-pending-approval { background-color: #ffc107; color: #000; }
    .badge.bg-open { background-color: #0dcaf0; color: #000; }
    .badge.bg-on-progress { background-color: #0d6efd; color: #fff; }
    .badge.bg-completed { background-color: #198754; color: #fff; }
    .badge.bg-rejected { background-color: #dc3545; color: #fff; }
    .badge.bg-cancelled { background-color: #6c757d; color: #fff; }
    .badge.bg-closed { background-color: #212529; color: #fff; }

    .badge.bg-approval-pending { background-color: #ffc107; color: #000; }
    .badge.bg-approval-approved { background-color: #198754; color: #fff; }
    .badge.bg-approval-superseded { background-color: #adb5bd; color: #000; }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Job Kanban List Report</h3>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('reports.tasks.list') }}" class="mb-4">
                        <div class="table-responsive">
                            <table class="table table-bordered" style="width: 100%;">
                                <tbody>
                                    <tr class="align-middle">
                                        <td><label for="search_filter" class="form-label mb-0 fw-bold">Search</label></td>
                                        <td><label for="status_filter" class="form-label mb-0 fw-bold">Task Status</label></td>
                                        
                                        @if(Auth::user()->isSuperAdmin() || Auth::user()->isAdminProject())
                                            <td><label for="department_filter" class="form-label mb-0 fw-bold">To Dept.</label></td>
                                            <td><label for="pengaju_filter" class="form-label mb-0 fw-bold">Requester</label></td>
                                        @else
                                            @if(Auth::user()->department_id && isset($departmentsForFilter))
                                                <td><label for="department_filter" class="form-label mb-0 fw-bold">To Dept.</label></td>
                                            @endif
                                        @endif
                                        
                                        <td><label for="date_from_filter" class="form-label mb-0 fw-bold">From Date</label></td>
                                        <td><label for="date_to_filter" class="form-label mb-0 fw-bold">To Date</label></td>
                                        
                                        <td style="width: 1%;"> </td> 
                                    </tr>

                                    <tr>
                                        <td>
                                            <input type="text" class="form-control" id="search_filter" name="search_filter" value="{{ $request->input('search_filter') }}" placeholder="Job ID, Area...">
                                        </td>

                                        <td>
                                            <select class="form-select" id="status_filter" name="status_filter">
                                                <option value="">All Statuses</option>
                                                @if(isset($taskStatusesForFilter))
                                                    @foreach($taskStatusesForFilter as $key => $value)
                                                        <option value="{{ $key }}" {{ $request->input('status_filter') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                                    @endforeach
                                                @else
                                                    <option value="" disabled>Status data not available</option>
                                                @endif
                                            </select>
                                        </td>

                                        @if(Auth::user()->isSuperAdmin() || Auth::user()->isAdminProject())
                                            <td>
                                                <select class="form-select" id="department_filter" name="department_filter">
                                                    <option value="">All Departments</option>
                                                    @if(isset($departmentsForFilter))
                                                        @foreach($departmentsForFilter as $id => $name)
                                                            <option value="{{ $id }}" {{ $request->input('department_filter') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                                        @endforeach
                                                    @else
                                                        <option value="" disabled>Department data not available</option>
                                                    @endif
                                                </select>
                                            </td>
                                            <td>
                                                <select class="form-select" id="pengaju_filter" name="pengaju_filter">
                                                    <option value="">All Requesters</option>
                                                    @if(isset($usersForFilter))
                                                        @foreach($usersForFilter as $id => $name)
                                                            <option value="{{ $id }}" {{ $request->input('pengaju_filter') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                                        @endforeach
                                                    @else
                                                        <option value="" disabled>User data not available</option>
                                                    @endif
                                                </select>
                                            </td>
                                        @else
                                            @if(Auth::user()->department_id && isset($departmentsForFilter))
                                            <td>
                                                <select class="form-select" id="department_filter" name="department_filter">
                                                    <option value="">All (Requested by me)</option>
                                                    <option value="{{ Auth::user()->department_id }}" {{ $request->input('department_filter') == Auth::user()->department_id ? 'selected' : '' }}>
                                                        {{ Auth::user()->department->department_name ?? 'My Department' }}
                                                    </option>
                                                    @foreach($departmentsForFilter as $id => $name)
                                                        @if($id != Auth::user()->department_id)
                                                        <option value="{{ $id }}" {{ $request->input('department_filter') == $id ? 'selected' : '' }}>
                                                            {{ $name }} (Only those I requested)
                                                        </option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </td>
                                            @endif
                                        @endif

                                        <td>
                                            <input type="date" class="form-control" id="date_from_filter" name="date_from_filter" value="{{ $request->input('date_from_filter') }}">
                                        </td>
                                        <td>
                                            <input type="date" class="form-control" id="date_to_filter" name="date_to_filter" value="{{ $request->input('date_to_filter') }}">
                                        </td>

                                        <td style="vertical-align: bottom; white-space: nowrap;">
                                            <button type="submit" class="btn btn-primary">Filter</button>
                                            <a href="{{ route('reports.tasks.list') }}" class="btn btn-secondary">Reset</a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </form>

                    @php
                        $exportParams = $request->all();
                    @endphp
                    <div class="mb-3">
                        <a href="{{ route('reports.tasks.export', $exportParams) }}" class="btn btn-success">
                            <i class="fas fa-file-excel"></i> Export to Excel
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Job ID</th>
                                    <th>Requester</th>
                                    <th>To Dept.</th>
                                    <th>Area</th>
                                    <th>Job List</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Task Status</th>
                                    <th>Processed By (Approver)</th>
                                    <th>Approval Status</th>
                                    <th>Approval Process Date</th>
                                    <th>Approval Notes</th>
                                    <th>Cancellation Reason</th>
                                    <th>Closed By</th>
                                    <th>Closed Date</th>
                                    <th>Created Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($tasks as $task)
                                    @php
                                        $processedApproval = $task->processedApprovalDetail();
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
                                                @php
                                                    $approvalBadgeClass = '';
                                                    switch ($processedApproval->status) {
                                                        case 'pending':
                                                            $approvalBadgeClass = 'bg-approval-pending';
                                                            break;
                                                        case 'approved':
                                                            $approvalBadgeClass = 'bg-approval-approved';
                                                            break;
                                                        case 'superseded':
                                                            $approvalBadgeClass = 'bg-approval-superseded';
                                                            break;
                                                        default:
                                                            $approvalBadgeClass = 'bg-secondary';
                                                    }
                                                @endphp
                                                <span class="badge {{ $approvalBadgeClass }}">
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
                                @empty
                                    <tr>
                                        <td colspan="16" class="text-center">
                                            No job data found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

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

</x-app-layout>