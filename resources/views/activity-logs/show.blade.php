<x-app-layout>
    @section('title', 'Log Aktivitas untuk Job ' . $job->id_job)

    <div class="container-fluid mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Log Aktivitas untuk Job: {{ $job->id_job }}</h3>
                 <div class="card-tools">
                    <a href="{{ route('activity-logs.index') }}" class="btn btn-sm btn-outline-secondary">Kembali ke Semua Log</a>
                    <a href="{{ route('jobs.index') }}" class="btn btn-sm btn-outline-primary">Kembali ke Kanban</a>
                 </div>
            </div>
            <div class="card-body">
                @if($activities->isEmpty())
                    <p class="text-center text-gray-500">Tidak ada log aktivitas untuk job ini.</p>
                @else
                    <div class="list-group">
                        @foreach ($activities as $activity)
                            <div class="list-group-item list-group-item-action flex-column align-items-start mb-2 border rounded">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1">{{ $activity->description }}</h5>
                                    <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                                </div>
                                <p class="mb-1">
                                    <span class="badge bg-info text-dark me-1">{{ $activity->event }}</span>
                                    Oleh: <strong>{{ $activity->causer->name ?? 'Sistem' }}</strong>
                                    pada {{ $activity->created_at->format('d M Y, H:i:s') }}
                                </p>
                                @if ($activity->properties && ($activity->properties->has('old') || $activity->properties->has('attributes')))
                                    <small>
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#propertiesModal{{ $activity->id }}">
                                            Lihat Detail Perubahan
                                        </a>
                                    </small>
                                    <!-- Modal -->
                                    <div class="modal fade" id="propertiesModal{{ $activity->id }}" tabindex="-1" aria-labelledby="propertiesModalLabel{{ $activity->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="propertiesModalLabel{{ $activity->id }}">Detail Perubahan Log #{{ $activity->id }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    @if($activity->properties->has('old'))
                                                        <h6>Data Lama (Sebelum):</h6>
                                                        <pre class="bg-light p-2 rounded"><code>{{ json_encode($activity->properties->get('old'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                                    @endif
                                                    @if($activity->properties->has('attributes'))
                                                        <h6>Data Baru (Sesudah):</h6>
                                                        <pre class="bg-light p-2 rounded"><code>{{ json_encode($activity->properties->get('attributes'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3">
                        {{ $activities->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>