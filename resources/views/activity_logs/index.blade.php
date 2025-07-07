<x-app-layout>
    @section('title', 'Kanban Activity Log')

    {{-- Style untuk membuat layout tabel tetap/fixed --}}
    <style>
        .table-fixed {
            table-layout: fixed;
            width: 100%;
        }

        .table-fixed td,
        .table-fixed th {
            /* Memaksa teks panjang untuk pindah baris */
            overflow-wrap: break-word;
        }

        /* Style bawaan Anda */
        table.table.table-bordered td,
        table.table.table-bordered th {
            border: 1px solid rgb(102, 110, 117) !important;
            vertical-align: middle;
        }

        div.card-header {
            border-bottom: 1px solid rgb(102, 110, 117) !important;
        }

        [data-bs-theme="dark"] .table-bordered th,
        [data-bs-theme="dark"] .table-bordered td {
            border-color: #495057 !important;
        }
    </style>

    <div class="container-fluid mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Kanban Activity Log</h3>
            </div>
            <div class="card-body">
                {{-- FORM FILTER (Tidak ada perubahan di sini) --}}
                <form method="GET" action="{{ route('activity-logs.index') }}" class="mb-4">
                    <div class="table-responsive">
                        <table class="table table-bordered" style="width: 100%;">
                            <tbody>
                                <tr class="align-middle">
                                    <td><label for="subject_filter" class="form-label mb-0 fw-bold">Task Job ID</label></td>
                                    <td><label for="event_filter" class="form-label mb-0 fw-bold">Event</label></td>
                                    <td><label for="causer_filter" class="form-label mb-0 fw-bold">Performed By</label></td>
                                    <td><label for="date_from_filter" class="form-label mb-0 fw-bold">From Date</label></td>
                                    <td><label for="date_to_filter" class="form-label mb-0 fw-bold">To Date</label></td>
                                    <td style="width: 1%;"></td>
                                </tr>
                                <tr>
                                    <td>
                                        <input type="text" class="form-control" id="subject_filter" name="subject_filter"
                                            value="{{ $request->input('subject_filter') }}" placeholder="Enter Job ID">
                                    </td>
                                    <td>
                                        <select class="form-select" id="event_filter" name="event_filter">
                                            <option value="">All Events</option>
                                            @foreach($eventNames as $event)
                                            <option value="{{ $event }}" {{ $request->input('event_filter') == $event ? 'selected' : '' }}>
                                                {{ ucfirst($event) }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select" id="causer_filter" name="causer_filter">
                                            <option value="">All Users</option>
                                            @foreach($users as $id => $name)
                                            <option value="{{ $id }}" {{ $request->input('causer_filter') == $id ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="date" class="form-control" id="date_from_filter" name="date_from_filter"
                                            value="{{ $request->input('date_from_filter') }}">
                                    </td>
                                    <td>
                                        <input type="date" class="form-control" id="date_to_filter" name="date_to_filter"
                                            value="{{ $request->input('date_to_filter') }}">
                                    </td>
                                    <td style="vertical-align: bottom; white-space: nowrap;">
                                        <button type="submit" class="btn btn-primary">Filter</button>
                                        <a href="{{ route('activity-logs.index') }}" class="btn btn-secondary">Reset</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </form>


                {{-- TABEL DATA DENGAN LAYOUT FIXED DAN KOLOM DETAILS DIHILANGKAN --}}
               
               <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover table-fixed">
                        <thead>
                            <tr>
                                <th style="width: 5%;">ID</th>
                                <th style="width: 10%;">Task Job ID</th>
                                <th style="width: 12%;">Requester</th>
                                <th style="width: 12%;">Dept To</th>
                                <th style="width: 26%;">Description</th>
                                <th style="width: 10%;">Event</th>
                                <th style="width: 10%;">Performed By</th>
                                <th style="width: 15%;">Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($activities as $activity)
                                <tr>
                                    <td>{{ $activity->id }}</td>
                                    <td>
                                        @if ($activity->subject && $activity->subject_type == \App\Models\Task::class)
                                            {{ $activity->subject->id_job }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    
                                    {{-- Menampilkan data dari relasi yang benar: 'pengaju' dan 'department' --}}
                                    @if ($activity->subject && $activity->subject_type == \App\Models\Task::class)
                                        <td>{{ $activity->subject->pengaju->name ?? 'N/A' }}</td>
                                        <td>{{ $activity->subject->department->department_name ?? 'N/A' }}</td>
                                    @else
                                        <td>N/A</td>
                                        <td>N/A</td>
                                    @endif
                                    
                                    <td>{{ $activity->description }}</td>
                                    <td><span class="badge bg-info text-dark">{{ $activity->event }}</span></td>
                                    <td>{{ $activity->causer->name ?? 'System' }}</td>
                                    <td>{{ $activity->created_at->format('d M Y, H:i:s') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">No activity logs found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $activities->appends($request->all())->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>