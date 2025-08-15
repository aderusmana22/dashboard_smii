@section('title', 'Laporan Job Marsho') {{-- Judul halaman --}}

<x-app-layout>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Laporan Job Marsho</h3>
                    <div class="card-tools">
                        {{-- Tombol ini akan mengarah ke route ekspor --}}
                        <a href="{{ route('reports.marsho-jobs.export') }}" class="btn btn-success">
                            <i class="fas fa-file-excel"></i> Export ke Excel
                        </a>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID Job</th>
                                    <th>Status</th>
                                    <th>Area</th>
                                    <th>List Pekerjaan</th>
                                    <th>Dept. Terakhir</th>
                                    <th>Diajukan Oleh</th>
                                    <th>Tgl Mulai</th>
                                    <th>Tgl Selesai</th>
                                    <th>Ditutup Oleh</th>
                                    <th>Tgl Ditutup</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Gunakan @forelse untuk menangani kasus jika tidak ada data --}}
                                @forelse ($jobs as $job)
                                    <tr>
                                        <td>{{ $job->id_job }}</td>
                                        <td><span class="badge bg-primary">{{ ucfirst($job->status) }}</span></td>
                                        <td>{{ $job->area->name ?? 'N/A' }}</td>
                                        <td>{{ Str::limit($job->list_job, 50) }}</td>
                                        <td>{{ $job->latestRoute->toDepartment->department_name ?? 'N/A' }}</td>
                                        <td>{{ $job->pengaju->name ?? 'N/A' }}</td>
                                        <td>{{ $job->tanggal_job_mulai ? $job->tanggal_job_mulai->format('d-m-Y H:i') : '' }}</td>
                                        <td>{{ $job->tanggal_job_selesai ? $job->tanggal_job_selesai->format('d-m-Y H:i') : '' }}</td>
                                        <td>{{ $job->penutup->name ?? 'N/A' }}</td>
                                        <td>{{ $job->closed_at ? $job->closed_at->format('d-m-Y H:i') : '' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">Tidak ada data untuk ditampilkan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
    </div>
</div>
</x-app-layout>
