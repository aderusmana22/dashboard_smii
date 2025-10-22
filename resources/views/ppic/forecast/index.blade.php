<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Import & View Forecast Data') }}
        </h2>
    </x-slot>

    <div class="py-12">
        {{-- Kontainer utama sekarang tidak memiliki kelas, membiarkan anak-nya yang mengatur padding --}}
        <div>
            {{-- 
                ===================================================================
                PERBAIKAN 1: Menambahkan padding horizontal (px-4 sm:px-6 lg:px-8)
                Ini memberikan jarak dari tepi layar tanpa membatasi lebar.
                ===================================================================
            --}}
            <div class="flex flex-col lg:flex-row gap-8 px-4 sm:px-6 lg:px-8">

                <!-- =================================================================== -->
                <!-- KOLOM KIRI: UPLOAD & PETUNJUK (Lebar 1/3 di layar besar) -->
                <!-- =================================================================== -->
                <div class="w-full lg:w-1/3 flex flex-col gap-8">

                    <!-- KARTU UPLOAD FILE (Tidak ada perubahan) -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <div class="text-center">
                                <div class="mb-6">
                                    <svg class="mx-auto h-16 w-16 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M14 2H6C4.9 2 4 2.9 4 4V20C4 21.1 4.9 22 6 22H18C19.1 22 20 21.1 20 20V8L14 2ZM18 20H6V4H13V9H18V20ZM11.2 18.4L9.8 17L12.6 14.2L9.8 11.4L11.2 10L15.4 14.2L11.2 18.4Z"/>
                                    </svg>
                                    <h2 class="mt-2 text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">
                                        IMPORT Forecast
                                    </h2>
                                </div>
                                <form id="import-form" action="{{ route('ppic.forecast.import') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div id="drop-area" class="relative flex flex-col items-center justify-center w-full p-8 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors duration-300">
                                        <div class="text-center">
                                            <p class="mb-2 text-gray-600"><span class="font-semibold">Drag and drop file here</span></p>
                                            <p class="text-xs text-gray-500">or</p>
                                        </div>
                                        <label for="file-upload" class="relative cursor-pointer mt-4 px-5 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                                            <span>Browse File</span>
                                            <input id="file-upload" name="file" type="file" class="sr-only" required>
                                        </label>
                                        <div id="file-name" class="mt-4 text-sm text-gray-800 font-medium"></div>
                                    </div>
                                    <button type="submit" class="mt-6 w-full px-4 py-3 bg-green-600 text-white font-semibold rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-transform transform hover:scale-105">
                                        Import
                                    </button>
                                </form>
                                <div class="mt-6">
                                     <a href="{{ route('ppic.forecast.template') }}" class="inline-flex items-center px-6 py-2 bg-gray-200 text-gray-700 font-medium rounded-md hover:bg-gray-300 transition-colors">
                                        <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                        </svg>
                                        Download Template
                                    </a>
                                </div>
                                <div id="message-area" class="mt-4 text-left"></div>
                            </div>
                        </div>
                    </div>

                    <!-- KARTU PETUNJUK PENGISIAN (Teks disederhanakan) -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-medium mb-4 text-gray-900">Petunjuk Pengisian Template</h3>
                            <div class="text-sm text-gray-700 space-y-2">
                                <p>1. Gunakan template yang di-download dari tombol di atas.</p>
                                <p>2. Jangan mengubah <strong>Baris 1 (Judul)</strong> dan <strong>Baris 2 (Header)</strong>.</p>
                                <p>3. Mulai isi data dari <strong>Baris 3</strong> dan pastikan formatnya benar:</p>
                                <ul class="list-disc list-inside pl-4 space-y-1 mt-2">
                                    <li><code class="font-mono bg-gray-200 px-1 rounded">month</code>: Bisa berupa angka (<strong>1-12</strong>) atau teks (<strong>Januari, Feb, etc.</strong>).</li>
                                    <li><code class="font-mono bg-gray-200 px-1 rounded">unit</code> & <code class="font-mono bg-gray-200 px-1 rounded">tonage</code>: Gunakan titik (<strong>.</strong>) untuk desimal, bukan koma.</li>
                                </ul>
                            </div>

                            <h3 class="text-lg font-medium mt-6 mb-2 text-gray-900">Contoh Pengisian Data</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm border border-gray-300">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="px-4 py-2 border border-gray-300">item_number</th>
                                            <th class="px-4 py-2 border border-gray-300">month</th>
                                            <th class="px-4 py-2 border border-gray-300">year</th>
                                            <th class="px-4 py-2 border border-gray-300">unit</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white">
                                        <tr>
                                            <td class="px-4 py-2 border border-gray-300 font-mono">FG-00123</td>
                                            <td class="px-4 py-2 border border-gray-300 font-mono">Januari</td>
                                            <td class="px-4 py-2 border border-gray-300 font-mono">2025</td>
                                            <td class="px-4 py-2 border border-gray-300 font-mono">1500.50</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-2 border border-gray-300 font-mono">FG-00456</td>
                                            <td class="px-4 py-2 border border-gray-300 font-mono">12</td>
                                            <td class="px-4 py-2 border border-gray-300 font-mono">2025</td>
                                            <td class="px-4 py-2 border border-gray-300 font-mono">500</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- =================================================================== -->
                <!-- KOLOM KANAN: TABEL DATA (Lebar 2/3 di layar besar) -->
                <!-- =================================================================== -->
                {{-- 
                    ===================================================================
                    PERBAIKAN 2: Menambahkan kelas "lg:w-2/3"
                    Ini membuat kolom ini mengambil sisa 2/3 lebar di layar besar.
                    ===================================================================
                --}}
            <div class="w-full lg:w-2/3">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg h-full">
        <div class="p-6 bg-white border-b border-gray-200">
            <h3 class="text-lg font-medium mb-4">Daftar Data Forecast Terimpor</h3>
            <div class="overflow-x-auto">
                <table id="forecast-table" class="w-full table-auto divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Item Number</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Month</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Year</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Unit</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Tonage</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <!-- Data akan diisi oleh AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

            </div>
        </div>
    </div>

    {{-- Kode JavaScript tidak perlu diubah sama sekali --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            function loadData() {
                $.ajax({
                    url: '{{ route("ppic.forecast.data") }}',
                    type: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        let tableBody = $('#forecast-table tbody');
                        tableBody.empty();
                        if (response.data.length > 0) {
                            response.data.forEach(function (item) {
                                tableBody.append(`
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">${item.item_number}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">${item.description}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">${item.month}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">${item.year}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">${item.unit}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">${item.tonage}</td>
                                        </tr>
                                    `);
                            });
                        } else {
                            tableBody.append(`
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">Belum ada data yang diimpor.</td>
                                    </tr>
                                `);
                        }
                    },
                    error: function () {
                        $('#forecast-table tbody').html(`<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">Gagal memuat data.</td></tr>`);
                    }
                });
            }
            loadData();
            $('#import-form').on('submit', function (e) {
                e.preventDefault();
                let formData = new FormData(this);
                let messageArea = $('#message-area');
                messageArea.html('');
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (response) {
                        messageArea.html(`<div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">${response.success}</div>`);
                        $('#import-form')[0].reset();
                        $('#file-name').text('');
                        loadData();
                    },
                    error: function (xhr) {
                        let errorMsg = 'Terjadi kesalahan. Silakan coba lagi.';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMsg = xhr.responseJSON.error;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            errorMsg = Object.values(xhr.responseJSON.errors).join('<br>');
                        }
                        messageArea.html(`<div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">${errorMsg}</div>`);
                    }
                });
            });
            const dropArea = $('#drop-area');
            const fileInput = $('#file-upload');
            const fileNameDisplay = $('#file-name');
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropArea.on(eventName, e => { e.preventDefault(); e.stopPropagation(); });
            });
            ['dragenter', 'dragover'].forEach(eventName => {
                dropArea.on(eventName, () => dropArea.addClass('border-blue-500 bg-blue-50'));
            });
            ['dragleave', 'drop'].forEach(eventName => {
                dropArea.on(eventName, () => dropArea.removeClass('border-blue-500 bg-blue-50'));
            });
            dropArea.on('drop', e => {
                const files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) {
                    fileInput.prop('files', files);
                    fileNameDisplay.text(files[0].name);
                }
            });
            fileInput.on('change', function () {
                if (this.files.length > 0) {
                    fileNameDisplay.text(this.files[0].name);
                }
            });
        });
    </script>
</x-app-layout>