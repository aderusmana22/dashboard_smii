<x-app-layout>
    {{-- Awalnya ada tag <html> di sini, ini tidak diperlukan dalam komponen Blade --}}
    @section('title')
        Daftar Laporan Kecelakaan
    @endsection

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Kode CSS untuk dark mode tetap sama --}}
    <style>
        .dark-skin .bg-white { background-color: rgb(31 41 55 / 1); }
        .dark-skin .bg-gray-50 { background-color: rgb(55 65 81 / 1); }
        .dark-skin .bg-gray-100 { background-color: rgb(55 65 81 / 1); }
        .dark-skin .divide-gray-200> :not([hidden])~ :not([hidden]) { border-color: rgb(55 65 81 / 1); }
        .dark-skin .text-gray-900 { color: rgb(249 250 251 / 1); }
        .dark-skin .text-gray-800 { color: rgb(229 231 235 / 1); }
        .dark-skin .text-gray-700 { color: rgb(209 213 219 / 1); }
        .dark-skin .text-gray-500 { color: rgb(209 213 219 / 1); }
        .dark-skin .border-gray-300 { border-color: rgb(75 85 99 / 1); }
        .dark-skin .text-indigo-600 { color: #818cf8; }
        .dark-skin .text-indigo-600:hover { color: #a5b4fc; }
        .dark-skin .text-red-600 { color: #f87171; }
        .dark-skin .text-red-600:hover { color: #fca5a5; }
        .dark-skin .modal-cancel-button { background-color: rgb(75 85 99 / 1); color: rgb(229 231 235 / 1); }
        .dark-skin .modal-cancel-button:hover { background-color: rgb(107 114 128 / 1); }
    </style>

    <div class="py-10">
        <div class="mx-auto max-w-9xl sm:px-6 lg:px-8">

            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <h2 class="text-2xl font-bold text-gray-800 sm:text-3xl">
                    Daftar Laporan Kecelakaan
                </h2>
                <a href="{{ route('accidents-report.create') }}"
                    class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25">
                    <svg class="w-4 h-4 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Buat Laporan Baru
                </a>
            </div>

            <div id="notification-container" class="mb-4"></div>

            <div class="mb-6 bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-gray-200">
                    <h3 class="mb-4 text-lg font-semibold text-gray-700">Filter Laporan</h3>
                    <form id="search-form" class="flex flex-col gap-4 md:flex-row md:items-end">
                        {{-- Bagian form filter tetap sama --}}
                        <div class="flex-1 min-w-0">
                            <label for="search_nomor_form" class="block text-sm font-medium text-gray-700">No. Form</label>
                            <input type="text" name="nomor_form" id="search_nomor_form" class="bg-white block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>
                        <div class="flex-1 min-w-0">
                            <label for="search_nama_korban" class="block text-sm font-medium text-gray-700">Nama Korban</label>
                            <input type="text" name="nama_korban" id="search_nama_korban" class="bg-white block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>
                        <div class="flex-1 min-w-0">
                            <label for="search_status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" id="search_status" class="bg-white block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Semua Status</option>
                                <option value="pending_manager_hse">Pending Manager HSE</option>
                                <option value="pending_manager_terkait">Pending Manager Terkait</option>
                                <option value="pending_dept_head">Pending Dept Head</option>
                                <option value="pending_gm">Pending GM</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="revised">Revised</option>
                            </select>
                        </div>
                        <div class="flex-1 min-w-0">
                            <label for="search_date_start" class="block text-sm font-medium text-gray-700">Rentang Tanggal</label>
                            <div class="flex items-center mt-1 space-x-2">
                                <input type="date" name="date_start" id="search_date_start" class="bg-white block w-full border-gray-300 rounded-md shadow-sm">
                                <span class="text-gray-500">-</span>
                                <input type="date" name="date_end" id="search_date_end" class="bg-white block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" id="reset-button" class="w-full px-4 py-2 text-white bg-gray-600 rounded-md md:w-auto hover:bg-gray-700">Reset</button>
                            <button type="submit" class="w-full px-4 py-2 text-white bg-indigo-600 rounded-md md:w-auto hover:bg-indigo-700">Cari</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-gray-200">
                    <div class="overflow-x-auto">
                        <table id="reports-table" class="w-full table-bordered table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th>No. Form</th>
                                    <th>Tanggal</th>
                                    <th>Nama Korban</th>
                                    <th>Status</th>
                                    <th>Lokasi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL REJECT DIHAPUS DARI SINI -->

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

        <script>
            var dt_jQuery = jQuery.noConflict(true);

            dt_jQuery(document).ready(function ($) {

                $.ajaxSetup({
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
                });

                const table = $('#reports-table').DataTable({
                    dom: '<"row mb-3"<"col-12 d-flex justify-content-between"l f>>' +
                        '<"row"<"col-12"tr>>' +
                        '<"row mt-3"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('accidents-report.data') }}",
                        data: function (d) {
                            d.nomor_form = $('#search_nomor_form').val();
                            d.nama_korban = $('#search_nama_korban').val();
                            d.status = $('#search_status').val();
                            d.date_start = $('#search_date_start').val();
                            d.date_end = $('#search_date_end').val();
                        }
                    },
                    columns: [
                        { data: 'nomor_form', name: 'nomor_form', defaultContent: '-' },
                        {
                            data: 'date', name: 'date', render: function (data) {
                                if (!data) return '-';
                                return new Date(data).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
                            }
                        },
                        { data: 'nama_korban', name: 'nama_korban' },
                        {
                            data: 'approval_status', name: 'approval_statuses.status', orderable: true, searchable: false, render: function (data, type, row) {
                                const status = row.approval_status ? row.approval_status.status : 'draft';
                                let colorClass = 'bg-gray-100 text-gray-800';
                                if (status === 'approved') colorClass = 'bg-green-100 text-green-800';
                                else if (status === 'rejected') colorClass = 'bg-red-100 text-red-800';
                                else if (status.startsWith('pending_')) colorClass = 'bg-yellow-100 text-yellow-800';
                                else if (status === 'revised') colorClass = 'bg-blue-100 text-blue-800';
                                const statusText = status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                                return `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${colorClass}">${statusText}</span>`;
                            }
                        },
                        {
                            data: 'lokasi_kecelakaan', name: 'lokasi_kecelakaan', render: function (data) {
                                return data && data.length > 30 ? data.substr(0, 30) + '...' : (data || '-');
                            }
                        },
                        {
                            data: 'nomor_form',
                            name: 'nomor_form',
                            orderable: false,
                            searchable: false,
                            // --- PERUBAHAN UTAMA DI SINI ---
                            render: function (data, type, row) {
                                let detailUrl = "{{ route('accidents-report.show', ':nomor_form') }}".replace(':nomor_form', data);
                                // Hanya mengembalikan link Detail dan menghapus semua logika approve/reject
                                return `<a href="${detailUrl}" class="text-indigo-600 hover:text-indigo-900">Detail</a>`;
                            }
                        }
                    ]
                });

                // Event handler untuk form pencarian dan reset tetap ada
                $('#search-form').on('submit', function (e) { e.preventDefault(); table.draw(); });
                $('#reset-button').on('click', function () { $('#search-form')[0].reset(); table.draw(); });
                
                // --- SEMUA EVENT HANDLER UNTUK APPROVE & REJECT DIHAPUS ---
            });

            // Fungsi notifikasi tetap ada karena mungkin masih berguna
            function showNotification(message, type = 'success') {
                const container = dt_jQuery('#notification-container');
                const typeClasses = type === 'success'
                    ? 'bg-green-100 border-green-400 text-green-700'
                    : 'bg-red-100 border-red-400 text-red-700';
                const notificationHtml = `<div class="${typeClasses} px-4 py-3 rounded-lg relative border" role="alert"><span class="block sm:inline">${message}</span></div>`;
                container.html(notificationHtml).fadeIn();
                setTimeout(() => { container.fadeOut(() => container.empty()); }, 5000);
            }

            // --- FUNGSI UNTUK MODAL REJECT (openRejectModal & closeRejectModal) DIHAPUS ---

        </script>
    @endpush
</x-app-layout>