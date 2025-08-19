<x-app-layout>
    @section('title')
    Form Laporan Kecelakaan
    @endsection

    {{-- Styling sekarang menggunakan kelas-kelas Tailwind CSS --}}

    <div class="py-10">
        <div class="container mx-auto px-4">
            <div class="bg-white shadow-md rounded-lg">
                <div class="p-4 md:p-8">
                    <form action="{{ route('accidents-report.store') }}" method="POST" class="mt-5">
                        @csrf

                        <!-- HEADER FORM MENGGUNAKAN TAILWIND CSS GRID -->
                        <div class="border rounded-lg">
                            <div class="grid grid-cols-1 md:grid-cols-12">
                                <!-- Kolom Logo -->
                                <div class="md:col-span-3 flex flex-col justify-center items-center p-3">
                                     <img src="{{ asset('assets/images/logohitam.png') }}" alt="Sinar Meadow Logo">
                                    <p class="mb-0 font-semibold text-center text-sm">PT SINAR MEADOW<br>INTERNATIONAL INDONESIA</p>
                                </div>

                                <!-- Kolom Judul -->
                                <div class="md:col-span-6 border-l border-r flex flex-col justify-center text-center font-bold">
                                    <div class="p-2 border-b text-xl">FORM</div>
                                    <div class="p-4 text-2xl">LAPORAN INVESTIGASI KECELAKAAN KERJA</div>
                                </div>

                                <!-- Kolom Nomor & Tanggal -->
                                <div class="md:col-span-3 p-3 space-y-2">
                                    <div class="grid grid-cols-3 items-center">
                                        <label for="nomor_form" class="col-span-1 text-sm">Nomor form:</label>
                                        <div class="col-span-2">
                                            <input type="text" id="nomor_form" name="nomor_form" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-3 items-center">
                                        <label for="date" class="col-span-1 text-sm">Date:</label>
                                        <div class="col-span-2">
                                            <input type="text" id="date" name="date" readonly class="w-full bg-gray-100 border-transparent rounded-md px-2">
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
                                <select id="kategori_kecelakaan" name="kategori_kecelakaan" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="Kerja">Kerja</option>
                                    <option value="Lalu Lintas">Lalu Lintas</option>
                                    <option value="Kebakaran">Kebakaran</option>
                                    <option value="Lain-lain">Lain-lain</option>
                                </select>
                            </div>
                            <div>
                                <label for="kategori_dampak" class="block text-sm font-medium text-gray-700">Kategori Dampak</label>
                                <select id="kategori_dampak" name="kategori_dampak" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="Ringan">Ringan (Minor)</option>
                                    <option value="Sedang">Sedang (Moderate)</option>
                                    <option value="Berat">Berat (Major)</option>
                                    <option value="Kematian">Kematian (Fatality)</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="waktu_kecelakaan" class="block text-sm font-medium text-gray-700">Tanggal & Jam Kecelakaan</label>
                            <input type="datetime-local" id="waktu_kecelakaan" name="waktu_kecelakaan" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>
                        <div class="mb-4">
                            <label for="lokasi_kecelakaan" class="block text-sm font-medium text-gray-700">Lokasi Kecelakaan</label>
                            <input type="text" id="lokasi_kecelakaan" name="lokasi_kecelakaan" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                            <div>
                                <label for="tipe_kecelakaan" class="block text-sm font-medium text-gray-700">Tipe Kecelakaan</label>
                                <input type="text" id="tipe_kecelakaan" name="tipe_kecelakaan" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="cth: Terpeleset, Terjatuh">
                            </div>
                            <div>
                                <label for="bagian_terluka" class="block text-sm font-medium text-gray-700">Bagian yang Terluka</label>
                                <input type="text" id="bagian_terluka" name="bagian_terluka" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="cth: Tangan Kanan">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="uraian_kejadian" class="block text-sm font-medium text-gray-700">Uraian Kejadian</label>
                            <textarea id="uraian_kejadian" name="uraian_kejadian" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" rows="8"></textarea>
                        </div>

                        <h4 class="font-bold text-xl mt-8 pt-4 border-t mb-4">Data Korban</h4>
                        <div class="mb-4">
                            <label for="nama_korban" class="block text-sm font-medium text-gray-700">Nama Korban</label>
                            <input type="text" id="nama_korban" name="nama_korban" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                            <div>
                                <label for="nik" class="block text-sm font-medium text-gray-700">NIK</label>
                                <input type="text" id="nik" name="nik" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>
                            <div>
                                <label for="tanggal_lahir" class="block text-sm font-medium text-gray-700">Tanggal Lahir</label>
                                <input type="date" id="tanggal_lahir" name="tanggal_lahir" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" onchange="hitungUsia()">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="usia" class="block text-sm font-medium text-gray-700">Usia</label>
                            <input type="text" id="usia" name="usia" readonly class="mt-1 block w-1/4 bg-gray-100 border-transparent rounded-md px-2">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                            <div>
                                <label for="tanggal_masuk" class="block text-sm font-medium text-gray-700">Tanggal Masuk Kerja</label>
                                <input type="date" id="tanggal_masuk" name="tanggal_masuk" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" onchange="hitungMasaKerja()">
                            </div>
                            <div>
                                <label for="masa_kerja" class="block text-sm font-medium text-gray-700">Masa Kerja</label>
                                <input type="text" id="masa_kerja" name="masa_kerja" readonly class="mt-1 block w-full bg-gray-100 border-transparent rounded-md px-2">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="jabatan" class="block text-sm font-medium text-gray-700">Jabatan</label>
                            <input type="text" id="jabatan" name="jabatan" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>
                        <div class="mb-4">
                            <label for="departemen" class="block text-sm font-medium text-gray-700">Seksi / Departemen</label>
                            <input type="text" id="departemen" name="departemen" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>

                        <h4 class="font-bold text-xl mt-8 pt-4 border-t mb-4">Tindakan Pertolongan & Akibat</h4>
                        <div class="mb-4">
                            <label for="pertolongan" class="block text-sm font-medium text-gray-700">Diberikan pertolongan (P3K)</label>
                            <select id="pertolongan" name="pertolongan" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="Di Tempat Kejadian">Di Tempat Kejadian</option>
                                <option value="Di Klinik">Di Klinik</option>
                                <option value="Di Rumah Sakit">Di Rumah Sakit</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center mb-4">
                            <label for="p3k_oleh" class="md:col-span-3 text-sm font-medium text-gray-700">P3K dilakukan Oleh</label>
                            <div class="md:col-span-9 grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                                <div class="md:col-span-8">
                                    <input type="text" id="p3k_oleh" name="p3k_oleh" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                </div>
                                <label for="jam_p3k" class="md:col-span-1 text-sm">Jam</label>
                                <div class="md:col-span-3">
                                    <input type="time" id="jam_p3k" name="jam_p3k" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                </div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="akibat_kecelakaan" class="block text-sm font-medium text-gray-700">Akibat Kecelakaan</label>
                            <select id="akibat_kecelakaan" name="akibat_kecelakaan" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="Sementara Total tak mampu bekerja">Sementara Total tak mampu bekerja</option>
                                <option value="Sementara Sebagian tak mampu bekerja">Sementara Sebagian tak mampu bekerja</option>
                                <option value="Tetap Sebagian tak mampu bekerja">Tetap Sebagian tak mampu bekerja</option>
                                <option value="Tetap Total tak mampu bekerja">Tetap Total tak mampu bekerja</option>
                                <option value="Meninggal">Meninggal</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="waktu_hilang" class="block text-sm font-medium text-gray-700">Jumlah waktu hilang (hari)</label>
                            <input type="number" id="waktu_hilang" name="waktu_hilang" class="mt-1 block w-1/4 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Biaya Perawatan</label>
                            <div id="biaya-container" class="mt-2 space-y-2"></div>
                            <button type="button" class="mt-2 px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500" onclick="tambahBiaya()">Tambah Biaya</button>
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
                                <input class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" type="checkbox" id="apd_wajib_{{ $key }}" onchange="toggleApdDetails('{{ $key }}')">
                                <label class="ml-2 block text-sm font-bold text-gray-900" for="apd_wajib_{{ $key }}">{{ $label }} Diwajibkan</label>
                            </div>
                            <div id="apd_details_{{ $key }}" class="apd-details hidden pl-6 mt-3">
                                @if ($key == 'sarung_tangan')
                                <div class="mb-2">
                                    <label for="apd_keterangan_{{ $key }}" class="text-sm font-medium text-gray-700">Keterangan (Jenis/Spesifikasi):</label>
                                    <input type="text" id="apd_keterangan_{{ $key }}" name="apd_keterangan_{{ $key }}" class="mt-1 block w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" disabled>
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
                                <input class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300" type="radio" name="sebab_utama" id="sebab_a_lain">
                                <label class="ml-2 text-sm text-gray-900" for="sebab_a_lain">{{ count($tindakanBerbahaya) + 1 }}. Lain-lain, sebutkan:</label>
                            </div>
                            <input type="text" id="sebab_a_lain_input" name="sebab_a_lain_input" class="mt-1 block w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 disabled:bg-gray-200" disabled>
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
                                <input class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300" type="radio" name="sebab_utama" id="sebab_b_lain">
                                <label class="ml-2 text-sm text-gray-900" for="sebab_b_lain">{{ count($keadaanBerbahaya) + 1 }}. Lain-lain, sebutkan:</label>
                            </div>
                            <input type="text" id="sebab_b_lain_input" name="sebab_b_lain_input" class="mt-1 block w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 disabled:bg-gray-200" disabled>
                        </div>

                        <h4 class="font-bold text-xl mt-8 pt-4 border-t mb-4">Analisa Masalah</h4>
                        <div class="mb-4">
                            <textarea id="analisa_masalah" name="analisa_masalah" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" rows="6"></textarea>
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
                        <button type="button" class="px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500" onclick="tambahSaranPerbaikan()">Tambah Saran</button>

                        <h4 class="font-bold text-xl mt-8 pt-4 border-t mb-4">Tindakan Pencegahan</h4>
                        <div class="mb-4">
                            <textarea id="tindakan_pencegahan" name="tindakan_pencegahan" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" rows="6"></textarea>
                        </div>

                        <h4 class="font-bold text-xl mt-8 pt-4 border-t mb-4">Rekomendasi</h4>
                        <div class="mb-4">
                            <textarea id="rekomendasi" name="rekomendasi" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" rows="6"></textarea>
                        </div>

                        <button type="submit" class="w-full mt-8 py-3 px-4 border border-transparent rounded-md shadow-sm text-lg font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Submit Laporan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="{{ asset('js/tinymce/tinymce.min.js') }}"></script>
    <script>
        // --- Seluruh JavaScript Anda tidak perlu diubah ---
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof tinymce !== 'undefined') {
                tinymce.init({
                    selector: 'textarea#uraian_kejadian',
                    plugins: 'autolink lists link charmap preview anchor',
                    toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent',
                    height: 300,
                    promotion: false,
                    license_key: 'gpl'
                });
            } else {
                console.error('TinyMCE is not loaded. Please check the script path.');
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
        });

        document.getElementById('date').value = new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
        function hitungUsia() {const tgl=document.getElementById('tanggal_lahir').value;if(tgl){const birthDate=new Date(tgl);const today=new Date();let age=today.getFullYear()-birthDate.getFullYear();const m=today.getMonth()-birthDate.getMonth();if(m<0||(m===0&&today.getDate()<birthDate.getDate())){age--}document.getElementById('usia').value=age+" tahun"}}
        function hitungMasaKerja() {const tgl=document.getElementById('tanggal_masuk').value;if(tgl){const startDate=new Date(tgl);const today=new Date();let years=today.getFullYear()-startDate.getFullYear();let months=today.getMonth()-startDate.getMonth();if(months<0||(months===0&&today.getDate()<startDate.getDate())){years--;months+=12}document.getElementById('masa_kerja').value=years+" tahun "+months+" bulan"}}
        
        let biayaCount=0;
        function tambahBiaya(){
            biayaCount++;
            const c=document.getElementById('biaya-container');
            const n=document.createElement('div');
            n.className = 'flex space-x-2';
            n.id='biaya_item_'+biayaCount;
            n.innerHTML=`<span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">Rp</span><input type="number" name="biaya_harga[]" class="flex-1 block w-full rounded-none border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="Harga"><input type="text" name="biaya_kategori[]" class="flex-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="Kategori"><button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-r-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" onclick="hapusBiaya(${biayaCount})">Hapus</button>`;
            c.appendChild(n);
        }
        function hapusBiaya(id){document.getElementById('biaya_item_'+id).remove()}
        
        function toggleApdDetails(key){const c=document.getElementById(`apd_wajib_${key}`);const d=document.getElementById(`apd_details_${key}`);const k=document.getElementById(`apd_keterangan_${key}`);const r=document.getElementsByName(`apd_dipakai_${key}`);if(c.checked){d.classList.remove('hidden');if(k){k.disabled=false}r.forEach(rad=>rad.disabled=false)}else{d.classList.add('hidden');if(k){k.disabled=true;k.value=''}r.forEach(rad=>{rad.disabled=true;rad.checked=false})}}

        function tambahSaranPerbaikan() {
            const container = document.getElementById('perbaikan-container');
            const newIndex = container.rows.length;
            const newRow = container.insertRow(newIndex);
            newRow.id = 'perbaikan_item_' + newIndex;
            newRow.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">${newIndex + 1}</td>
                <td class="px-6 py-4"><input type="text" name="perbaikan_tindakan[]" class="w-full border-gray-300 rounded-md shadow-sm" placeholder="Uraian tindakan perbaikan"></td>
                <td class="px-6 py-4"><input type="text" name="perbaikan_pic[]" class="w-full border-gray-300 rounded-md shadow-sm" placeholder="Nama PIC"></td>
                <td class="px-6 py-4"><input type="date" name="perbaikan_due_date[]" class="w-full border-gray-300 rounded-md shadow-sm"></td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium"><button type="button" class="text-red-600 hover:text-red-900" onclick="hapusSaranPerbaikan('perbaikan_item_${newIndex}')">Hapus</button></td>
            `;
        }

        function hapusSaranPerbaikan(rowId) {
            document.getElementById(rowId).remove();
            updateNomorSaran();
        }

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