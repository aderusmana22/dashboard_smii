<x-app-layout>
    @section('title')
        {{-- Judul halaman dinamis, tergantung mode create atau update --}}
        {{ isset($laporan) ? 'Revisi Laporan Kecelakaan' : 'Form Laporan Kecelakaan Baru' }}
    @endsection

    @php
        // Flag untuk menentukan apakah ini mode revisi/update.
        // Ini akan menjadi false saat membuat baru dari controller ($laporan = null).
        $isUpdate = isset($laporan);
    @endphp

    @push('styles')
        {{-- CDN untuk Select2 --}}
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <style>
            /* Menyesuaikan tampilan Select2 agar cocok dengan Tailwind */
            .select2-container .select2-selection--single { height: 2.625rem !important; border: 1px solid #d1d5db !important; border-radius: 0.375rem !important; }
            .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 2.625rem !important; padding-left: 0.75rem !important; color: #1f2937; }
            .select2-container--default .select2-selection--single .select2-selection__arrow { height: 2.625rem !important; }
            .select2-dropdown { border: 1px solid #d1d5db !important; border-radius: 0.375rem !important; }
            .select2-search__field { border: 1px solid #d1d5db !important; }
        </style>
    @endpush

    <div class="py-10">
        <div class="container mx-auto px-4">
            <div class="bg-white shadow-md rounded-lg">
                <div class="p-4 md:p-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">
                        {{ $isUpdate ? 'Revisi Laporan #' . ($laporan->nomor_form ?? $laporan->id) : 'Form Laporan Kecelakaan Baru' }}
                    </h2>

                    @if ($isUpdate)
                        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6" role="alert">
                            <p class="font-bold">Mode Revisi</p>
                            <p>Anda sedang merevisi laporan. Data dari laporan sebelumnya telah dimuat. Setelah disimpan, laporan ini akan menjadi revisi baru dan memulai kembali alur persetujuan.</p>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">Error!</strong>
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                             <strong class="font-bold">Terdapat kesalahan validasi:</strong>
                            <ul class="mt-2 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('accidents-report.store') }}" method="POST" class="mt-5">
                        @csrf

                        @if ($isUpdate)
                            <input type="hidden" name="revised_from_id" value="{{ $laporan->id }}">
                        @endif

                        <!-- HEADER FORM -->
                        <div class="border rounded-lg">
                            <div class="grid grid-cols-1 md:grid-cols-12">
                                <div class="md:col-span-3 flex flex-col justify-center items-center p-3">
                                     <img src="{{ asset('assets/images/logohitam.png') }}" alt="Sinar Meadow Logo" class="h-16">
                                    <p class="mb-0 font-semibold text-center text-sm mt-2">PT SINAR MEADOW<br>INTERNATIONAL INDONESIA</p>
                                </div>
                                <div class="md:col-span-6 border-l border-r flex flex-col justify-center text-center font-bold">
                                    <div class="p-2 border-b text-xl">FORM</div>
                                    <div class="p-4 text-2xl">LAPORAN INVESTIGASI KECELAKAAN KERJA</div>
                                </div>
                                <div class="md:col-span-3 p-3 space-y-2">
                                    <div class="grid grid-cols-3 items-center">
                                        <label for="nomor_form" class="col-span-1 text-sm">Nomor:</label>
                                        <div class="col-span-2">
                                            <input type="text" id="nomor_form" name="nomor_form" value="Akan digenerate otomatis" readonly class="w-full border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-500 text-sm">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-3 items-center">
                                        <label for="date_display" class="col-span-1 text-sm">Date:</label>
                                        <div class="col-span-2">
                                            <input type="text" id="date_display" readonly class="w-full bg-gray-100 border-transparent rounded-md px-2 text-sm">
                                            <input type="hidden" id="date" name="date" value="{{ old('date', ($isUpdate && $laporan->date) ? $laporan->date->format('Y-m-d') : '') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- AKHIR HEADER FORM -->

                        <h4 class="font-bold text-xl mt-8 pt-4 border-t mb-4">Detail Insiden & Dampak</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                            <div>
                                <label for="kategori_kecelakaan" class="block text-sm font-medium text-gray-700">Kategori Kecelakaan</label>
                                <select id="kategori_kecelakaan" name="kategori_kecelakaan" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="Kerja" {{ old('kategori_kecelakaan', $laporan->kategori_kecelakaan ?? '') == 'Kerja' ? 'selected' : '' }}>Kerja</option>
                                    <option value="Lalu Lintas" {{ old('kategori_kecelakaan', $laporan->kategori_kecelakaan ?? '') == 'Lalu Lintas' ? 'selected' : '' }}>Lalu Lintas</option>
                                    <option value="Kebakaran" {{ old('kategori_kecelakaan', $laporan->kategori_kecelakaan ?? '') == 'Kebakaran' ? 'selected' : '' }}>Kebakaran</option>
                                    <option value="Lain-lain" {{ old('kategori_kecelakaan', $laporan->kategori_kecelakaan ?? '') == 'Lain-lain' ? 'selected' : '' }}>Lain-lain</option>
                                </select>
                            </div>
                            <div>
                                <label for="kategori_dampak" class="block text-sm font-medium text-gray-700">Kategori Dampak</label>
                                <select id="kategori_dampak" name="kategori_dampak" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="Ringan" {{ old('kategori_dampak', $laporan->kategori_dampak ?? '') == 'Ringan' ? 'selected' : '' }}>Ringan (Minor)</option>
                                    <option value="Sedang" {{ old('kategori_dampak', $laporan->kategori_dampak ?? '') == 'Sedang' ? 'selected' : '' }}>Sedang (Moderate)</option>
                                    <option value="Berat" {{ old('kategori_dampak', $laporan->kategori_dampak ?? '') == 'Berat' ? 'selected' : '' }}>Berat (Major)</option>
                                    <option value="Kematian" {{ old('kategori_dampak', $laporan->kategori_dampak ?? '') == 'Kematian' ? 'selected' : '' }}>Kematian (Fatality)</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="waktu_kecelakaan" class="block text-sm font-medium text-gray-700">Tanggal & Jam Kecelakaan</label>
                            <input type="datetime-local" id="waktu_kecelakaan" name="waktu_kecelakaan" value="{{ old('waktu_kecelakaan', ($isUpdate && $laporan->waktu_kecelakaan) ? $laporan->waktu_kecelakaan->format('Y-m-d\TH:i') : '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div class="mb-4">
                            <label for="lokasi_kecelakaan" class="block text-sm font-medium text-gray-700">Lokasi Kecelakaan</label>
                            <input type="text" id="lokasi_kecelakaan" name="lokasi_kecelakaan" value="{{ old('lokasi_kecelakaan', $laporan->lokasi_kecelakaan ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                            <div>
                                <label for="tipe_kecelakaan" class="block text-sm font-medium text-gray-700">Tipe Kecelakaan</label>
                                <input type="text" id="tipe_kecelakaan" name="tipe_kecelakaan" value="{{ old('tipe_kecelakaan', $laporan->tipe_kecelakaan ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="cth: Terpeleset, Terjatuh">
                            </div>
                            <div>
                                <label for="bagian_terluka" class="block text-sm font-medium text-gray-700">Bagian yang Terluka</label>
                                <input type="text" id="bagian_terluka" name="bagian_terluka" value="{{ old('bagian_terluka', $laporan->bagian_terluka ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="cth: Tangan Kanan">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="uraian_kejadian" class="block text-sm font-medium text-gray-700">Uraian Kejadian</label>
                            <textarea id="uraian_kejadian" name="uraian_kejadian" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" rows="8">{{ old('uraian_kejadian', $laporan->uraian_kejadian ?? '') }}</textarea>
                        </div>

                        <h4 class="font-bold text-xl mt-8 pt-4 border-t mb-4">Data Korban</h4>
                        <div class="mb-4">
                            <label for="nama_korban" class="block text-sm font-medium text-gray-700">Nama Korban</label>
                            <input type="text" id="nama_korban" name="nama_korban" value="{{ old('nama_korban', $laporan->nama_korban ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                            <div>
                                <label for="nik" class="block text-sm font-medium text-gray-700">NIK</label>
                                <input type="text" id="nik" name="nik" value="{{ old('nik', $laporan->nik ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label for="tanggal_lahir" class="block text-sm font-medium text-gray-700">Tanggal Lahir</label>
                                <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir', ($isUpdate && $laporan->tanggal_lahir) ? $laporan->tanggal_lahir->format('Y-m-d') : '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" onchange="hitungUsia()">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="usia" class="block text-sm font-medium text-gray-700">Usia</label>
                            <input type="text" id="usia" name="usia" value="{{ old('usia', $laporan->usia ?? '') }}" readonly class="mt-1 block w-1/4 bg-gray-100 border-transparent rounded-md px-2">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                            <div>
                                <label for="tanggal_masuk" class="block text-sm font-medium text-gray-700">Tanggal Masuk Kerja</label>
                                <input type="date" id="tanggal_masuk" name="tanggal_masuk" value="{{ old('tanggal_masuk', ($isUpdate && $laporan->tanggal_masuk) ? $laporan->tanggal_masuk->format('Y-m-d') : '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" onchange="hitungMasaKerja()">
                            </div>
                            <div>
                                <label for="masa_kerja" class="block text-sm font-medium text-gray-700">Masa Kerja</label>
                                <input type="text" id="masa_kerja" name="masa_kerja" value="{{ old('masa_kerja', $laporan->masa_kerja ?? '') }}" readonly class="mt-1 block w-full bg-gray-100 border-transparent rounded-md px-2">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="jabatan" class="block text-sm font-medium text-gray-700">Jabatan</label>
                            <input type="text" id="jabatan" name="jabatan" value="{{ old('jabatan', $laporan->jabatan ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div class="mb-4">
                            <label for="departemen" class="block text-sm font-medium text-gray-700">Seksi / Departemen</label>
                            <input type="text" id="departemen" name="departemen" value="{{ old('departemen', $laporan->departemen ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>

                        <h4 class="font-bold text-xl mt-8 pt-4 border-t mb-4">Tindakan Pertolongan & Akibat</h4>
                        <div class="mb-4">
                            <label for="pertolongan" class="block text-sm font-medium text-gray-700">Diberikan pertolongan (P3K)</label>
                            <select id="pertolongan" name="pertolongan" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="Di Tempat Kejadian" {{ old('pertolongan', $laporan->pertolongan ?? '') == 'Di Tempat Kejadian' ? 'selected' : '' }}>Di Tempat Kejadian</option>
                                <option value="Di Klinik" {{ old('pertolongan', $laporan->pertolongan ?? '') == 'Di Klinik' ? 'selected' : '' }}>Di Klinik</option>
                                <option value="Di Rumah Sakit" {{ old('pertolongan', $laporan->pertolongan ?? '') == 'Di Rumah Sakit' ? 'selected' : '' }}>Di Rumah Sakit</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center mb-4">
                            <label for="p3k_oleh" class="md:col-span-3 text-sm font-medium text-gray-700">P3K dilakukan Oleh</label>
                            <div class="md:col-span-9 grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                                <div class="md:col-span-8">
                                    <input type="text" id="p3k_oleh" name="p3k_oleh" value="{{ old('p3k_oleh', $laporan->p3k_oleh ?? '') }}" class="w-full border-gray-300 rounded-md shadow-sm">
                                </div>
                                <label for="jam_p3k" class="md:col-span-1 text-sm">Jam</label>
                                <div class="md:col-span-3">
                                    <input type="time" id="jam_p3k" name="jam_p3k" value="{{ old('jam_p3k', $laporan->jam_p3k ?? '') }}" class="w-full border-gray-300 rounded-md shadow-sm">
                                </div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="akibat_kecelakaan" class="block text-sm font-medium text-gray-700">Akibat Kecelakaan</label>
                            <select id="akibat_kecelakaan" name="akibat_kecelakaan" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="Sementara Total tak mampu bekerja" {{ old('akibat_kecelakaan', $laporan->akibat_kecelakaan ?? '') == 'Sementara Total tak mampu bekerja' ? 'selected' : '' }}>Sementara Total tak mampu bekerja</option>
                                <option value="Sementara Sebagian tak mampu bekerja" {{ old('akibat_kecelakaan', $laporan->akibat_kecelakaan ?? '') == 'Sementara Sebagian tak mampu bekerja' ? 'selected' : '' }}>Sementara Sebagian tak mampu bekerja</option>
                                <option value="Tetap Sebagian tak mampu bekerja" {{ old('akibat_kecelakaan', $laporan->akibat_kecelakaan ?? '') == 'Tetap Sebagian tak mampu bekerja' ? 'selected' : '' }}>Tetap Sebagian tak mampu bekerja</option>
                                <option value="Tetap Total tak mampu bekerja" {{ old('akibat_kecelakaan', $laporan->akibat_kecelakaan ?? '') == 'Tetap Total tak mampu bekerja' ? 'selected' : '' }}>Tetap Total tak mampu bekerja</option>
                                <option value="Meninggal" {{ old('akibat_kecelakaan', $laporan->akibat_kecelakaan ?? '') == 'Meninggal' ? 'selected' : '' }}>Meninggal</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="waktu_hilang" class="block text-sm font-medium text-gray-700">Jumlah waktu hilang (hari)</label>
                            <input type="number" id="waktu_hilang" name="waktu_hilang" value="{{ old('waktu_hilang', $laporan->waktu_hilang ?? '') }}" class="mt-1 block w-1/4 border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Biaya Perawatan</label>
                            <div id="biaya-container" class="mt-2 space-y-2"></div>
                            <button type="button" class="mt-2 px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700" onclick="tambahBiaya()">Tambah Biaya</button>
                        </div>

                        <h4 class="font-bold text-xl mt-8 pt-4 border-t mb-4">Alat Pelindung Diri (APD)</h4>
                        @php
                            $apds = [
                                'sarung_tangan' => 'Sarung Tangan', 'sepatu' => 'Sepatu Keselamatan', 'helm' => 'Helm',
                                'masker' => 'Masker', 'kacamata' => 'Kaca mata', 'celemek' => 'Celemek',
                                'kedok' => 'Kedok pelindung Muka', 'hairnet' => 'Hair Net'
                            ];
                        @endphp
                        @foreach ($apds as $key => $label)
                        <div class="border rounded-md p-3 mb-3 bg-gray-50">
                            <div class="flex items-center">
                                <input class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" type="checkbox" id="apd_wajib_{{ $key }}" name="apd_wajib_{{ $key }}" onchange="toggleApdDetails('{{ $key }}')">
                                <label class="ml-2 block text-sm font-bold text-gray-900" for="apd_wajib_{{ $key }}">{{ $label }} Diwajibkan</label>
                            </div>
                            <div id="apd_details_{{ $key }}" class="apd-details hidden pl-6 mt-3">
                                @if ($key == 'sarung_tangan')
                                <div class="mb-2">
                                    <label for="apd_keterangan_{{ $key }}" class="text-sm font-medium text-gray-700">Keterangan (Jenis/Spesifikasi):</label>
                                    <input type="text" id="apd_keterangan_{{ $key }}" name="apd_keterangan_{{ $key }}" class="mt-1 block w-full text-sm border-gray-300 rounded-md shadow-sm" disabled>
                                </div>
                                @endif
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Apakah Dipakai oleh Korban?</label>
                                    <div class="flex items-center mt-1">
                                        <input class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300" type="radio" name="apd_dipakai_{{ $key }}" id="apd_dipakai_{{ $key }}_ya" value="ya" disabled>
                                        <label class="ml-2 text-sm text-gray-900" for="apd_dipakai_{{ $key }}_ya">Ya</label>
                                    </div>
                                    <div class="flex items-center mt-1">
                                        <input class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300" type="radio" name="apd_dipakai_{{ $key }}" id="apd_dipakai_{{ $key }}_tidak" value="tidak" disabled>
                                        <label class="ml-2 text-sm text-gray-900" for="apd_dipakai_{{ $key }}_tidak">Tidak</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach

                        <h4 class="font-bold text-xl mt-8 pt-4 border-t mb-4">Analisa Sebab Utama Kecelakaan</h4>
                        @php
                            $tindakanBerbahaya = [
                                'Mengoperasikan tanpa wewenang', 'Mengoperasikan dengan kecepatan berlebihan', 'Alat penyelamat tidak berfungsi',
                                'Menggunakan alat yang rusak', 'Menggunakan alat / bahan tidak sesuai fungsinya.', 'Menggunakan alat secara tidak benar',
                                'Menggunakan alat pelindung diri tidak sesuai fungsinya.', 'Tidak menggunakan Alat Pelindung Diri yang diwajibkan',
                                'Pemuatan / pembongkaran / penempatan yang tidak sesuai', 'Salah mengangkat', 'Mengambil posisi salah',
                                'Menservis alat yang berputar', 'Bersendau gurau', 'Mengantuk, melamun'
                            ];
                            $keadaanBerbahaya = [
                                'Alat penyelamat yang kurang sempurna', 'Alat, mesin, atau bahan rusak', 'Sistem pemberi peringatan yang kurang sempurna',
                                'Bahaya kebakaran & peledakan', 'House keeping di bawah standard', 'Kondisi udara yang berbahaya terhadap gas, debu, dan uap',
                                'Kebisingan tinggi', 'Paparan / tekanan panas', 'Pencahayaan kurang'
                            ];
                        @endphp
                        <div class="border rounded-md p-3 mb-3 bg-gray-50">
                            <p class="font-bold">A. Tindakan Berbahaya (Unsafe Human Act)</p>
                            @foreach ($tindakanBerbahaya as $index => $sebab)
                            <div class="flex items-center mb-2">
                                <input class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300" type="radio" name="sebab_utama" id="sebab_a_{{ $index }}" value="A - {{ $sebab }}">
                                <label class="ml-2 text-sm text-gray-900" for="sebab_a_{{ $index }}">{{ $loop->iteration }}. {{ $sebab }}</label>
                            </div>
                            @endforeach
                            <div class="flex items-center">
                                <input class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300" type="radio" name="sebab_utama" id="sebab_a_lain" value="on">
                                <label class="ml-2 text-sm text-gray-900" for="sebab_a_lain">{{ count($tindakanBerbahaya) + 1 }}. Lain-lain, sebutkan:</label>
                            </div>
                            <input type="text" id="sebab_a_lain_input" name="sebab_a_lain_input" class="mt-1 block w-full text-sm border-gray-300 rounded-md shadow-sm disabled:bg-gray-200" disabled>
                        </div>
                        <div class="border rounded-md p-3 mb-3 bg-gray-50">
                            <p class="font-bold">B. Keadaan Berbahaya (Unsafe Condition)</p>
                            @foreach ($keadaanBerbahaya as $index => $sebab)
                            <div class="flex items-center mb-2">
                                <input class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300" type="radio" name="sebab_utama" id="sebab_b_{{ $index }}" value="B - {{ $sebab }}">
                                <label class="ml-2 text-sm text-gray-900" for="sebab_b_{{ $index }}">{{ $loop->iteration }}. {{ $sebab }}</label>
                            </div>
                            @endforeach
                            <div class="flex items-center">
                                <input class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300" type="radio" name="sebab_utama" id="sebab_b_lain" value="on">
                                <label class="ml-2 text-sm text-gray-900" for="sebab_b_lain">{{ count($keadaanBerbahaya) + 1 }}. Lain-lain, sebutkan:</label>
                            </div>
                            <input type="text" id="sebab_b_lain_input" name="sebab_b_lain_input" class="mt-1 block w-full text-sm border-gray-300 rounded-md shadow-sm disabled:bg-gray-200" disabled>
                        </div>

                        <h4 class="font-bold text-xl mt-8 pt-4 border-t mb-4">Analisa Masalah</h4>
                        <div class="mb-4">
                            <textarea id="analisa_masalah" name="analisa_masalah" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" rows="6">{{ old('analisa_masalah', $laporan->analisa_masalah ?? '') }}</textarea>
                        </div>

                        <h4 class="font-bold text-xl mt-8 pt-4 border-t mb-4">Saran Perbaikan</h4>
                        <div class="overflow-x-auto mb-2">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-[5%]">No</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tindakan Perbaikan</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[20%]">PIC</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[20%]">Due Date</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-[10%]">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="perbaikan-container" class="bg-white divide-y divide-gray-200"></tbody>
                            </table>
                        </div>
                        <button type="button" class="px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700" onclick="tambahSaranPerbaikan()">Tambah Saran</button>

                        <h4 class="font-bold text-xl mt-8 pt-4 border-t mb-4">Tindakan Pencegahan</h4>
                        <div class="mb-4">
                            <textarea id="tindakan_pencegahan" name="tindakan_pencegahan" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" rows="6">{{ old('tindakan_pencegahan', $laporan->tindakan_pencegahan ?? '') }}</textarea>
                        </div>

                        <h4 class="font-bold text-xl mt-8 pt-4 border-t mb-4">Rekomendasi</h4>
                        <div class="mb-4">
                            <textarea id="rekomendasi" name="rekomendasi" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" rows="6">{{ old('rekomendasi', $laporan->rekomendasi ?? '') }}</textarea>
                        </div>

                        <!-- ================================================================== -->
                        <!-- --- BAGIAN PERSETUJUAN YANG DIPERBAIKI --- -->
                        <!-- ================================================================== -->
                        <h4 class="font-bold text-xl mt-8 pt-4 border-t mb-4">Persetujuan & Tanda Tangan</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">

                            <!-- Pembuat Laporan (Menggunakan semua user) -->
                            <div class="border rounded-lg p-4 flex flex-col">
                                <label for="pembuat_laporan_id" class="block text-sm font-medium text-gray-700 mb-2 text-center">Pembuat Laporan</label>
                                <select id="pembuat_laporan_id" name="pembuat_laporan_id" class="user-select w-full">
                                    <option value=""></option>
                                    @foreach ($allUsers as $user)
                                        <option value="{{ $user->id }}"
                                            {{ old('pembuat_laporan_id', $laporan->pembuat_laporan_id ?? Auth::id()) == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Assisten/Manager HSE (Menggunakan koleksi $hseManagers) -->
                            <div class="border rounded-lg p-4 flex flex-col">
                                <label for="manager_hse_id" class="block text-sm font-medium text-gray-700 mb-2 text-center">Assisten/Manager HSE</label>
                                <select id="manager_hse_id" name="manager_hse_id" class="user-select w-full">
                                    <option value=""></option>
                                    @foreach ($hseManagers as $user)
                                        <option value="{{ $user->id }}"
                                            {{ old('manager_hse_id', $laporan->manager_hse_id ?? '') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Assisten/Manager Terkait (Menggunakan semua user) -->
                            <div class="border rounded-lg p-4 flex flex-col">
                                <label for="manager_terkait_id" class="block text-sm font-medium text-gray-700 mb-2 text-center">Assisten/Manager Terkait</label>
                                <select id="manager_terkait_id" name="manager_terkait_id" class="user-select w-full">
                                    <option value=""></option>
                                    @foreach ($allUsers as $user)
                                        <option value="{{ $user->id }}"
                                            {{ old('manager_terkait_id', $laporan->manager_terkait_id ?? '') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Dept Head QM HSE (Menggunakan koleksi $deptHeads) -->
                            <div class="border rounded-lg p-4 flex flex-col">
                                <label for="dept_head_id" class="block text-sm font-medium text-gray-700 mb-2 text-center">Dept Head QM HSE</label>
                                <select id="dept_head_id" name="dept_head_id" class="user-select w-full">
                                    <option value=""></option>
                                    @foreach ($deptHeads as $user)
                                        <option value="{{ $user->id }}"
                                            {{ old('dept_head_id', $laporan->dept_head_id ?? '') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- GM (Menggunakan koleksi $gms) -->
                            <div class="border rounded-lg p-4 flex flex-col">
                                <label for="gm_id" class="block text-sm font-medium text-gray-700 mb-2 text-center">GM</label>
                                <select id="gm_id" name="gm_id" class="user-select w-full">
                                    <option value=""></option>
                                    @foreach ($gms as $user)
                                        <option value="{{ $user->id }}"
                                            {{ old('gm_id', $laporan->gm_id ?? '') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>
                        <!-- ================================================================== -->
                        <!-- --- AKHIR BAGIAN PERSETUJUAN --- -->
                        <!-- ================================================================== -->

                        <button type="submit" class="w-full mt-8 py-3 px-4 border border-transparent rounded-md shadow-sm text-lg font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ $isUpdate ? 'Kirim Ulang Laporan Revisi' : 'Submit Laporan' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="{{ asset('js/tinymce/tinymce.min.js') }}"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
    // ==================================================================
    // --- PENGGUNAAN JQUERY DENGAN .noConflict() ---
    // ==================================================================
    var $j = jQuery.noConflict();

    $j(document).ready(function() {
        // Inisialisasi Select2 untuk semua dropdown persetujuan
        $j('.user-select').select2({
            placeholder: 'Cari & pilih pengguna',
            allowClear: true
        });

        // --- LOGIKA PENGISIAN OTOMATIS YANG DISEMPURNAKAN ---
        const isUpdate = @json($isUpdate);
        if (!isUpdate) {
            // Jika membuat laporan baru, coba pilih otomatis user jika hanya ada satu pilihan
            // dalam koleksi yang sudah difilter dari controller.
            @if($hseManagers->count() == 1)
                $j('#manager_hse_id').val('{{ $hseManagers->first()->id }}').trigger('change');
            @endif

            @if($deptHeads->count() == 1)
                $j('#dept_head_id').val('{{ $deptHeads->first()->id }}').trigger('change');
            @endif

            @if($gms->count() == 1)
                $j('#gm_id').val('{{ $gms->first()->id }}').trigger('change');
            @endif
        }
    });
    // ==================================================================
    // --- AKHIR KODE JQUERY ---
    // ==================================================================


    // Kode JavaScript murni (Vanilla JS) di bawah ini tidak perlu diubah.
    const isUpdate = @json($isUpdate);
    const biayaData = @json($isUpdate ? ($laporan->biayaPerawatan ?? []) : []);
    const perbaikanData = @json($isUpdate ? ($laporan->saranPerbaikan ?? []) : []);
    const apdData = @json($isUpdate ? ($laporan->apd_data ?? []) : []);
    const sebabUtamaKategori = @json($isUpdate ? $laporan->sebab_utama_kategori : null);
    const sebabUtamaDeskripsi = @json($isUpdate ? $laporan->sebab_utama_deskripsi : null);

    document.addEventListener('DOMContentLoaded', function () {
        if (typeof tinymce !== 'undefined') {
            tinymce.init({
                selector: 'textarea#uraian_kejadian, textarea#analisa_masalah, textarea#tindakan_pencegahan, textarea#rekomendasi',
                plugins: 'autolink lists link charmap preview anchor image media paste',
                toolbar: 'undo redo | styles | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | link image media',
                paste_data_images: true,
                automatic_uploads: false,
                file_picker_types: 'image',
                height: 350,
                promotion: false,
                license_key: 'gpl'
            });
        }

        const allRadioSebab = document.querySelectorAll('input[name="sebab_utama"]');
        const lainInputA = document.getElementById('sebab_a_lain_input');
        const lainInputB = document.getElementById('sebab_b_lain_input');
        const lainRadioA = document.getElementById('sebab_a_lain');
        const lainRadioB = document.getElementById('sebab_b_lain');

        function handleSebabChange() {
            lainInputA.disabled = !lainRadioA.checked;
            if (!lainRadioA.checked) lainInputA.value = ''; else lainInputA.focus();
            lainInputB.disabled = !lainRadioB.checked;
            if (!lainRadioB.checked) lainInputB.value = ''; else lainInputB.focus();
        }
        allRadioSebab.forEach(radio => radio.addEventListener('change', handleSebabChange));

        const dateInput = document.getElementById('date');
        const dateDisplay = document.getElementById('date_display');
        const initialDate = dateInput.value ? new Date(dateInput.value + 'T00:00:00') : new Date();
        dateDisplay.value = initialDate.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
        if (!dateInput.value) {
            dateInput.value = initialDate.toISOString().split('T')[0];
        }

        if (isUpdate) {
            biayaData.forEach(item => tambahBiaya(item));
            perbaikanData.forEach(item => tambahSaranPerbaikan(item));
            for (const key in apdData) {
                const apd = apdData[key];
                const checkbox = document.getElementById(`apd_wajib_${key}`);
                if (checkbox) {
                    checkbox.checked = true;
                    toggleApdDetails(key);
                    if (document.getElementById(`apd_keterangan_${key}`)) {
                        document.getElementById(`apd_keterangan_${key}`).value = apd.keterangan || '';
                    }
                    if (apd.dipakai) {
                        const radio = document.getElementById(`apd_dipakai_${key}_${apd.dipakai}`);
                        if (radio) radio.checked = true;
                    }
                }
            }
            if (sebabUtamaKategori && sebabUtamaDeskripsi) {
                const radioValue = `${sebabUtamaKategori} - ${sebabUtamaDeskripsi}`;
                const targetRadio = document.querySelector(`input[name="sebab_utama"][value="${radioValue}"]`);
                if (targetRadio) {
                    targetRadio.checked = true;
                } else {
                    const lainRadio = document.getElementById(`sebab_${sebabUtamaKategori.toLowerCase()}_lain`);
                    const lainInput = document.getElementById(`sebab_${sebabUtamaKategori.toLowerCase()}_lain_input`);
                    if (lainRadio && lainInput) {
                        lainRadio.checked = true;
                        lainInput.value = sebabUtamaDeskripsi;
                        lainInput.disabled = false;
                    }
                }
            }
        }
    });

    function hitungUsia() {
        const tgl = document.getElementById('tanggal_lahir').value;
        if (tgl) {
            const birthDate = new Date(tgl);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const m = today.getMonth() - birthDate.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) { age--; }
            document.getElementById('usia').value = age + " tahun";
        }
    }

    function hitungMasaKerja() {
        const tgl = document.getElementById('tanggal_masuk').value;
        if (tgl) {
            const startDate = new Date(tgl);
            const today = new Date();
            let years = today.getFullYear() - startDate.getFullYear();
            let months = today.getMonth() - startDate.getMonth();
            if (months < 0 || (months === 0 && today.getDate() < startDate.getDate())) { years--; months += 12; }
            document.getElementById('masa_kerja').value = years + " tahun " + months + " bulan";
        }
    }

    let biayaCount = 0;
    function tambahBiaya(data = null) {
        biayaCount++;
        const container = document.getElementById('biaya-container');
        const newItem = document.createElement('div');
        newItem.className = 'flex space-x-2';
        newItem.id = 'biaya_item_' + biayaCount;
        newItem.innerHTML = `<span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">Rp</span><input type="number" name="biaya_harga[]" class="flex-1 block w-full rounded-none border-gray-300" placeholder="Harga" value="${data ? data.harga : ''}"><input type="text" name="biaya_kategori[]" class="flex-1 block w-full border-gray-300" placeholder="Kategori" value="${data ? data.kategori : ''}"><button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-r-md text-white bg-red-600 hover:bg-red-700" onclick="hapusBiaya(${biayaCount})">Hapus</button>`;
        container.appendChild(newItem);
    }
    function hapusBiaya(id) { document.getElementById('biaya_item_' + id).remove(); }

    function toggleApdDetails(key) {
        const checkbox = document.getElementById(`apd_wajib_${key}`);
        const detailsDiv = document.getElementById(`apd_details_${key}`);
        const keteranganInput = document.getElementById(`apd_keterangan_${key}`);
        const radioButtons = document.getElementsByName(`apd_dipakai_${key}`);
        if (checkbox.checked) {
            detailsDiv.classList.remove('hidden');
            if (keteranganInput) { keteranganInput.disabled = false; }
            radioButtons.forEach(radio => radio.disabled = false);
        } else {
            detailsDiv.classList.add('hidden');
            if (keteranganInput) { keteranganInput.disabled = true; keteranganInput.value = ''; }
            radioButtons.forEach(radio => { radio.disabled = true; radio.checked = false; });
        }
    }

    function tambahSaranPerbaikan(data = null) {
        const container = document.getElementById('perbaikan-container');
        const newIndex = container.rows.length;
        const newRow = container.insertRow(newIndex);
        newRow.id = 'perbaikan_item_' + newIndex;
        newRow.innerHTML = `
            <td class="px-6 py-4 text-sm text-center text-gray-500">${newIndex + 1}</td>
            <td class="px-6 py-4"><input type="text" name="perbaikan_tindakan[]" class="w-full border-gray-300 rounded-md shadow-sm" placeholder="Uraian tindakan perbaikan" value="${data ? data.tindakan : ''}"></td>
            <td class="px-6 py-4"><input type="text" name="perbaikan_pic[]" class="w-full border-gray-300 rounded-md shadow-sm" placeholder="Nama PIC" value="${data ? data.pic : ''}"></td>
            <td class="px-6 py-4"><input type="date" name="perbaikan_due_date[]" class="w-full border-gray-300 rounded-md shadow-sm" value="${data ? data.due_date : ''}"></td>
            <td class="px-6 py-4 text-center text-sm font-medium"><button type="button" class="text-red-600 hover:text-red-900" onclick="hapusSaranPerbaikan('perbaikan_item_${newIndex}')">Hapus</button></td>
        `;
    }
    function hapusSaranPerbaikan(rowId) { document.getElementById(rowId).remove(); updateNomorSaran(); }
    function updateNomorSaran() {
        const rows = document.getElementById('perbaikan-container').rows;
        for (let i = 0; i < rows.length; i++) {
            rows[i].cells[0].innerText = i + 1;
            rows[i].id = 'perbaikan_item_' + i;
            rows[i].querySelector('button').setAttribute('onclick', `hapusSaranPerbaikan('perbaikan_item_${i}')`);
        }
    }
    </script>
    @endpush
</x-app-layout>