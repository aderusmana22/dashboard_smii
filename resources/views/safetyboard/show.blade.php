<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detail Laporan #{{ $laporan->nomor_form ?? $laporan->id }}
            </h2>
            <a href="{{ route('accidents-report.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                &larr; Kembali ke Daftar
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Session Alerts -->
            @if (session('success'))
                <div class="mb-4 rounded-lg bg-green-100 p-4 text-sm text-green-800" role="alert">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-lg bg-red-100 p-4 text-sm text-red-800" role="alert">{{ session('error') }}</div>
            @endif

            <!-- ================================================== -->
            <!--      BAGIAN AKSI (MODAL DENGAN ALPINE.JS)        -->
            <!-- ================================================== -->
            <div x-data="{ rejectModalOpen: false }">
                <!-- Tombol Aksi untuk Approver -->
                @if ($laporan->approvalStatus?->current_approver_id === Auth::id())
                <div class="bg-white shadow-sm sm:rounded-lg border-l-4 border-indigo-500">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900">Tindakan Persetujuan Diperlukan</h3>
                        <p class="mt-1 text-gray-600">Laporan ini menunggu persetujuan Anda sebagai <strong>{{ Str::title(str_replace(['_', '_id'], ' ', $currentApproverField)) }}</strong>.</p>
                        <div class="mt-4 flex gap-x-3">
                            <form action="{{ route('accidents-report.approve', $laporan) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menyetujui laporan ini?');">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Setujui & Lanjutkan
                                </button>
                            </form>
                            <button @click="rejectModalOpen = true" type="button" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Tolak
                            </button>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Modal Penolakan (Menggunakan Alpine.js) -->
                <div x-show="rejectModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;">
                    <div @click.away="rejectModalOpen = false" class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-900" id="rejectModalLabel">Tolak Laporan Kecelakaan</h3>
                            <form action="{{ route('accidents-report.reject', $laporan) }}" method="POST" class="mt-4">
                                @csrf
                                <div>
                                    <label for="rejection_reason" class="block text-sm font-medium text-gray-700">Alasan Penolakan (Wajib diisi)</label>
                                    <textarea id="rejection_reason" name="rejection_reason" rows="4" required minlength="10" placeholder="Jelaskan secara detail mengapa laporan ini ditolak..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                </div>
                                <div class="mt-6 flex justify-end gap-x-3">
                                    <button @click="rejectModalOpen = false" type="button" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                        Batal
                                    </button>
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Tolak Laporan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi untuk Pembuat Laporan (Revisi) -->
            @if ($laporan->approvalStatus?->status === 'rejected' && $laporan->pembuat_laporan_id === Auth::id())
            <div class="bg-white shadow-sm sm:rounded-lg border-l-4 border-red-500">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-900">Tindakan Revisi Diperlukan</h3>
                    <div class="mt-2 text-gray-600">
                        <p>Laporan ini ditolak dengan alasan:</p>
                        <blockquote class="mt-2 border-l-4 border-gray-300 bg-gray-50 p-4">
                            <p class="italic">"{{ $laporan->approvalStatus->rejection_reason }}"</p>
                        </blockquote>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('accidents-report.revise', $laporan) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Revisi dan Kirim Ulang Laporan
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- ================================================== -->
            <!--      KARTU INFORMASI UTAMA LAPORAN               -->
            <!-- ================================================== -->
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-4">
                    <h3 class="text-lg font-bold text-gray-900 border-b pb-3">Informasi Laporan</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                        <!-- Informasi Revisi -->
                        @if ($laporan->revision_number > 0)
                        <div class="md:col-span-2">
                            <strong class="text-gray-600">Status Revisi:</strong>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Revisi ke-{{ $laporan->revision_number }}
                            </span>
                            @if($laporan->revisedFrom)
                            (Revisi dari <a href="{{ route('accidents-report.show', $laporan->revised_from_id) }}" class="text-indigo-600 hover:underline">Laporan #{{$laporan->revisedFrom->nomor_form}}</a>)
                            @endif
                        </div>
                        @endif

                        <div><strong class="text-gray-600">Nomor Form:</strong> {{ $laporan->nomor_form ?? '-' }}</div>
                        <div><strong class="text-gray-600">Tanggal Laporan:</strong> {{ \Carbon\Carbon::parse($laporan->date)->format('d F Y') }}</div>
                        <div>
                            <strong class="text-gray-600">Status Saat Ini:</strong>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                @if($laporan->approvalStatus?->status == 'approved') bg-green-100 text-green-800
                                @elseif($laporan->approvalStatus?->status == 'rejected') bg-red-100 text-red-800
                                @elseif($laporan->approvalStatus?->status == 'revised') bg-gray-100 text-gray-800
                                @elseif(str_starts_with($laporan->approvalStatus?->status ?? '', 'pending_')) bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ Str::title(str_replace('_', ' ', $laporan->approvalStatus?->status ?? 'Draft')) }}
                            </span>
                        </div>
                        <div><strong class="text-gray-600">Pembuat Laporan:</strong> {{ $laporan->pembuatLaporan->name ?? 'N/A' }}</div>
                    </div>

                    <div class="border-t pt-4 space-y-2">
                        <h4 class="text-md font-bold text-gray-800">Detail Insiden</h4>
                        <p class="text-sm"><strong class="text-gray-600">Tanggal & Jam Kecelakaan:</strong> {{ \Carbon\Carbon::parse($laporan->waktu_kecelakaan)->format('d F Y, H:i') }}</p>
                        <p class="text-sm"><strong class="text-gray-600">Lokasi:</strong> {{ $laporan->lokasi_kecelakaan }}</p>
                        <div>
                            <p class="text-sm font-semibold text-gray-600">Uraian Kejadian:</p>
                            <div class="prose prose-sm max-w-none mt-1 p-3 bg-gray-50 rounded-md border">
                                {!! $laporan->uraian_kejadian !!}
                            </div>
                        </div>
                    </div>
                    <!-- Tampilkan semua field lainnya dari laporan di sini -->
                </div>
            </div>

            <!-- ================================================== -->
            <!--      KARTU RIWAYAT PERSETUJUAN                   -->
            <!-- ================================================== -->
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-900 border-b pb-3">Riwayat Proses</h3>
                    @if($laporan->approvalHistories->isEmpty())
                        <p class="mt-4 text-sm text-gray-500">Belum ada riwayat proses.</p>
                    @else
                    <div class="mt-4 flow-root">
                        <ul role="list" class="-mb-8">
                            @foreach ($laporan->approvalHistories->sortBy('created_at') as $history)
                            <li>
                                <div class="relative pb-8">
                                    @if(!$loop->last)
                                    <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    @endif
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white
                                                @if($history->action == 'created') bg-blue-500
                                                @elseif($history->action == 'approved') bg-green-500
                                                @elseif($history->action == 'rejected') bg-red-500
                                                @else bg-gray-500
                                                @endif">
                                                <!-- Heroicon name: solid/user -->
                                                <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                            <div>
                                                <p class="text-sm text-gray-500">
                                                    <span class="font-medium text-gray-900">{{ $history->user->name ?? 'Sistem' }}</span>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                        @if($history->action == 'created') bg-blue-100 text-blue-800
                                                        @elseif($history->action == 'approved') bg-green-100 text-green-800
                                                        @elseif($history->action == 'rejected') bg-red-100 text-red-800
                                                        @else bg-gray-100 text-gray-800
                                                        @endif">{{ Str::title($history->action) }}</span>
                                                </p>
                                                <p class="mt-1 text-sm italic text-gray-700">"{{ $history->notes }}"</p>
                                            </div>
                                            <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                                <time title="{{ \Carbon\Carbon::parse($history->created_at)->format('d M Y H:i:s') }}">{{ \Carbon\Carbon::parse($history->created_at)->diffForHumans() }}</time>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>