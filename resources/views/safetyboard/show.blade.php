<x-app-layout>
    {{-- Alpine.js diperlukan untuk modal penolakan agar interaktif --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detail Laporan Investigasi #{{ $laporan->nomor_form ?? $laporan->id }}
            </h2>
            <div class="flex items-center gap-2 print:hidden">
                <a href="{{ route('accidents-report.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    &larr; Kembali
                </a>
                <button onclick="window.print()"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Cetak Laporan
                </button>
            </div>
        </div>
    </x-slot>

    <style>
        /* CSS untuk mensimulasikan checkbox dari format PDF */
        .pdf-check::before {
            content: '(';
        }

        .pdf-check::after {
            content: ')';
        }

        .pdf-check-checked::before {
            content: '(✓)';
        }

        /* Aturan khusus untuk mode cetak */
        @media print {

            body>nav,
            body>header,
            .print\:hidden {
                display: none !important;
            }

            body,
            #app {
                margin: 0 !important;
                padding: 0 !important;
                background-color: white !important;
            }

            .printable-area {
                margin: 0 !important;
                padding: 1.5cm !important;
                box-shadow: none !important;
                border: none !important;
                width: 100% !important;
                max-width: 100% !important;
            }
        }

        /* ATURAN FINAL UNTUK GAMBAR DARI EDITOR */
        .prose img[src*="/storage/editor-uploads/"] {
            max-height: 200px !important;
            width: auto !important;
            height: auto !important;
            object-fit: contain !important;
            margin-left: auto !important;
            margin-right: auto !important;
            display: block !important;
        }
    </style>

    <div class="py-12 print:py-0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- ================================================== -->
            <!--   BAGIAN INTERAKTIF (HANYA TAMPIL DI LAYAR)       -->
            <!-- ================================================== -->
            <div class="print:hidden space-y-6">
                @if (session('success'))
                    <div class="rounded-lg bg-green-100 p-4 text-sm text-green-800" role="alert">{{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="rounded-lg bg-red-100 p-4 text-sm text-red-800" role="alert">{{ session('error') }}</div>
                @endif
                <div x-data="{ rejectModalOpen: false }">
                    @if ($laporan->approvalStatus?->current_approver_id === Auth::id())
                        <div class="bg-white shadow-sm sm:rounded-lg border-l-4 border-indigo-500">
                            <div class="p-6">
                                <h3 class="text-lg font-bold text-gray-900">Tindakan Persetujuan Diperlukan</h3>
                                <p class="mt-1 text-gray-600">Laporan ini menunggu persetujuan Anda sebagai
                                    <strong>{{ Str::title(str_replace(['_', '_id'], ' ', $currentApproverField)) }}</strong>.
                                </p>
                                <div class="mt-4 flex gap-x-3">
                                    <form action="{{ route('accidents-report.approve', $laporan) }}" method="POST"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menyetujui laporan ini?');">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">Setujui</button>
                                    </form>
                                    <button @click="rejectModalOpen = true" type="button"
                                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">Tolak</button>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div x-show="rejectModalOpen" x-transition
                        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
                        style="display: none;">
                        <div @click.away="rejectModalOpen = false"
                            class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4">
                            <form action="{{ route('accidents-report.reject', $laporan) }}" method="POST" class="p-6">
                                @csrf
                                <h3 class="text-lg font-bold text-gray-900">Tolak Laporan Kecelakaan</h3>
                                <div class="mt-4">
                                    <label for="rejection_reason" class="block text-sm font-medium text-gray-700">Alasan
                                        Penolakan</label>
                                    <textarea id="rejection_reason" name="rejection_reason" rows="4" required
                                        minlength="10"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                                </div>
                                <div class="mt-6 flex justify-end gap-x-3">
                                    <button @click="rejectModalOpen = false" type="button"
                                        class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase hover:bg-gray-50">Batal</button>
                                    <button type="submit"
                                        class="px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase hover:bg-red-700">Tolak
                                        Laporan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @if ($laporan->approvalStatus?->status === 'rejected' && $laporan->pembuat_laporan_id === Auth::id())
                    <div class="bg-white shadow-sm sm:rounded-lg border-l-4 border-red-500">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-900">Tindakan Revisi Diperlukan</h3>
                            <div class="mt-2 text-gray-600">
                                <p>Laporan ditolak dengan alasan:</p>
                                <blockquote class="mt-2 border-l-4 bg-gray-50 p-4 italic">
                                    "{{ $laporan->approvalStatus->rejection_reason }}"</blockquote>
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('accidents-report.revise', $laporan) }}"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase hover:bg-indigo-700">Revisi
                                    Laporan</a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- ================================================== -->
            <!--   KONTEN DOKUMEN DENGAN STRUKTUR BARU              -->
            <!-- ================================================== -->
            <div class="printable-area bg-white shadow-lg p-8 sm:rounded-lg">
                @php
                    function renderCheck($value, $expected)
                    {
                        echo ($value === $expected) ? '<span class="pdf-check-checked mr-2"></span>' : '<span class="pdf-check mr-2"></span>';
                    }
                @endphp

                <!-- ============================================== -->
                <!--   BLOK 1: HEADER / KOP SURAT                   -->
                <!-- ============================================== -->
                <div class="grid grid-cols-12 border-2 border-black">
                    <div class="col-span-3 flex flex-col justify-center items-center p-2 text-center">
                        <img src="{{ asset('assets/images/logohitam.png') }}" alt="Sinar Meadow Logo" class="h-16">
                        <p class="font-bold text-xs mt-2 leading-tight">PT SINAR MEADOW<br>INTERNATIONAL INDONESIA</p>
                    </div>
                    <div class="col-span-6 border-x-2 border-black flex flex-col justify-center text-center font-bold">
                        <div class="p-1 border-b-2 border-black text-lg">FORM</div>
                        <div class="p-2 text-xl leading-tight">LAPORAN INVESTIGASI KECELAKAAN KERJA</div>
                    </div>
                    <div class="col-span-3 text-xs p-2">
                        <div class="grid grid-cols-3"><span>No</span> <span class="col-span-2">:
                                {{ $laporan->nomor_form ?? 'F/S9.9-01A' }}</span></div>
                        <div class="grid grid-cols-3"><span>Revision</span> <span class="col-span-2">:
                                {{ $laporan->revision_number ?? 0 }}</span></div>
                        <div class="grid grid-cols-3"><span>Date</span> <span class="col-span-2">:
                                {{ optional($laporan->date)->format('d F Y') ?? '1 Desember 2015' }}</span></div>
                        <div class="grid grid-cols-3"><span>Page</span> <span class="col-span-2">: 1 of 3</span></div>
                    </div>
                </div>

                <!-- =========================================================== -->
                <!--   BLOK 2: BADAN LAPORAN (DALAM BINGKAI YANG MENYATU)      -->
                <!-- =========================================================== -->
                <div class="border-x-2 border-b-2 border-black">
                    <div class="p-6">
                        <div class="text-sm space-y-4">

                            <!-- 1. Kategori Kecelakaan -->
                            <div>
                                <div class="flex">
                                    <div class="w-52 font-bold">1. Kategori Kecelakaan</div>
                                    <div class="flex-1"><span class="pr-2">:</span></div>
                                </div>
                                <div class="pl-5 pt-1 space-y-1">
                                    <div class="flex items-center">
                                        <span class="font-mono">(<span class="inline-flex justify-center w-4">@if($laporan->kategori_kecelakaan == 'Kerja')√@endif</span>)</span>
                                        <span class="ml-2">Kerja</span>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="font-mono">(<span class="inline-flex justify-center w-4">@if($laporan->kategori_kecelakaan == 'Lalu Lintas')√@endif</span>)</span>
                                        <span class="ml-2">Lalu Lintas</span>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="font-mono">(<span class="inline-flex justify-center w-4">@if($laporan->kategori_kecelakaan == 'Kebakaran')√@endif</span>)</span>
                                        <span class="ml-2">Kebakaran</span>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="font-mono">(<span class="inline-flex justify-center w-4">@if($laporan->kategori_kecelakaan == 'Lain-lain')√@endif</span>)</span>
                                        <span class="ml-2">Lain-lain</span>
                                    </div>
                                </div>
                            </div>

                            <!-- 2. Tanggal & Jam Kecelakaan -->
                            <div class="space-y-1">
                                <div class="flex">
                                    <div class="w-52 font-bold">2. Tanggal Kecelakaan</div>
                                    <div class="flex-1"><span class="pr-2">:</span>{{ optional($laporan->waktu_kecelakaan)->format('d-M-y') ?? '-' }}</div>
                                </div>
                                <div class="flex">
                                    <div class="w-52 pl-5">Jam Kecelakaan</div>
                                    <div class="flex-1"><span class="pr-2">:</span>{{ optional($laporan->waktu_kecelakaan)->format('H.i') ?? '-' }}</div>
                                </div>
                            </div>

                            <!-- 3. Lokasi Kecelakaan -->
                            <div class="space-y-1">
                                <div class="flex">
                                    <div class="w-52 font-bold">3. Lokasi Kecelakaan</div>
                                    <div class="flex-1"><span class="pr-2">:</span>{{ $laporan->lokasi_kecelakaan ?? '-' }}</div>
                                </div>
                                <div class="flex">
                                    <div class="w-52 pl-5">Seksi / Departemen</div>
                                    <div class="flex-1"><span class="pr-2">:</span>{{ $laporan->departemen ?? '-' }}</div>
                                </div>
                            </div>

                            <!-- 4. Nama Korban -->
                            <div class="space-y-1">
                                <div class="flex">
                                    <div class="w-52 font-bold">4. Nama Korban</div>
                                    <div class="flex-1"><span class="pr-2">:</span>{{ $laporan->nama_korban ?? '-' }}</div>
                                </div>
                                <div class="flex">
                                    <div class="w-52 pl-5">NIK</div>
                                    <div class="flex-1"><span class="pr-2">:</span>{{ $laporan->nik ?? '-' }}</div>
                                </div>
                                <div class="flex">
                                    <div class="w-52 pl-5">Usia</div>
                                    <div class="flex-1"><span class="pr-2">:</span>{{ $laporan->usia ?? '-' }}</div>
                                </div>
                                <div class="flex">
                                    <div class="w-52 pl-5">Masa Kerja</div>
                                    <div class="flex-1"><span class="pr-2">:</span>{{ $laporan->masa_kerja ?? '-' }}</div>
                                </div>
                                <div class="flex">
                                    <div class="w-52 pl-5">Jabatan</div>
                                    <div class="flex-1"><span class="pr-2">:</span>{{ $laporan->jabatan ?? '-' }}</div>
                                </div>
                            </div>

                            <!-- 5. Diberi Pertolongan (P3K) -->
                            <div class="space-y-1">
                                <div class="flex">
                                    <div class="w-52 font-bold">5. Diberi Pertolongan ( P3K)</div>
                                    <div class="flex-1"><span class="pr-2">:</span></div>
                                </div>
                                <div class="pl-5 pt-1 space-y-1">
                                    <div class="flex items-center">
                                        <span class="font-mono">(<span class="inline-flex justify-center w-4">@if($laporan->pertolongan == 'Di Tempat Kejadian')√@endif</span>)</span>
                                        <span class="ml-2">Di Tempat Kejadian</span>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="font-mono">(<span class="inline-flex justify-center w-4">@if($laporan->pertolongan == 'Di Klinik')√@endif</span>)</span>
                                        <span class="ml-2">Di Klinik</span>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="font-mono">(<span class="inline-flex justify-center w-4">@if($laporan->pertolongan == 'Di Rumah Sakit')√@endif</span>)</span>
                                        <span class="ml-2">Di Rumah Sakit</span>
                                    </div>
                                </div>
                                <div class="flex pt-1">
                                    <div class="w-52 pl-5">P3K dilakukan Oleh</div>
                                    <div class="flex-1"><span class="pr-2">:</span>{{ $laporan->p3k_oleh ?? '-' }}</div>
                                </div>
                                <div class="flex">
                                    <div class="w-52 pl-5">Jam</div>
                                    <div class="flex-1"><span class="pr-2">:</span>{{ $laporan->jam_p3k ? \Carbon\Carbon::parse($laporan->jam_p3k)->format('H.i') : '-' }}</div>
                                </div>
                            </div>

                            <!-- 6. Akibat Kecelakaan -->
                            <div>
                                <div class="flex">
                                    <div class="w-52 font-bold">6. Akibat Kecelakaan</div>
                                    <div class="flex-1"><span class="pr-2">:</span></div>
                                </div>
                                <div class="pl-5 pt-1 space-y-1">
                                    <div class="flex items-center">
                                        <span class="font-mono">(<span class="inline-flex justify-center w-4">@if($laporan->akibat_kecelakaan == 'Sementara Total tak mampu bekerja')√@endif</span>)</span>
                                        <span class="ml-2">Sementara Total tak mampu bekerja</span>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="font-mono">(<span class="inline-flex justify-center w-4">@if($laporan->akibat_kecelakaan == 'Sementara Sebagian tak mampu bekerja')√@endif</span>)</span>
                                        <span class="ml-2">Sementara Sebagian tak mampu bekerja</span>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="font-mono">(<span class="inline-flex justify-center w-4">@if($laporan->akibat_kecelakaan == 'Tetap Sebagian tak mampu bekerja')√@endif</span>)</span>
                                        <span class="ml-2">Tetap Sebagian tak mampu bekerja</span>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="font-mono">(<span class="inline-flex justify-center w-4">@if($laporan->akibat_kecelakaan == 'Tetap Total tak mampu bekerja')√@endif</span>)</span>
                                        <span class="ml-2">Tetap Total tak mampu bekerja</span>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="font-mono">(<span class="inline-flex justify-center w-4">@if($laporan->akibat_kecelakaan == 'Meninggal')√@endif</span>)</span>
                                        <span class="ml-2">Meninggal</span>
                                    </div>
                                </div>
                            </div>

                            <!-- 7. Jumlah waktu hilang -->
                            <div class="flex">
                                <div class="w-52 font-bold">7. Jumlah waktu hilang</div>
                                <div class="flex-1"><span class="pr-2">:</span>{{ $laporan->waktu_hilang ?? '-' }} hari</div>
                            </div>

                            <!-- 8. Biaya Perawatan -->
                            <div class="flex">
                                <div class="w-52 font-bold">8. Biaya perawatan</div>
                                <div class="flex flex-1 items-baseline">
                                    <div class="pr-2">:</div>
                                    <div class="flex-1">
                                        <div class="inline-grid grid-cols-[auto,auto,1fr] gap-x-2">
                                            @forelse ($laporan->biayaPerawatan as $biaya)
                                                <div class="text-right">Rp</div>
                                                <div class="text-right">{{ number_format($biaya->harga, 0, ',', '.') }}</div>
                                                <div>({{ $biaya->kategori }})</div>
                                            @empty
                                                <div class="col-span-3">-</div>
                                            @endforelse
                                            @if ($laporan->biayaPerawatan->count() > 1)
                                                <div class="col-span-3 !my-1"><hr class="border-gray-400"></div>
                                                <div class="font-bold text-right">Total Rp</div>
                                                <div class="font-bold text-right">{{ number_format($laporan->biayaPerawatan->sum('harga'), 0, ',', '.') }}</div>
                                                <div></div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 9. Alat Pelindung Diri (APD) -->
                            <div class="flex">
                                <div class="w-52 font-bold">9. Alat Pelindung Diri (APD)</div>
                                <div class="flex-1 grid grid-cols-2 gap-x-8">
                                    <div>
                                        @php
                                            $apds = $laporan->apd_data ?? [];
                                            $apd_labels = ['sarung_tangan' => 'Sarung Tangan', 'sepatu' => 'Sepatu Keselamatan', 'helm' => 'Helm', 'masker' => 'Masker', 'kacamata' => 'Kaca mata', 'celemek' => 'Celemek', 'kedok' => 'Kedok pelindung Muka', 'hairnet' => 'Hair Net'];
                                        @endphp
                                        @foreach ($apd_labels as $key => $label)
                                            <div class="flex items-center">
                                                <span class="font-mono">(<span class="inline-flex justify-center w-4">@if(isset($apds[$key]))√@endif</span>)</span>
                                                <span class="ml-2">{{ $label }}</span>
                                                @if ($key === 'sarung_tangan' && !empty($apds[$key]['keterangan']))
                                                    <span class="text-gray-600 ml-1">({{ Str::limit($apds[$key]['keterangan'], 20) }})</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="border-l pl-4">
                                        <strong>DIPAKAI</strong><br>
                                        @foreach ($apd_labels as $key => $label)
                                            <div class="grid grid-cols-12 items-center">
                                                <div class="col-span-6 flex items-center">
                                                    <span class="font-mono">(<span class="inline-flex justify-center w-4">@if(isset($apds[$key]['dipakai']) && $apds[$key]['dipakai'] == 'ya')√@endif</span>)</span>
                                                    <span class="ml-2">Ya</span>
                                                </div>
                                                <div class="col-span-6 flex items-center">
                                                    <span class="font-mono">(<span class="inline-flex justify-center w-4">@if(isset($apds[$key]['dipakai']) && $apds[$key]['dipakai'] == 'tidak')√@endif</span>)</span>
                                                    <span class="ml-2">Tidak</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- 10. Sebab Kecelakaan -->
                            <div>
                                <div class="flex">
                                    <div class="w-52 font-bold">10. Sebab Kecelakaan</div>
                                    <div class="flex-1"><span class="pr-2">:</span></div>
                                </div>
                                <div class="pl-5 pt-1 space-y-1">
                                    @php
                                        $sebabKecelakaanOptions = ['Tindakan berbahaya Orang Lain', 'Tindakan berbahaya diri sendiri.', 'Keadaan berbahaya'];
                                    @endphp
                                    @foreach($sebabKecelakaanOptions as $option)
                                    <div class="flex items-center">
                                        <span class="font-mono">(<span class="inline-flex justify-center w-4">@if($laporan->sebab_kecelakaan == $option)√@endif</span>)</span>
                                        <span class="ml-2">{{ $option }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- 11. Uraian Kejadian -->
                            <div class="pt-4">
                                <strong class="font-bold">11. Uraian Kejadian:</strong>
                                <div class="prose prose-sm max-w-none mt-1">{!! $laporan->uraian_kejadian !!}</div>
                            </div>

                            <!-- 12, 13, 14 -->
                            <div class="space-y-1">
                                <div class="flex">
                                    <div class="w-52 font-bold">12. Kategori Dampak</div>
                                    <div class="flex-1"><span class="pr-2">:</span>{{ $laporan->kategori_dampak ?? '-' }}</div>
                                </div>
                                <div class="flex">
                                    <div class="w-52 font-bold">13. Tipe Kecelakaan</div>
                                    <div class="flex-1"><span class="pr-2">:</span>{{ $laporan->tipe_kecelakaan ?? '-' }}</div>
                                </div>
                                <div class="flex">
                                    <div class="w-52 font-bold">14. Bagian badan yang terluka</div>
                                    <div class="flex-1"><span class="pr-2">:</span>{{ $laporan->bagian_terluka ?? '-' }}</div>
                                </div>
                            </div>

                            <!-- 15. Analisa Sebab Utama (REVISED) -->
                            <div class="pt-4 border-t-2 border-black">
                                @php
                                    $tindakanBerbahaya = [ 'Mengoperasikan tanpa wewenang', 'Mengoperasikan dengan kecepatan berlebihan', 'Alat penyelamat tidak berfungsi', 'Menggunakan alat yang rusak', 'Menggunakan alat / bahan tidak sesuai fungsinya.', 'Menggunakan alat secara tidak benar', 'Menggunakan alat pelindung diri tidak sesuai fungsinya.', 'Tidak menggunakan Alat Pelindung Diri yang diwajibkan', 'Pemuatan / pembongkaran / penempatan yang tidak sesuai', 'Salah mengangkat', 'Mengambil posisi salah', 'Menservis alat yang berputar', 'Bersendau gurau', 'Mengantuk, melamun' ];
                                    $keadaanBerbahaya = [ 'Alat penyelamat yang kurang sempurna', 'Alat, mesin, atau bahan rusak', 'Sistem pemberi peringatan yang kurang sempurna', 'Bahaya kebakaran & peledakan', 'House keeping di bawah standard', 'Kondisi udara yang berbahaya terhadap gas, debu, dan uap', 'Kebisingan tinggi', 'Paparan / tekanan panas', 'Pencahayaan kurang' ];
                                    $sebabUtamaA = collect($laporan->sebab_utama)->firstWhere('kategori', 'A');
                                    $sebabUtamaB = collect($laporan->sebab_utama)->firstWhere('kategori', 'B');
                                @endphp
                                <strong class="font-bold">15. Analisa Sebab Utama Kecelakaan:</strong>
                                <div class="pl-5 pt-1 space-y-2">
                                    <div>
                                        <p class="font-semibold">A. Tindakan Berbahaya (Unsafe Human Act):</p>
                                        <div class="pl-4 space-y-1">
                                            @foreach ($tindakanBerbahaya as $sebab)
                                                <div class="flex items-center">
                                                    <span class="font-mono">(<span class="inline-flex justify-center w-4">@if($sebabUtamaA && $sebabUtamaA['deskripsi'] == $sebab)√@endif</span>)</span>
                                                    <span class="ml-2">{{ $sebab }}</span>
                                                </div>
                                            @endforeach
                                            @php $isLainA = $sebabUtamaA && !in_array($sebabUtamaA['deskripsi'], $tindakanBerbahaya); @endphp
                                            <div class="flex items-center">
                                                <span class="font-mono">(<span class="inline-flex justify-center w-4">@if($isLainA)√@endif</span>)</span>
                                                <span class="ml-2">Lain-lain: {{ $isLainA ? $sebabUtamaA['deskripsi'] : '' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="font-semibold">B. Keadaan Berbahaya (Unsafe Condition):</p>
                                        <div class="pl-4 space-y-1">
                                            @foreach ($keadaanBerbahaya as $sebab)
                                                <div class="flex items-center">
                                                    <span class="font-mono">(<span class="inline-flex justify-center w-4">@if($sebabUtamaB && $sebabUtamaB['deskripsi'] == $sebab)√@endif</span>)</span>
                                                    <span class="ml-2">{{ $sebab }}</span>
                                                </div>
                                            @endforeach
                                            @php $isLainB = $sebabUtamaB && !in_array($sebabUtamaB['deskripsi'], $keadaanBerbahaya); @endphp
                                            <div class="flex items-center">
                                                <span class="font-mono">(<span class="inline-flex justify-center w-4">@if($isLainB)√@endif</span>)</span>
                                                <span class="ml-2">Lain-lain: {{ $isLainB ? $sebabUtamaB['deskripsi'] : '' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 16. Analisa Masalah -->
                            <div class="pt-4 border-t-2 border-black">
                                <strong class="font-bold">16. Analisa Masalah:</strong>
                                <div class="prose prose-sm max-w-none mt-1">{!! $laporan->analisa_masalah !!}</div>
                            </div>

                            <!-- 17. Saran Perbaikan -->
                            <div class="pt-4 border-t-2 border-black">
                                <strong class="font-bold">17. Saran Perbaikan, PIC, Due Date:</strong>
                                <table class="w-full border-collapse border border-black mt-2 text-xs">
                                    <thead>
                                        <tr class="border-b-2 border-black font-bold">
                                            <td class="border-r border-black p-1 text-center">No</td>
                                            <td class="border-r border-black p-1">Tindakan Perbaikan</td>
                                            <td class="border-r border-black p-1 text-center">PIC</td>
                                            <td class="p-1 text-center">Due Date</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($laporan->saranPerbaikan as $saran)
                                        <tr class="border-b border-black">
                                            <td class="border-r border-black p-1 text-center">{{ $loop->iteration }}</td>
                                            <td class="border-r border-black p-1">{{ $saran->tindakan }}</td>
                                            <td class="border-r border-black p-1 text-center">{{ $saran->pic }}</td>
                                            <td class="p-1 text-center">{{ optional($saran->due_date)->format('d-M-y') }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="4" class="p-2 text-center text-gray-500">Tidak ada saran perbaikan.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Tindakan Pencegahan -->
                            <div class="pt-4 border-t-2 border-black">
                                <strong class="font-bold">Tindakan Pencegahan:</strong>
                                <div class="prose prose-sm max-w-none mt-1">{!! $laporan->tindakan_pencegahan !!}</div>
                            </div>

                            <!-- Rekomendasi -->
                            <div class="pt-4 border-t-2 border-black">
                                <strong class="font-bold">Rekomendasi:</strong>
                                <div class="prose prose-sm max-w-none mt-1">{!! $laporan->rekomendasi !!}</div>
                            </div>
                        </div>

                        {{-- BAGIAN TANDA TANGAN DINAMIS --}}
                        @php
                            $approvedHistories = $laporan->approvalHistories->where('action', 'approved');
                            $signatories = [
                                [ 'title' => 'Dibuat Oleh', 'role' => 'Pembuat Laporan', 'user' => $laporan->pembuatLaporan, 'action_date' => $laporan->created_at ],
                                [ 'title' => 'Diketahui', 'role' => 'Ass. Manager HSE', 'user' => $laporan->managerHse, 'action_date' => optional($approvedHistories->firstWhere('user_id', $laporan->manager_hse_id))->created_at ],
                                [ 'title' => 'Diketahui', 'role' => 'Manager Terkait', 'user' => $laporan->managerTerkait, 'action_date' => optional($approvedHistories->firstWhere('user_id', $laporan->manager_terkait_id))->created_at ],
                                [ 'title' => 'Diketahui', 'role' => 'Dept Head QHSE', 'user' => $laporan->deptHead, 'action_date' => optional($approvedHistories->firstWhere('user_id', $laporan->dept_head_id))->created_at ],
                                [ 'title' => 'Disetujui', 'role' => 'General Manager', 'user' => $laporan->generalManager, 'action_date' => optional($approvedHistories->firstWhere('user_id', $laporan->gm_id))->created_at ],
                            ];
                        @endphp

                        <div class="mt-8 text-sm">
                            <p class="text-right">Jakarta, {{ optional($laporan->date)->format('d F Y') }}</p>

                            <div class="grid grid-cols-5 text-center mt-4 font-bold">
                                <div class="col-span-1 border border-black p-1">{{ $signatories[0]['title'] }}</div>
                                <div class="col-span-3 border-y border-r border-black p-1">Diketahui</div>
                                <div class="col-span-1 border-y border-r border-black p-1">{{ $signatories[4]['title'] }}</div>
                            </div>

                            <div class="grid grid-cols-5 text-center text-xs h-32">
                                @foreach ($signatories as $signatory)
                                    <div class="border-b {{ $loop->first ? 'border-x' : 'border-r' }} border-black flex flex-col justify-center items-center p-2">
                                        @if ($signatory['action_date'])
                                            <div class="text-center">
                                                <p class="font-bold text-green-600">{{ $loop->first ? 'SUBMITTED' : 'APPROVED' }}</p>
                                                <p class="text-gray-600 mt-1">{{ \Carbon\Carbon::parse($signatory['action_date'])->format('d M Y') }}</p>
                                                <p class="text-gray-500 text-[10px]">{{ \Carbon\Carbon::parse($signatory['action_date'])->format('H:i:s') }}</p>
                                            </div>
                                        @else
                                            <div class="text-gray-400 italic">Menunggu...</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <div class="grid grid-cols-5 text-center font-bold text-xs">
                                @foreach ($signatories as $signatory)
                                    <div class="border-b {{ $loop->first ? 'border-x' : 'border-r' }} border-black p-1 flex flex-col h-16 justify-center">
                                        <span class="font-bold underline">{{ optional($signatory['user'])->name ?? 'N/A' }}</span>
                                        <span class="font-normal">{{ $signatory['role'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            <!-- ================================================== -->
            <!--     RIWAYAT PROSES (Hanya Tampil di Layar)         -->
            <!-- ================================================== -->
            <div class="bg-white shadow-sm sm:rounded-lg print:hidden">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-900 border-b pb-3">Riwayat Proses</h3>
                    @forelse ($laporan->approvalHistories->sortBy('created_at') as $history)
                        @if ($loop->first)
                            <div class="mt-4 flow-root">
                        <ul role="list" class="-mb-8"> @endif
                                <li>
                                    <div class="relative pb-8">
                                        @if(!$loop->last) <span
                                        class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200"></span> @endif
                                        <div class="relative flex space-x-3">
                                            <div><span
                                                    class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white @if($history->action == 'created') bg-blue-500 @elseif($history->action == 'approved') bg-green-500 @elseif($history->action == 'rejected') bg-red-500 @else bg-gray-500 @endif"><svg
                                                        class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                                                        viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd"
                                                            d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                                            clip-rule="evenodd" />
                                                    </svg></span></div>
                                            <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                                <div>
                                                    <p class="text-sm text-gray-500"><span
                                                            class="font-medium text-gray-900">{{ $history->user->name ?? 'Sistem' }}</span>
                                                        <span
                                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium @if($history->action == 'created') bg-blue-100 text-blue-800 @elseif($history->action == 'approved') bg-green-100 text-green-800 @elseif($history->action == 'rejected') bg-red-100 text-red-800 @else bg-gray-100 text-gray-800 @endif">{{ Str::title($history->action) }}</span>
                                                    </p>
                                                    <p class="mt-1 text-sm italic text-gray-700">"{{ $history->notes }}"</p>
                                                </div>
                                                <div class="whitespace-nowrap text-right text-sm text-gray-500"><time
                                                        title="{{ \Carbon\Carbon::parse($history->created_at)->format('d M Y H:i:s') }}">{{ \Carbon\Carbon::parse($history->created_at)->diffForHumans() }}</time>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                @if ($loop->last)
                                    </ul>
                                </div> @endif
                    @empty
                        <p class="mt-4 text-sm text-gray-500">Belum ada riwayat proses.</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>