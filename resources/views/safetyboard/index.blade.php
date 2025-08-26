<x-app-layout>
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    @section('title')
    Daftar Laporan Kecelakaan
    @endsection

    {{-- CSS untuk DataTables (Bootstrap 5 styling) --}}
    @push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    {{-- Meta tag untuk CSRF token agar bisa diakses oleh semua skrip AJAX --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

     <style>
        /* Mengganti warna SVG default pada paginasi DataTables untuk dark mode */
        .dark .page-item.disabled .page-link,
        .dark .page-item .page-link {
            background-color: transparent;
        }

        /* Warna teks untuk info tabel */
        .dark .dataTables_info {
            color: #9ca3af; /* text-gray-400 */
        }
    </style>
    @endpush

    <div class="py-10">
        <div class="mx-auto max-w-9xl sm:px-6 lg:px-8"> {{-- Konsistensi: max-w-7xl lebih umum dan terlihat bagus --}}

            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <h2 class="text-2xl font-bold text-gray-800 sm:text-3xl">
                    Daftar Laporan Kecelakaan
                </h2>
                <a href="{{ route('accidents-report.create') }}" class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25">
                    <svg class="w-4 h-4 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Buat Laporan Baru
                </a>
            </div>

            {{-- Notifikasi Dinamis dari AJAX akan muncul di sini --}}
            <div id="notification-container" class="mb-4"></div>

            <!-- Filter dan Pencarian -->
            <div class="mb-6 bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="mb-4 text-lg font-semibold text-gray-700">Filter Laporan</h3>
                    
                    {{-- PERBAIKAN UTAMA: Menggunakan Flexbox untuk tata letak satu baris di desktop --}}
                    <form id="search-form" class="flex flex-col gap-4 md:flex-row md:items-end">
                        
                        {{-- Filter No. Form --}}
                        <div class="flex-1 min-w-0">
                            <label for="search_nomor_form" class="block text-sm font-medium text-gray-700">No. Form</label>
                            <input type="text" name="nomor_form" id="search_nomor_form" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>

                        {{-- Filter Nama Korban --}}
                        <div class="flex-1 min-w-0">
                            <label for="search_nama_korban" class="block text-sm font-medium text-gray-700">Nama Korban</label>
                            <input type="text" name="nama_korban" id="search_nama_korban" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>

                        {{-- Filter Status --}}
                        <div class="flex-1 min-w-0">
                            <label for="search_status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" id="search_status" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
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

                        {{-- Filter Rentang Tanggal --}}
                        <div class="flex-1 min-w-0">
                            <label for="search_date_start" class="block text-sm font-medium text-gray-700">Rentang Tanggal</label>
                            <div class="flex items-center mt-1 space-x-2">
                                <input type="date" name="date_start" id="search_date_start" class="block w-full border-gray-300 rounded-md shadow-sm">
                                <span class="text-gray-500">-</span>
                                <input type="date" name="date_end" id="search_date_end" class="block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                        </div>

                        {{-- Tombol Aksi --}}
                        <div class="flex items-center gap-2">
                            <button type="button" id="reset-button" class="w-full px-4 py-2 text-white bg-gray-600 rounded-md md:w-auto hover:bg-gray-700">Reset</button>
                            <button type="submit" class="w-full px-4 py-2 text-white bg-indigo-600 rounded-md md:w-auto hover:bg-indigo-700">Cari</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
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
                            <tbody>
                                {{-- Isi tabel akan dimuat oleh DataTables secara dinamis --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Penolakan -->
    <div id="rejectModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block w-full max-w-lg overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle">
                <form id="rejectForm" action="" method="POST">
                    @csrf
                    <div class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="w-full mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">
                                    Tolak Laporan Kecelakaan
                                </h3>
                                <div class="mt-2">
                                    <label for="rejection_reason" class="block text-sm font-medium text-gray-700">Alasan Penolakan (Wajib diisi)</label>
                                    <textarea id="rejection_reason" name="rejection_reason" rows="4" class="block w-full mt-1 border border-gray-300 rounded-md shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500" required minlength="10"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="px-4 py-3 bg-gray-50 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Tolak Laporan
                        </button>
                        <button type="button" class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm" onclick="closeRejectModal()">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    {{-- jQuery dan DataTables JS dari CDN --}}
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            // Setup AJAX global untuk mengirim CSRF token secara otomatis
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Inisialisasi DataTables
            const table = $('#reports-table').DataTable({
                // --- PERBAIKAN: Menambahkan opsi 'dom' untuk menata ulang elemen kontrol ---
                dom:
                    '<"row mb-3"' +
                    '<"col-12 d-flex justify-content-between"' +
                    'l' + // 'l' adalah Length changing (Show entries)
                    'f' + // 'f' adalah Filtering (Search)
                    '>>' +
                    '<"row"<"col-12"tr>>' + // 't' adalah table, 'r' adalah processing
                    '<"row mt-3"' +
                    '<"col-sm-12 col-md-5"i>' + // 'i' adalah info
                    '<"col-sm-12 col-md-7"p>' + // 'p' adalah pagination
                    '>',
                // --------------------------------------------------------------------------
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
                    { data: 'date', name: 'date', render: function(data) {
                        if (!data) return '-';
                        return new Date(data).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
                    }},
                    { data: 'nama_korban', name: 'nama_korban' },
                    { data: 'approval_status', name: 'approval_statuses.status', orderable: true, searchable: false, render: function(data, type, row) {
                        const status = data ? data.status : 'draft';
                        let colorClass = 'bg-gray-100 text-gray-800';
                        if (status === 'approved') colorClass = 'bg-green-100 text-green-800';
                        else if (status === 'rejected') colorClass = 'bg-red-100 text-red-800';
                        else if (status.startsWith('pending_')) colorClass = 'bg-yellow-100 text-yellow-800';

                        const statusText = status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                        return `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${colorClass}">${statusText}</span>`;
                    }},
                    { data: 'lokasi_kecelakaan', name: 'lokasi_kecelakaan', render: function(data) {
                        return data && data.length > 30 ? data.substr(0, 30) + '...' : (data || '-');
                    }},
                    { data: 'id', name: 'id', orderable: false, searchable: false, render: function(data, type, row) {
                        let detailUrl = "{{ route('accidents-report.show', ':id') }}".replace(':id', data);
                        let actions = `<a href="${detailUrl}" class="mr-3 text-indigo-600 hover:text-indigo-900">Detail</a>`;

                        const approvalStatus = row.approval_status;
                        const currentUserId = {{ Auth::id() }};

                        if (approvalStatus && approvalStatus.current_approver_id == currentUserId) {
                            let approveUrl = "{{ route('accidents-report.approve', ':id') }}".replace(':id', data);
                            let rejectUrl = "{{ route('accidents-report.reject', ':id') }}".replace(':id', data);

                            actions += `
                                <button type="button" class="text-green-600 hover:text-green-900 approve-btn" data-url="${approveUrl}">Approve</button>
                                <span class="mx-1 text-gray-300">|</span>
                                <button type="button" class="text-red-600 hover:text-red-900 reject-btn" data-url="${rejectUrl}">Reject</button>
                            `;
                        }
                        return actions;
                    }}
                ]
            });

            // ... sisa kode JavaScript Anda (event listener, dll.) tidak perlu diubah ...
            // Event listener untuk form pencarian
            $('#search-form').on('submit', function(e) {
                e.preventDefault();
                table.draw();
            });

            // Event listener untuk tombol reset
            $('#reset-button').on('click', function() {
                $('#search-form')[0].reset();
                table.draw();
            });

            // Event delegation untuk tombol approve
            $('#reports-table tbody').on('click', '.approve-btn', function() {
                if (!confirm('Anda yakin ingin menyetujui laporan ini?')) return;

                const url = $(this).data('url');
                $.ajax({
                    url: url,
                    type: 'POST',
                    success: function(response) {
                        showNotification(response.message, 'success');
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        const errorMsg = xhr.responseJSON?.message || 'Terjadi kesalahan saat menyetujui laporan.';
                        showNotification(errorMsg, 'error');
                    }
                });
            });

            // Event delegation untuk tombol reject
            $('#reports-table tbody').on('click', '.reject-btn', function() {
                const url = $(this).data('url');
                openRejectModal(url);
            });

            // Submit form modal reject
            $('#rejectForm').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        showNotification(response.message, 'success');
                        closeRejectModal();
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        let errorMsg = 'Gagal menolak laporan.';
                        if (xhr.responseJSON?.errors?.rejection_reason) {
                            errorMsg += '<br>' + xhr.responseJSON.errors.rejection_reason.join('<br>');
                        } else if (xhr.responseJSON?.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        showNotification(errorMsg, 'error');
                    }
                });
            });
        });

        // ... sisa fungsi JavaScript Anda (showNotification, openRejectModal, etc.) tidak perlu diubah ...
        function showNotification(message, type = 'success') {
            const container = $('#notification-container');
            let bgColor, borderColor, textColor;

            if (type === 'success') {
                bgColor = 'bg-green-100';
                borderColor = 'border-green-400';
                textColor = 'text-green-700';
            } else {
                bgColor = 'bg-red-100';
                borderColor = 'border-red-400';
                textColor = 'text-red-700';
            }

            const notificationHtml = `
                <div class="${bgColor} ${borderColor} ${textColor} px-4 py-3 rounded-lg relative border" role="alert">
                    <span class="block sm:inline">${message}</span>
                </div>
            `;

            container.html(notificationHtml).fadeIn();

            setTimeout(() => {
                container.fadeOut(() => container.empty());
            }, 5000);
        }

        function openRejectModal(actionUrl) {
            const modal = $('#rejectModal');
            const form = $('#rejectForm');
            form.attr('action', actionUrl);
            form[0].reset();
            modal.removeClass('hidden');
        }

        function closeRejectModal() {
            $('#rejectModal').addClass('hidden');
        }
    </script>
    @endpush
</x-app-layout>