<x-app-layout>
    @section('title', 'Detail Laporan Kecelakaan')

    <div class="py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold">Detail Laporan #{{ $laporan->nomor_form ?? $laporan->id }}</h2>
                <a href="{{ route('accidents-report.index') }}" class="btn btn-secondary">
                    &larr; Kembali ke Daftar
                </a>
            </div>

            @if (session('success'))
                <div class="alert alert-success" role="alert">
                    {{ session('success') }}
                </div>
            @endif
             @if (session('error'))
                <div class="alert alert-danger" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Bagian Aksi Persetujuan --}}
            @if ($laporan->current_approver_id === Auth::id())
            <div class="card shadow-sm mb-4">
                <div class="card-header fw-bold bg-primary text-white">
                    Tindakan Persetujuan Diperlukan
                </div>
                <div class="card-body">
                    <p>Laporan ini menunggu persetujuan Anda sebagai <strong>{{ Str::title(str_replace(['_', 'pending', 'approval'], ' ', $laporan->status)) }}</strong>.</p>
                    <div class="d-flex gap-2">
                        {{-- Tombol Approve --}}
                        <form action="{{ route('accidents-report.approve', $laporan->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menyetujui laporan ini?');">
                            @csrf
                            <button type="submit" class="btn btn-success">Setujui & Lanjutkan</button>
                        </form>

                        {{-- Tombol Reject (dengan modal) --}}
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            Tolak
                        </button>
                    </div>
                </div>
            </div>
            @endif

            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h4 class="card-title border-bottom pb-2 mb-3">Informasi Laporan</h4>
                    <div class="row">
                        <div class="col-md-6 mb-2"><strong>Nomor Form:</strong> {{ $laporan->nomor_form ?? '-' }}</div>
                        <div class="col-md-6 mb-2"><strong>Tanggal Laporan:</strong> {{ \Carbon\Carbon::parse($laporan->date)->format('d F Y') }}</div>
                        <div class="col-md-6 mb-2"><strong>Status Saat Ini:</strong> 
                            <span class="badge 
                                @if($laporan->status == 'approved') bg-success
                                @elseif($laporan->status == 'rejected') bg-danger
                                @elseif(str_starts_with($laporan->status, 'pending_')) bg-warning text-dark
                                @else bg-secondary
                                @endif">
                                {{ Str::title(str_replace('_', ' ', $laporan->status)) }}
                            </span>
                        </div>
                        <div class="col-md-6 mb-2"><strong>Pembuat Laporan:</strong> {{ $laporan->creator->name ?? 'N/A' }}</div>
                        @if($laporan->status == 'rejected')
                        <div class="col-12 mb-2 text-danger"><strong>Alasan Penolakan:</strong> {{ $laporan->rejection_reason }}</div>
                        @endif
                    </div>
                    
                    <h5 class="mt-4">Detail Insiden</h5>
                    <p><strong>Tanggal & Jam Kecelakaan:</strong> {{ \Carbon\Carbon::parse($laporan->waktu_kecelakaan)->format('d F Y, H:i') }}</p>
                    <p><strong>Lokasi:</strong> {{ $laporan->lokasi_kecelakaan }}</p>
                    <p><strong>Uraian Kejadian:</strong> {!! $laporan->uraian_kejadian !!}</p>
                    
                    {{-- Tampilkan semua field lainnya dari laporan di sini --}}
                </div>
            </div>

            {{-- Riwayat Persetujuan --}}
            <div class="card shadow-sm">
                <div class="card-header fw-bold">
                    Riwayat Proses
                </div>
                <div class="card-body">
                    @if($laporan->approvalHistories->isEmpty())
                        <p class="text-muted">Belum ada riwayat proses.</p>
                    @else
                    <ul class="list-group list-group-flush">
                        @foreach ($laporan->approvalHistories as $history)
                            <li class="list-group-item px-0">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">
                                        <span class="badge bg-info me-2">{{ Str::title($history->action) }}</span>
                                        {{ $history->user->name ?? 'Sistem' }}
                                    </h6>
                                    <small>{{ \Carbon\Carbon::parse($history->created_at)->diffForHumans() }}</small>
                                </div>
                                <p class="mb-1 fst-italic">"{{ $history->notes }}"</p>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($history->created_at)->format('d M Y H:i:s') }}</small>
                            </li>
                        @endforeach
                    </ul>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <!-- Modal Penolakan -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">Tolak Laporan Kecelakaan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('accidents-report.reject', $laporan->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="rejection_reason" class="form-label">Alasan Penolakan (Wajib diisi)</label>
                            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="4" required minlength="10"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Tolak Laporan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>