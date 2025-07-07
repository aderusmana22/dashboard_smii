<x-app-layout>
    @section('title', 'Kanban Activity Log')

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
    </style>

    <div class="container-fluid mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Kanban Activity Log</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('activity-logs.index') }}" class="mb-4">
                    <div class="table-responsive">
                        <table class="table table-bordered" style="width: 100%;">
                            <tbody>
                                <tr class="align-middle">
                                    <td><label for="subject_filter" class="form-label fw-bold mb-0">Task Job ID</label></td>
                                    <td><label for="event_filter" class="form-label fw-bold mb-0">Event</label></td>
                                    <td><label for="causer_filter" class="form-label fw-bold mb-0">Performed By</label></td>
                                    <td><label for="date_from_filter" class="form-label fw-bold mb-0">From Date</label></td>
                                    <td><label for="date_to_filter" class="form-label fw-bold mb-0">To Date</label></td>
                                    <td style="width: 1%;"> </td>
                                </tr>
                                <tr>
                                    <td>
                                        <input type="text" class="form-control" id="subject_filter" name="subject_filter" value="{{ $request->input('subject_filter') }}" placeholder="Enter Job ID">
                                    </td>
                                    <td>
                                        <select class="form-select" id="event_filter" name="event_filter">
                                            <option value="">All Events</option>
                                            @foreach($eventNames as $event)
                                                <option value="{{ $event }}" {{ $request->input('event_filter') == $event ? 'selected' : '' }}>{{ ucfirst($event) }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select" id="causer_filter" name="causer_filter">
                                            <option value="">All Users</option>
                                            @foreach($users as $id => $name)
                                                <option value="{{ $id }}" {{ $request->input('causer_filter') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="date" class="form-control" id="date_from_filter" name="date_from_filter" value="{{ $request->input('date_from_filter') }}">
                                    </td>
                                    <td>
                                        <input type="date" class="form-control" id="date_to_filter" name="date_to_filter" value="{{ $request->input('date_to_filter') }}">
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

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Log ID</th>
                                <th>Description</th>
                                <th>Subject</th>
                                <th>Event</th>
                                <th>Performed By</th>
                                <th>Changed Properties</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($activities as $activity)
                                <tr>
                                    <td>{{ $activity->id }}</td>
                                    <td>{{ $activity->description }}</td>
                                    <td>
                                        @if ($activity->subject)
                                            Task: {{ $activity->subject->id_job ?? $activity->subject_id }}
                                            ({{ Str::afterLast($activity->subject_type, '\\') }})
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td><span class="badge bg-info text-dark">{{ $activity->event }}</span></td>
                                    <td>{{ $activity->causer->name ?? 'System' }}</td>
                                    <td>
                                        @if ($activity->properties && ($activity->properties->has('old') || $activity->properties->has('attributes')))
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#propertiesModal{{ $activity->id }}">
                                                View
                                            </button>
                                            <div class="modal fade" id="propertiesModal{{ $activity->id }}" tabindex="-1" aria-labelledby="propertiesModalLabel{{ $activity->id }}" aria-hidden="true">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="propertiesModalLabel{{ $activity->id }}">Change Details for Log #{{ $activity->id }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            @if($activity->properties->has('attributes'))
                                                                <h6>New / Changed Data:</h6>
                                                                <pre class="bg-light p-2 rounded"><code>{{ json_encode($activity->properties['attributes'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                                            @endif
                                                             @if($activity->properties->has('old'))
                                                                <h6 class="mt-3">Old Data:</h6>
                                                                <pre class="bg-light p-2 rounded"><code>{{ json_encode($activity->properties['old'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $activity->created_at->format('d M Y, H:i:s') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No activity logs found.</td>
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