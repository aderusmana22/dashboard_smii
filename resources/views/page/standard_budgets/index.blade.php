<x-app-layout>
    @section('title')
    Standar Budget
    @endsection

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="">
        <div class="flex justify-end mb-3">
            {{-- Tombol Import Excel akan membuka modal --}}
            <button type="button" class="btn btn-info" id="btnOpenImportModal">Import Excel</button>
            <button type="button" class="ml-2 btn btn-primary" id="btnOpenCreateModal">
                Tambah Data
            </button>
        </div>

        {{-- Tempat untuk menampilkan notifikasi global, termasuk dari session --}}
        <div id="alert-placeholder" class="mb-3">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session()->has('failures'))
               <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <strong>Peringatan! Beberapa baris gagal diimport:</strong>
                    <ul class="list-disc pl-5">
                        @foreach (session()->get('failures') as $failure)
                            <li>Baris {{ $failure->row() }}: {{ implode(', ', $failure->errors()) }} (Nilai: {{ implode(', ', $failure->values()) }})</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>


        <div class="card mt-3">
            <div class="card-body">

                <div class="d-flex justify-end gap-2 mb-2">
                    <select id="year_filter" class="form-select w-auto">
                        <option value="">Semua Tahun</option>
                        @foreach($years as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>

                    <a href="{{ route('standard-budgets.download-sample') }}" class="btn btn-success">
                        Download Template Excel
                    </a>
                </div>

                <table class="table table-bordered table-striped" id="standardBudgetsTable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Name Region</th>
                            <th>Amount</th>
                            <th>Tahun</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

            </div>
        </div>
    </div>

    {{-- Modal CRUD --}}
    <div id="budgetModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/30 backdrop-blur-sm">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl relative">
                <div class="flex items-center justify-between px-4 py-2 border-b">
                    <h5 class="text-xl font-semibold" id="budgetModalLabel">Form Standard Budget</h5>
                    <button id="btnCloseModal" type="button" class="text-gray-600 hover:text-red-600 text-2xl">×</button>
                </div>
                <form id="budgetForm" class="p-6">
                    <input type="hidden" name="id" id="budgetId">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="name_region_modal" class="block text-sm font-medium">Name Region</label>
                            <input type="text" id="name_region_modal" name="name_region" class="form-input mt-1 w-full border-gray-300 rounded" placeholder="Kode Region, Negara" required>
                            <div class="text-red-600 text-sm mt-1" id="name_region_error"></div>
                        </div>
                        <div>
                            <label for="amount_modal" class="block text-sm font-medium">Amount</label>
                            <input type="number" step="0.01" id="amount_modal" name="amount" class="form-input mt-1 w-full border-gray-300 rounded text-right" placeholder="0.00" required>
                            <div class="text-red-600 text-sm mt-1" id="amount_error"></div>
                        </div>
                        <div>
                            <label for="year_modal" class="block text-sm font-medium">Tahun</label>
                            <input type="number" id="year_modal" name="year" class="form-input mt-1 w-full border-gray-300 rounded text-center" placeholder="YYYY" min="1990" max="{{ date('Y') + 5 }}" required>
                            <div class="text-red-600 text-sm mt-1" id="year_error"></div>
                        </div>
                    </div>
                    <div class="flex justify-end mt-6 space-x-2">
                        <button type="button" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded" id="btnCancelModal">Tutup</button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded" id="btnSaveBudget">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Import Excel --}}
    <div id="importExcelModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/30 backdrop-blur-sm">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-lg relative"> {{-- max-w-lg untuk modal import --}}
                <div class="flex items-center justify-between px-4 py-2 border-b">
                    <h5 class="text-xl font-semibold" id="importExcelModalLabel">Import Standard Budgets dari Excel</h5>
                    <button id="btnCloseImportModal" type="button" class="text-gray-600 hover:text-red-600 text-2xl">×</button>
                </div>
                {{-- Form ini akan submit secara normal, bukan AJAX --}}
                <form action="{{ route('standard-budgets.import.excel') }}" method="POST" enctype="multipart/form-data" class="p-6">
                    @csrf
                    <div class="mb-4">
                        <p class="text-sm text-gray-600">Pastikan file Excel Anda memiliki kolom berikut (dengan header pada baris pertama):</p>
                        <ul class="list-disc list-inside text-sm text-gray-600 pl-2">
                            <li><code>name_region</code> (Teks: Kode Region, Negara)</li>
                            <li><code>amount</code> (Angka: Jumlah budget tonnage)</li>
                            <li><code>year</code> (Angka: Tahun budget, minimal 1990)</li>
                        </ul>
                        <p class="text-sm mt-2">Contoh:</p>
                        <table class="w-full text-xs table-auto border border-gray-300 my-1">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="border px-2 py-1">name_region</th>
                                    <th class="border px-2 py-1">amount</th>
                                    <th class="border px-2 py-1">year</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="border px-2 py-1">Region 1A / China</td>
                                    <td class="border px-2 py-1 text-right">1500000.75</td>
                                    <td class="border px-2 py-1 text-center">2023</td>
                                </tr>
                            </tbody>
                        </table>
                         <a href="{{ route('standard-budgets.download-sample') }}" class="text-sm text-blue-600 hover:underline">Download Template Excel</a>
                    </div>

                    <div class="mb-4">
                        <label for="excel_file" class="block text-sm font-medium text-gray-700">Pilih File Excel (.xlsx, .xls)</label>
                        <input type="file" class="form-input mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                               id="excel_file" name="excel_file" accept=".xlsx, .xls" required>
                    </div>

                    <div class="flex justify-end mt-6 space-x-2">
                        <button type="button" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded" id="btnCancelImportModal">Batal</button>
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Import Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            const budgetModal = $('#budgetModal');
            const importExcelModal = $('#importExcelModal'); // Modal baru

            function showBudgetModal() { budgetModal.removeClass('hidden'); }
            function hideBudgetModal() { budgetModal.addClass('hidden'); }
            function showImportExcelModal() { importExcelModal.removeClass('hidden'); }
            function hideImportExcelModal() { importExcelModal.addClass('hidden'); }


            function clearValidationErrors() {
                // Penamaan ID input di CRUD modal sudah diubah (cth: name_region_modal)
                $('#budgetForm .form-input').removeClass('border-red-500');
                $('#budgetForm .text-red-600').text('');
            }

            // Fungsi notifikasi ini akan lebih banyak digunakan oleh AJAX CRUD
            function showAjaxNotification(message, type = 'success') {
                const alertPlaceholder = $('#alert-placeholder');
                // Hapus notifikasi lama dari AJAX sebelum menampilkan yang baru
                alertPlaceholder.find('.ajax-notification').remove();

                const alertDiv = $(
                    `<div class="alert alert-${type} alert-dismissible fade show mt-3 ajax-notification" role="alert">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>`
                );
                alertPlaceholder.append(alertDiv); // Append, jangan html() agar notif session tidak hilang
                setTimeout(() => alertDiv.alert('close'), 5000);
            }

            // Auto-close notifikasi dari session (jika ada)
            $('#alert-placeholder .alert').not('.ajax-notification').delay(7000).slideUp(300, function() {
                $(this).alert('close');
            });


            const table = $('#standardBudgetsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('standard-budgets.index') }}",
                    data: d => {
                        d.year = $('#year_filter').val();
                    }
                },
                dom: '<"d-flex justify-between align-items-center mb-3"f>rt<"d-flex justify-between align-items-center mt-3"ip>',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    { data: 'name_region', name: 'name_region' },
                    {
                        data: 'amount', name: 'amount', className: 'text-right',
                        render: (data) => parseFloat(data).toLocaleString('id-ID', {
                            minimumFractionDigits: 2, maximumFractionDigits: 2
                        })
                    },
                    { data: 'year', name: 'year', className: 'text-center' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ]
            });

            // CRUD Modal
            $('#btnOpenCreateModal').on('click', () => {
                $('#budgetForm')[0].reset();
                clearValidationErrors();
                $('#budgetModalLabel').text('Tambah Standard Budget');
                $('#budgetId').val('');
                $('#year_modal').val(new Date().getFullYear()); // ID input tahun di modal: year_modal
                showBudgetModal();
            });

            $('#btnCloseModal, #btnCancelModal').on('click', hideBudgetModal);

            $('#budgetForm').on('submit', function(e) {
                e.preventDefault();
                clearValidationErrors();
                $('#btnSaveBudget').text('Menyimpan...').prop('disabled', true);

                const id = $('#budgetId').val();
                const url = id ? "{{ url('standard-budgets') }}/" + id : "{{ route('standard-budgets.store') }}";
                const method = id ? 'PUT' : 'POST';

                $.ajax({
                    url, type: method, data: $(this).serialize(),
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(response) {
                        hideBudgetModal();
                        table.ajax.reload();
                        showAjaxNotification(response.success, 'success');
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            $.each(errors, (key, value) => {
                                // Sesuaikan dengan ID input di modal
                                $('#' + key + '_modal').addClass('border-red-500');
                                $('#' + key + '_error').text(value[0]);
                            });
                            showAjaxNotification('Terdapat kesalahan input.', 'danger');
                        } else {
                            showAjaxNotification('Gagal menyimpan data. ' + (xhr.responseJSON?.message || xhr.statusText || 'Server Error'), 'danger');
                            console.error(xhr.responseText);
                        }
                    },
                    complete: () => $('#btnSaveBudget').text('Simpan').prop('disabled', false)
                });
            });

            $('body').on('click', '.edit-btn', function() {
                const id = $(this).data('id');
                clearValidationErrors(); // Panggil ini sebelum mengisi form
                $.get("{{ url('standard-budgets') }}/" + id + "/edit", function(data) {
                    $('#budgetId').val(data.id);
                    $('#name_region_modal').val(data.name_region); // ID input name_region di modal
                    $('#amount_modal').val(parseFloat(data.amount).toFixed(2)); // ID input amount di modal
                    $('#year_modal').val(data.year); // ID input year di modal
                    $('#budgetModalLabel').text('Edit Standard Budget');
                    showBudgetModal();
                }).fail(xhr => {
                    showAjaxNotification('Gagal memuat data. '  + (xhr.responseJSON?.message || xhr.statusText || 'Server Error'), 'danger');
                    console.error(xhr.responseText);
                });
            });


            // Import Excel Modal
            $('#btnOpenImportModal').on('click', showImportExcelModal);
            $('#btnCloseImportModal, #btnCancelImportModal').on('click', hideImportExcelModal);
            // Tidak ada AJAX untuk form import, submit biasa


            // Delete
            $('body').on('click', '.delete-btn', function() {
                // ... (kode delete tetap sama)
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Anda yakin?',
                    text: "Data yang dihapus tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                    customClass: {
                        confirmButton: 'bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded mr-2',
                        cancelButton: 'bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-2 rounded'
                    },
                    buttonsStyling: false
                }).then(result => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('standard-budgets') }}/" + id,
                            type: 'POST',
                            data: {
                                _method: 'DELETE',
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                table.ajax.reload();
                                showAjaxNotification(response.success, 'success');
                            },
                            error: function(xhr) {
                                showAjaxNotification('Gagal menghapus data. ' + (xhr.responseJSON?.error || xhr.statusText || 'Server Error'), 'danger');
                                console.error(xhr.responseText);
                            }
                        });
                    }
                });
            });

            $('#year_filter').on('change', () => table.ajax.reload());
        });
    </script>
    @endpush
</x-app-layout>