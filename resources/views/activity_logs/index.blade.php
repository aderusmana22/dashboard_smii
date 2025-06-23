<x-app-layout>
    @section('title', 'Log Aktivitas Kanban')

    <div class="container-fluid mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Log Aktivitas Kanban</h3>
            </div>
            <div class="card-body">
                {{-- Filter Section --}}
                <form method="GET" action="{{ route('activity-logs.index') }}" class="mb-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="subject_filter" class="form-label">ID Job Task</label>
                            <input type="text" class="form-control" id="subject_filter" name="subject_filter" value="{{ $request->input('subject_filter') }}" placeholder="Masukkan ID Job">
                        </div>

                        <div class="col-md-2">
                            <label for="event_filter" class="form-label">Event</label>
                            <select class="form-select" id="event_filter" name="event_filter">
                                <option value="">Semua Event</option>
                                @foreach($eventNames as $event)
                                    <option value="{{ $event }}" {{ $request->input('event_filter') == $event ? 'selected' : '' }}>{{ ucfirst($event) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="causer_filter" class="form-label">Dilakukan Oleh</label>
                            <select class="form-select" id="causer_filter" name="causer_filter">
                                <option value="">Semua User</option>
                                @foreach($users as $id => $name)
                                    <option value="{{ $id }}" {{ $request->input('causer_filter') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="date_from_filter" class="form-label">Dari Tanggal</label>
                            <input type="date" class="form-control" id="date_from_filter" name="date_from_filter" value="{{ $request->input('date_from_filter') }}">
                        </div>

                        <div class="col-md-2">
                            <label for="date_to_filter" class="form-label">Sampai Tanggal</label>
                            <input type="date" class="form-control" id="date_to_filter" name="date_to_filter" value="{{ $request->input('date_to_filter') }}">
                        </div>

                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('activity-logs.index') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID Log</th>
                                <th>Deskripsi</th>
                                <th>Subjek</th>
                                <th>Event</th>
                                <th>Dilakukan Oleh</th>
                                <th>Properti Perubahan</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($activities as $activity)
                                <tr>
                                    <td>{{ $activity->id }}</td>
                                    <td>{{ $activity->description }}</td>
                                    <td>
                                        @if ($activity->subject)
                                            {{-- Anda mungkin ingin link ke detail Task jika ada --}}
                                            Task: {{ $activity->subject->id_job ?? $activity->subject_id }}
                                            ({{ Str::afterLast($activity->subject_type, '\\') }})
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td><span class="badge bg-info text-dark">{{ $activity->event }}</span></td>
                                    <td>{{ $activity->causer->name ?? 'Sistem' }}</td>
                                    <td>
                                        @if ($activity->properties && ($activity->properties->has('old') || $activity->properties->has('attributes')))
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#propertiesModal{{ $activity->id }}">
                                                Lihat
                                            </button>
                                            <!-- Modal -->
                                            <div class="modal fade" id="propertiesModal{{ $activity->id }}" tabindex="-1" aria-labelledby="propertiesModalLabel{{ $activity->id }}" aria-hidden="true">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="propertiesModalLabel{{ $activity->id }}">Detail Perubahan Log #{{ $activity->id }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            @if($activity->properties->has('old'))
                                                                <h6>Sebelum:</h6>
                                                                <pre class="bg-light p-2 rounded"><code>{{ json_encode($activity->properties['old'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                                            @endif
                                                            @if($activity->properties->has('attributes'))
                                                                <h6>Sesudah:</h6>
                                                                <pre class="bg-light p-2 rounded"><code>{{ json_encode($activity->properties['attributes'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
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
                                    <td colspan="7" class="text-center">Tidak ada log aktivitas ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $activities->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>