<x-app-layout>
    @section('title')
    Standar Budget
    @endsection

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="">
        <div class="flex justify-end mb-3">
            <button type="button" class="btn btn-info" id="btnOpenImportModal">Import Excel</button>
            <button type="button" class="ml-2 btn btn-primary" id="btnOpenCreateModal">
                Tambah Data
            </button>
        </div>

        {{-- Alert Placeholder for dynamic AJAX messages and session messages --}}
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
            @if (session('warning')) {{-- General warning for import --}}
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session()->has('failures') && is_iterable(session()->get('failures')) && count(session()->get('failures')) > 0)
               <div class="alert alert-danger alert-dismissible fade show" role="alert"> {{-- Changed to danger for more impact --}}
                    <strong>Peringatan! Beberapa baris gagal diimport dari Excel:</strong>
                    <ul class="list-disc pl-5 mt-2 mb-0" style="max-height: 200px; overflow-y: auto;">
                        @foreach (session()->get('failures') as $failure)
                            <li>
                                Baris ke-{{ $failure->row() }}:
                                {{ implode(', ', $failure->errors()) }}
                                @if($failure->values())
                                    (Nilai yang diberikan:
                                    @foreach($failure->values() as $key => $value)
                                        {{ $key }}: '{{ $value ?? 'Kosong' }}'{{ !$loop->last ? ',' : '' }}
                                    @endforeach
                                    )
                                @endif
                            </li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                    <div>
                        <select id="year_filter" class="form-select w-auto d-inline-block">
                            <option value="">Semua Tahun</option>
                            @if(isset($years) && count($years) > 0)
                                @foreach($years as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <a href="{{ route('standard-budgets.download-sample') }}" class="btn btn-success">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Download Template
                    </a>
                </div>

                <table class="table table-bordered table-striped" id="standardBudgetsTable" style="width:100%;">
                    <thead>
                        <tr>
                            <th style="width:5%;">No</th>
                            <th>Name Region</th>
                            <th style="width:20%;">Amount</th>
                            <th style="width:10%;">Tahun</th>
                            <th style="width:10%;">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Create/Edit Modal --}}
    <div id="budgetModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/50 backdrop-blur-sm">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl relative"> {{-- Adjusted max-width --}}
                <div class="flex items-center justify-between px-6 py-3 border-b"> {{-- Adjusted padding --}}
                    <h5 class="text-xl font-semibold" id="budgetModalLabel">Form Standard Budget</h5>
                    <button id="btnCloseModal" type="button" class="text-gray-400 hover:text-red-600 text-2xl leading-none">×</button>
                </div>
                <form id="budgetForm" class="p-6">
                    <input type="hidden" name="id" id="budgetId">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label for="name_region_modal" class="block text-sm font-medium text-gray-700 mb-1">Name Region <span class="text-red-500">*</span></label>
                            <input type="text" id="name_region_modal" name="name_region" class="form-input mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="Kode Region, Negara" required>
                            <div class="text-red-500 text-xs mt-1" id="name_region_error"></div>
                        </div>
                        <div>
                            <label for="amount_modal" class="block text-sm font-medium text-gray-700 mb-1">Amount <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" id="amount_modal" name="amount" class="form-input mt-1 block w-full border-gray-300 rounded-md shadow-sm text-right focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="0.00" required>
                            <div class="text-red-500 text-xs mt-1" id="amount_error"></div>
                        </div>
                        <div>
                            <label for="year_modal" class="block text-sm font-medium text-gray-700 mb-1">Tahun <span class="text-red-500">*</span></label>
                            <input type="number" id="year_modal" name="year" class="form-input mt-1 block w-full border-gray-300 rounded-md shadow-sm text-center focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="YYYY" min="1990" max="{{ date('Y') + 10 }}" required>
                            <div class="text-red-500 text-xs mt-1" id="year_error"></div>
                        </div>
                    </div>
                    <div class="flex justify-end mt-6 space-x-2 border-t pt-4">
                        <button type="button" class="btn btn-light" id="btnCancelModal">Tutup</button>
                        <button type="submit" class="btn btn-primary" id="btnSaveBudget">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Import Excel Modal --}}
    <div id="importExcelModal" class="fixed inset-0 z-[100] hidden overflow-y-auto bg-black/50 backdrop-blur-sm">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-lg relative">
                <div class="flex items-center justify-between px-6 py-3 border-b">
                    <h5 class="text-xl font-semibold" id="importExcelModalLabel">Import Standard Budgets</h5>
                    <button id="btnCloseImportModal" type="button" class="text-gray-400 hover:text-red-600 text-2xl leading-none">×</button>
                </div>

                {{-- This container is available but failures are primarily shown via session flash on page reload --}}
                <div id="importErrorContainer" class="hidden p-4"></div>

                <form id="importExcelForm" action="{{ route('standard-budgets.import.excel') }}" method="POST" enctype="multipart/form-data" class="p-6">
                    @csrf
                    <div class="mb-4">
                        <p class="text-sm text-gray-600">
                            Pastikan file Excel Anda memiliki header pada <strong>baris ke-2</strong> dan data dimulai dari <strong>baris ke-3</strong>.
                            Kolom yang diperlukan:
                        </p>
                        <ul class="list-disc list-inside text-sm text-gray-600 pl-2 my-2">
                            <li><code>name_region</code> (Teks: Kode Region, Negara)</li>
                            <li><code>amount</code> (Angka: Jumlah budget)</li>
                            <li><code>year</code> (Angka: Tahun budget, 4 digit, min 1990)</li>
                        </ul>
                        <p class="text-sm mt-2">Contoh (Header di baris 2):</p>
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs table-auto border border-gray-300 my-1">
                                <thead class="bg-gray-100">
                                    <tr><td colspan="3" class="border px-2 py-1 text-center font-semibold italic">Judul File Anda (Baris 1)</td></tr>
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
                                     <tr>
                                        <td class="border px-2 py-1">Region 2B / USA</td>
                                        <td class="border px-2 py-1 text-right">250000</td>
                                        <td class="border px-2 py-1 text-center">2024</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <a href="{{ route('standard-budgets.download-sample') }}" class="text-sm text-blue-600 hover:underline">Download Template Excel</a>
                    </div>

                    <div class="mb-4">
                        <label for="excel_file" class="block text-sm font-medium text-gray-700 mb-1">Pilih File Excel (.xlsx, .xls) <span class="text-red-500">*</span></label>
                        <input type="file" class="form-input mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                            id="excel_file" name="excel_file" accept=".xlsx, .xls" required>
                        <div class="text-xs text-gray-500 mt-1">Ukuran maksimal: 10MB. Header di baris 2, data mulai baris 3.</div>
                        <div id="excel_file_error" class="text-red-500 text-xs mt-1"></div>
                    </div>

                    <div class="flex justify-end mt-6 space-x-2 border-t pt-4">
                        <button type="button" class="btn btn-light" id="btnCancelImportModal">Batal</button>
                        <button type="submit" class="btn btn-success" id="btnSubmitImport">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM10 2a1 1 0 011 1v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 111.414-1.414L9 10.586V3a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Import Data
                        </button>
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
            const importExcelModal = $('#importExcelModal');
            const csrfToken = $('meta[name="csrf-token"]').attr('content');

            function showBudgetModal() { budgetModal.removeClass('hidden'); $('body').addClass('overflow-hidden');}
            function hideBudgetModal() { budgetModal.addClass('hidden'); $('body').removeClass('overflow-hidden');}
            function showImportExcelModal() { importExcelModal.removeClass('hidden'); $('body').addClass('overflow-hidden');}
            function hideImportExcelModal() { importExcelModal.addClass('hidden'); $('body').removeClass('overflow-hidden');}

            function clearValidationErrors(formId = 'budgetForm') {
                $('#' + formId + ' .form-input').removeClass('border-red-500 is-invalid');
                $('#' + formId + ' .text-red-500').text('');
                $('#' + formId + ' .invalid-feedback').remove(); // For Bootstrap style errors if any
            }

            function displayValidationErrors(errors, formId = 'budgetForm') {
                 $.each(errors, (key, value) => {
                    const inputField = $('#' + key + '_modal, #' + key); // Handles _modal suffix and direct ID
                    inputField.addClass('border-red-500 is-invalid');
                    // Ensure the error div exists or create it
                    let errorDiv = $('#' + key + '_error');
                    if (errorDiv.length === 0) {
                         // Create a generic error display if a specific one isn't found
                        inputField.after('<div class="text-red-500 text-xs mt-1 invalid-feedback" id="' + key + '_error">' + value[0] + '</div>');
                    } else {
                        errorDiv.text(value[0]);
                    }
                });
            }


            function showAjaxNotification(message, type = 'success') {
                const alertPlaceholder = $('#alert-placeholder');
                alertPlaceholder.find('.ajax-notification').remove(); // Remove previous ajax notifications

                const alertDiv = $(
                    `<div class="alert alert-${type} alert-dismissible fade show mt-3 ajax-notification" role="alert">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>`
                );
                alertPlaceholder.prepend(alertDiv); // Prepend to show at top
                $('html, body').animate({ scrollTop: alertPlaceholder.offset().top - 20 }, 'smooth'); // Scroll to message
                setTimeout(() => {
                    alertDiv.fadeOut(500, function() { $(this).remove(); });
                }, 7000);
            }

            // Auto-hide session alerts after a delay
            $('#alert-placeholder .alert').not('.ajax-notification').not('.alert-danger').delay(7000).slideUp(300, function() {
                $(this).alert('close');
            });
             // Keep danger alerts (like import failures) visible longer or until manually closed
            $('#alert-placeholder .alert-danger').not('.ajax-notification').delay(20000).slideUp(300, function() {
                 $(this).alert('close');
            });


            const table = $('#standardBudgetsTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('standard-budgets.index') }}",
                    type: "GET",
                    data: function(d) {
                        d.year = $('#year_filter').val();
                        // d.search_term = $('#customSearchInput').val(); // Example if you add custom search
                    },
                    error: function (xhr, error, code) {
                        console.error("DataTables AJAX error: ", xhr.responseText);
                        showAjaxNotification('Gagal memuat data tabel. Coba lagi nanti.', 'danger');
                    }
                },
                dom: '<"d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mb-3" <"text-muted"i> <"d-flex gap-2"f <"mt-2 mt-md-0"l> > > rt <"d-flex justify-content-between align-items-center mt-3"<"text-muted"i>p>',
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Cari...",
                    lengthMenu: "Tampil _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    infoEmpty: "Tidak ada data",
                    infoFiltered: "(disaring dari _MAX_ total data)",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Berikutnya",
                        previous: "Sebelumnya"
                    },
                    processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Memuat...</span></div> <span class="ms-2">Memuat data...</span>'
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'name_region', name: 'name_region' },
                    {
                        data: 'amount', name: 'amount', className: 'text-right',
                        render: function(data, type, row) {
                            // Data from server is already formatted as string "1.234,56"
                            // For sorting, DataTables might need raw number.
                            // If 'data' is a raw number from controller, use:
                            // return parseFloat(data).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            return data; // Assuming 'amount' is already formatted from controller editColumn
                        }
                    },
                    { data: 'year', name: 'year', className: 'text-center' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                order: [[3, 'desc'], [1, 'asc']] // Default sort by year desc, then name_region asc
            });

            $('#btnOpenCreateModal').on('click', () => {
                $('#budgetForm')[0].reset();
                clearValidationErrors();
                $('#budgetModalLabel').text('Tambah Standard Budget');
                $('#budgetId').val('');
                $('#year_modal').val(new Date().getFullYear());
                showBudgetModal();
            });

            $('#btnCloseModal, #btnCancelModal').on('click', hideBudgetModal);

            $('#budgetForm').on('submit', function(e) {
                e.preventDefault();
                clearValidationErrors();
                const btnSave = $('#btnSaveBudget');
                btnSave.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...').prop('disabled', true);

                const id = $('#budgetId').val();
                const url = id ? "{{ url('standard-budgets') }}/" + id : "{{ route('standard-budgets.store') }}";
                const method = id ? 'PUT' : 'POST';

                $.ajax({
                    url: url,
                    type: method,
                    data: $(this).serialize(),
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    success: function(response) {
                        hideBudgetModal();
                        table.ajax.reload(null, false); // false to keep current page
                        showAjaxNotification(response.success, 'success');
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) { // Validation error
                            const errors = xhr.responseJSON.errors;
                            displayValidationErrors(errors);
                            showAjaxNotification('Terdapat kesalahan input. Mohon periksa kembali form.', 'danger');
                        } else {
                            const errorMsg = xhr.responseJSON?.error || xhr.responseJSON?.message || xhr.statusText || 'Terjadi kesalahan. Silakan coba lagi.';
                            showAjaxNotification('Gagal menyimpan data: ' + errorMsg, 'danger');
                            console.error("Save error:", xhr.responseText);
                        }
                    },
                    complete: () => btnSave.text('Simpan').prop('disabled', false)
                });
            });

            $('body').on('click', '.edit-btn', function() {
                const id = $(this).data('id');
                clearValidationErrors();
                $.get("{{ url('standard-budgets') }}/" + id + "/edit", function(data) {
                    $('#budgetId').val(data.id);
                    $('#name_region_modal').val(data.name_region);
                    // Amount from server might be float, ensure it's formatted for display if needed,
                    // but input type number handles it.
                    $('#amount_modal').val(parseFloat(data.amount).toFixed(2));
                    $('#year_modal').val(data.year);
                    $('#budgetModalLabel').text('Edit Standard Budget');
                    showBudgetModal();
                }).fail(xhr => {
                    const errorMsg = xhr.responseJSON?.error || xhr.responseJSON?.message || 'Gagal memuat data untuk diedit.';
                    showAjaxNotification(errorMsg, 'danger');
                    console.error("Edit load error:", xhr.responseText);
                });
            });

            $('body').on('click', '.delete-btn', function() {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Anda yakin?',
                    text: "Data yang dihapus tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                }).then(result => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('standard-budgets') }}/" + id,
                            type: 'POST', // Method override
                            data: {
                                _method: 'DELETE',
                                _token: csrfToken
                            },
                            success: function(response) {
                                table.ajax.reload(null, false);
                                showAjaxNotification(response.success, 'success');
                            },
                            error: function(xhr) {
                                const errorMsg = xhr.responseJSON?.error || xhr.responseJSON?.message || 'Gagal menghapus data.';
                                showAjaxNotification(errorMsg, 'danger');
                                console.error("Delete error:", xhr.responseText);
                            }
                        });
                    }
                });
            });

            $('#btnOpenImportModal').on('click', () => {
                $('#importExcelForm')[0].reset();
                clearValidationErrors('importExcelForm');
                $('#excel_file_error').text(''); // Clear specific file error
                showImportExcelModal();
            });
            $('#btnCloseImportModal, #btnCancelImportModal').on('click', hideImportExcelModal);

            // Handle Import Form Submission (Standard form submission, page will reload)
            $('#importExcelForm').on('submit', function(e) {
                const fileInput = $('#excel_file');
                $('#excel_file_error').text(''); // Clear previous error

                if (!fileInput.val()) {
                    e.preventDefault(); // Prevent submission
                    $('#excel_file_error').text('Silakan pilih file Excel untuk diimport.');
                    fileInput.addClass('border-red-500');
                    return false;
                }
                // Add a loading indicator to the submit button
                $('#btnSubmitImport').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mengimport...').prop('disabled', true);
            });


            $('#year_filter').on('change', () => table.ajax.reload());

            // Close modals on Escape key
            $(document).on('keydown', function(event) {
                if (event.key === "Escape") {
                    if (!budgetModal.hasClass('hidden')) {
                        hideBudgetModal();
                    }
                    if (!importExcelModal.hasClass('hidden')) {
                        hideImportExcelModal();
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>