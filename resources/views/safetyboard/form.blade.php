<x-app-layout>
    @section('title')
    Form Laporan Kecelakaan
    @endsection

    @push('styles')
    <style>
        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            padding-top: 1.5rem;
            margin-top: 1.5rem;
            border-top: 1px solid #dee2e6;
        }
        .apd-item-container, .sebab-container {
            background-color: #f8f9fa;
        }
        .apd-details {
            transition: all 0.3s ease-in-out;
        }
        .sebab-container .form-check {
            margin-bottom: 0.5rem;
        }

        /* === CSS BARU UNTUK MERAPIKAN FORM SECARA KONSISTEN === */
        .form-horizontal .row {
            margin-bottom: 1rem;
        }
        .form-horizontal .col-form-label {
            font-weight: 500;
            text-align: left; /* Default untuk mobile */
        }
        @media (min-width: 768px) {
            .form-horizontal .col-form-label {
                text-align: right; /* Rata kanan untuk desktop */
            }
        }
        .form-horizontal .col-form-label {
            margin-bottom: 0;
        }
        /* Menjaga jarak untuk elemen yang tidak menggunakan grid horizontal */
        .form-section {
            margin-bottom: 1rem;
        }
    </style>
    @endpush

    <div class="py-5">
        <div class="container">
            <div class="card shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <h2 class="card-title text-center fw-bold mb-4">FORM LAPORAN KECELAKAAN</h2>

                    <form action="#" method="POST" class="form-horizontal">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="row">
                                    <label for="nomor_form" class="col-md-4 col-form-label">Nomor form :</label>
                                    <div class="col-md-8">
                                        <input type="text" id="nomor_form" name="nomor_form" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <label for="date" class="col-md-4 col-form-label">Date :</label>
                                    <div class="col-md-8">
                                        <input type="text" id="date" name="date" readonly class="form-control bg-light">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h4 class="section-title mb-4">Detail Insiden & Dampak</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row">
                                    <label for="kategori_kecelakaan" class="col-md-4 col-form-label">Kategori Kecelakaan</label>
                                    <div class="col-md-8">
                                        <select id="kategori_kecelakaan" name="kategori_kecelakaan" class="form-select">
                                            <option value="Kerja">Kerja</option>
                                            <option value="Lalu Lintas">Lalu Lintas</option>
                                            <option value="Kebakaran">Kebakaran</option>
                                            <option value="Lain-lain">Lain-lain</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <label for="kategori_dampak" class="col-md-4 col-form-label">Kategori Dampak</label>
                                    <div class="col-md-8">
                                        <select id="kategori_dampak" name="kategori_dampak" class="form-select">
                                            <option value="Ringan">Ringan (Minor)</option>
                                            <option value="Sedang">Sedang (Moderate)</option>
                                            <option value="Berat">Berat (Major)</option>
                                            <option value="Kematian">Kematian (Fatality)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <label for="waktu_kecelakaan" class="col-md-3 col-form-label">Tanggal & Jam Kecelakaan</label>
                            <div class="col-md-9">
                                <input type="datetime-local" id="waktu_kecelakaan" name="waktu_kecelakaan" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <label for="lokasi_kecelakaan" class="col-md-3 col-form-label">Lokasi Kecelakaan</label>
                            <div class="col-md-9">
                                <input type="text" id="lokasi_kecelakaan" name="lokasi_kecelakaan" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row">
                                    <label for="tipe_kecelakaan" class="col-md-4 col-form-label">Tipe Kecelakaan</label>
                                    <div class="col-md-8">
                                        <input type="text" id="tipe_kecelakaan" name="tipe_kecelakaan" class="form-control" placeholder="cth: Terpeleset, Terjatuh">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <label for="bagian_terluka" class="col-md-4 col-form-label">Bagian yang Terluka</label>
                                    <div class="col-md-8">
                                        <input type="text" id="bagian_terluka" name="bagian_terluka" class="form-control" placeholder="cth: Tangan Kanan">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-section">
                            <label for="uraian_kejadian" class="form-label">Uraian Kejadian</label>
                            <textarea id="uraian_kejadian" name="uraian_kejadian" class="form-control" rows="8"></textarea>
                        </div>

                        <h4 class="section-title mb-4">Data Korban</h4>
                        <div class="row">
                            <label for="nama_korban" class="col-md-3 col-form-label">Nama Korban</label>
                            <div class="col-md-9">
                                <input type="text" id="nama_korban" name="nama_korban" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row">
                                    <label for="nik" class="col-md-4 col-form-label">NIK</label>
                                    <div class="col-md-8">
                                        <input type="text" id="nik" name="nik" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <label for="tanggal_lahir" class="col-md-4 col-form-label">Tanggal Lahir</label>
                                    <div class="col-md-8">
                                        <input type="date" id="tanggal_lahir" name="tanggal_lahir" class="form-control" onchange="hitungUsia()">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <label for="usia" class="col-md-3 col-form-label">Usia</label>
                            <div class="col-md-3">
                                <input type="text" id="usia" name="usia" readonly class="form-control bg-light">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row">
                                    <label for="tanggal_masuk" class="col-md-4 col-form-label">Tanggal Masuk Kerja</label>
                                    <div class="col-md-8">
                                        <input type="date" id="tanggal_masuk" name="tanggal_masuk" class="form-control" onchange="hitungMasaKerja()">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <label for="masa_kerja" class="col-md-4 col-form-label">Masa Kerja</label>
                                    <div class="col-md-8">
                                        <input type="text" id="masa_kerja" name="masa_kerja" readonly class="form-control bg-light">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <label for="jabatan" class="col-md-3 col-form-label">Jabatan</label>
                            <div class="col-md-9">
                                <input type="text" id="jabatan" name="jabatan" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <label for="departemen" class="col-md-3 col-form-label">Seksi / Departemen</label>
                            <div class="col-md-9">
                                <input type="text" id="departemen" name="departemen" class="form-control">
                            </div>
                        </div>

                        <h4 class="section-title mb-4">Tindakan Pertolongan & Akibat</h4>
                        <div class="row">
                            <label for="pertolongan" class="col-md-3 col-form-label">Diberikan pertolongan (P3K)</label>
                            <div class="col-md-9">
                                <select id="pertolongan" name="pertolongan" class="form-select">
                                    <option value="Di Tempat Kejadian">Di Tempat Kejadian</option>
                                    <option value="Di Klinik">Di Klinik</option>
                                    <option value="Di Rumah Sakit">Di Rumah Sakit</option>
                                </select>
                            </div>
                        </div>
                        <div class="row align-items-center">
                            <label for="p3k_oleh" class="col-md-3 col-form-label">P3K dilakukan Oleh</label>
                            <div class="col-md-9">
                                <div class="row">
                                    <div class="col-md-8">
                                        <input type="text" id="p3k_oleh" name="p3k_oleh" class="form-control">
                                    </div>
                                    <label for="jam_p3k" class="col-md-1 col-form-label">Jam</label>
                                    <div class="col-md-3">
                                        <input type="time" id="jam_p3k" name="jam_p3k" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <label for="akibat_kecelakaan" class="col-md-3 col-form-label">Akibat Kecelakaan</label>
                            <div class="col-md-9">
                                <select id="akibat_kecelakaan" name="akibat_kecelakaan" class="form-select">
                                    <option value="Sementara Total tak mampu bekerja">Sementara Total tak mampu bekerja</option>
                                    <option value="Sementara Sebagian tak mampu bekerja">Sementara Sebagian tak mampu bekerja</option>
                                    <option value="Tetap Sebagian tak mampu bekerja">Tetap Sebagian tak mampu bekerja</option>
                                    <option value="Tetap Total tak mampu bekerja">Tetap Total tak mampu bekerja</option>
                                    <option value="Meninggal">Meninggal</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <label for="waktu_hilang" class="col-md-3 col-form-label">Jumlah waktu hilang (hari)</label>
                            <div class="col-md-3">
                                <input type="number" id="waktu_hilang" name="waktu_hilang" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label">Biaya Perawatan</label>
                            <div class="col-md-9">
                                <div id="biaya-container"></div>
                                <button type="button" class="btn btn-secondary mt-2" onclick="tambahBiaya()">Tambah Biaya</button>
                            </div>
                        </div>

                        <h4 class="section-title mb-4">Alat Pelindung Diri (APD)</h4>
                        {{-- Bagian APD sengaja tidak diubah ke form horizontal karena strukturnya unik --}}
                        @php
                            $apds = [
                                'sarung_tangan' => 'Sarung Tangan', 'sepatu' => 'Sepatu Keselamatan', 'helm' => 'Helm',
                                'masker' => 'Masker', 'kacamata' => 'Kaca mata', 'celemek' => 'Celemek',
                                'kedok' => 'Kedok pelindung Muka', 'hairnet' => 'Hair Net'
                            ];
                        @endphp
                        @foreach ($apds as $key => $label)
                        <div class="apd-item-container border p-3 mb-3 rounded">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="apd_wajib_{{ $key }}" onchange="toggleApdDetails('{{ $key }}')">
                                <label class="form-check-label fw-bold" for="apd_wajib_{{ $key }}">{{ $label }} Diwajibkan</label>
                            </div>
                            <div id="apd_details_{{ $key }}" class="apd-details d-none ps-4 mt-3">
                                @if ($key == 'sarung_tangan')
                                <div class="mb-2">
                                    <label for="apd_keterangan_{{ $key }}" class="form-label form-label-sm">Keterangan (Jenis/Spesifikasi):</label>
                                    <input type="text" id="apd_keterangan_{{ $key }}" name="apd_keterangan_{{ $key }}" class="form-control form-control-sm" disabled>
                                </div>
                                @endif
                                <div>
                                    <label class="form-label form-label-sm">Apakah Dipakai oleh Korban?</label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="apd_dipakai_{{ $key }}" id="apd_dipakai_{{ $key }}_ya" value="ya" disabled>
                                        <label class="form-check-label" for="apd_dipakai_{{ $key }}_ya">Ya</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="apd_dipakai_{{ $key }}" id="apd_dipakai_{{ $key }}_tidak" value="tidak" disabled>
                                        <label class="form-check-label" for="apd_dipakai_{{ $key }}_tidak">Tidak</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach

                        <h4 class="section-title mb-4">Analisa Sebab Utama Kecelakaan</h4>
                        {{-- Bagian ini juga tidak diubah karena merupakan daftar pilihan --}}
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
                        <div class="sebab-container border p-3 mb-3 rounded">
                            <p class="fw-bold">A. Tindakan Berbahaya (Unsafe Human Act)</p>
                            @foreach ($tindakanBerbahaya as $index => $sebab)
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="sebab_utama" id="sebab_a_{{ $index }}" value="A - {{ $sebab }}">
                                <label class="form-check-label" for="sebab_a_{{ $index }}">{{ $loop->iteration }}. {{ $sebab }}</label>
                            </div>
                            @endforeach
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="sebab_utama" id="sebab_a_lain">
                                <label class="form-check-label" for="sebab_a_lain">{{ count($tindakanBerbahaya) + 1 }}. Lain-lain, sebutkan:</label>
                                <input type="text" id="sebab_a_lain_input" name="sebab_a_lain_input" class="form-control form-control-sm mt-1" disabled>
                            </div>
                        </div>
                        <div class="sebab-container border p-3 mb-3 rounded">
                            <p class="fw-bold">B. Keadaan Berbahaya (Unsafe Condition)</p>
                            @foreach ($keadaanBerbahaya as $index => $sebab)
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="sebab_utama" id="sebab_b_{{ $index }}" value="B - {{ $sebab }}">
                                <label class="form-check-label" for="sebab_b_{{ $index }}">{{ $loop->iteration }}. {{ $sebab }}</label>
                            </div>
                            @endforeach
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="sebab_utama" id="sebab_b_lain">
                                <label class="form-check-label" for="sebab_b_lain">{{ count($keadaanBerbahaya) + 1 }}. Lain-lain, sebutkan:</label>
                                <input type="text" id="sebab_b_lain_input" name="sebab_b_lain_input" class="form-control form-control-sm mt-1" disabled>
                            </div>
                        </div>

                        <h4 class="section-title mb-4">Analisa Masalah</h4>
                        <div class="form-section">
                            <textarea id="analisa_masalah" name="analisa_masalah" class="form-control" rows="6"></textarea>
                        </div>

                        <h4 class="section-title mb-4">Saran Perbaikan</h4>
                        <div class="table-responsive mb-2">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 5%;" class="text-center">No</th>
                                        <th>Tindakan Perbaikan</th>
                                        <th style="width: 20%;">PIC</th>
                                        <th style="width: 20%;">Due Date</th>
                                        <th style="width: 10%;" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="perbaikan-container"></tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-secondary" onclick="tambahSaranPerbaikan()">Tambah Saran</button>

                        <h4 class="section-title mb-4">Tindakan Pencegahan</h4>
                        <div class="form-section">
                            <textarea id="tindakan_pencegahan" name="tindakan_pencegahan" class="form-control" rows="6"></textarea>
                        </div>

                        <h4 class="section-title mb-4">Rekomendasi</h4>
                        <div class="form-section">
                            <textarea id="rekomendasi" name="rekomendasi" class="form-control" rows="6"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mt-5 py-2 fs-5">Submit Laporan</button>
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
            n.classList.add('input-group','mb-2');
            n.id='biaya_item_'+biayaCount;
            n.innerHTML=`<span class="input-group-text">Rp</span><input type="number" name="biaya_harga[]" class="form-control" placeholder="Harga"><input type="text" name="biaya_kategori[]" class="form-control" placeholder="Kategori"><button type="button" class="btn btn-danger" onclick="hapusBiaya(${biayaCount})">Hapus</button>`;
            c.appendChild(n);
        }
        function hapusBiaya(id){document.getElementById('biaya_item_'+id).remove()}
        
        function toggleApdDetails(key){const c=document.getElementById(`apd_wajib_${key}`);const d=document.getElementById(`apd_details_${key}`);const k=document.getElementById(`apd_keterangan_${key}`);const r=document.getElementsByName(`apd_dipakai_${key}`);if(c.checked){d.classList.remove('d-none');if(k){k.disabled=false}r.forEach(rad=>rad.disabled=false)}else{d.classList.add('d-none');if(k){k.disabled=true;k.value=''}r.forEach(rad=>{rad.disabled=true;rad.checked=false})}}

        function tambahSaranPerbaikan() {
            const container = document.getElementById('perbaikan-container');
            const newIndex = container.rows.length;
            const newRow = container.insertRow(newIndex);
            newRow.id = 'perbaikan_item_' + newIndex;
            newRow.innerHTML = `
                <td class="text-center">${newIndex + 1}</td>
                <td><input type="text" name="perbaikan_tindakan[]" class="form-control" placeholder="Uraian tindakan perbaikan"></td>
                <td><input type="text" name="perbaikan_pic[]" class="form-control" placeholder="Nama PIC"></td>
                <td><input type="date" name="perbaikan_due_date[]" class="form-control"></td>
                <td class="text-center"><button type="button" class="btn btn-danger btn-sm" onclick="hapusSaranPerbaikan('perbaikan_item_${newIndex}')">Hapus</button></td>
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