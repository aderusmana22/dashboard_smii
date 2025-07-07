<x-app-layout>
    @section('title')
    Standar Budget
    @endsection

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        /* Force checkbox visibility - good for debugging CSS conflicts */
        input[type="checkbox"].checkbox_ids,
        input[type="checkbox"]#select_all_ids {
            appearance: checkbox !important;
            -webkit-appearance: checkbox !important;
            width: 16px !important;
            height: 16px !important;
            opacity: 1 !important;
            display: inline-block !important;
            position: static !important;
            visibility: visible !important;
            margin: auto; /* Helps with centering if text-align is on parent */
        }

        .swal2-confirm {
            background-color: green !important;
            color: white !important;
            border: unset !important;
        }

        .swal2-cancel {
            background-color: red !important;
            color: white !important;
            border: unset !important;
        }

    </style>

    <div class="">
        <div class="flex justify-between items-center mb-3">
            <div>
                <button type="button" class="btn btn-danger" id="btnBulkDelete" style="display:none;">
                    <i class="fas fa-trash-alt"></i>Delete Selected
                </button>
            </div>
            <div class="flex">
                <button type="button" class="btn btn-info" id="btnOpenImportModal">Import Excel</button>
                <button type="button" class="ml-2 btn btn-primary" id="btnOpenCreateModal">
                    Add Data
                </button>
            </div>
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
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Peringatan! Beberapa baris gagal diimport dari Excel:</strong>
                <ul class="list-disc pl-5 mt-2 mb-0" style="max-height: 200px; overflow-y: auto;">
                    @foreach (session()->get('failures') as $failure)
                    <li>
                        Baris Excel ke-{{ $failure->row() }}:
                        {{ implode(', ', $failure->errors()) }}
                        @if($failure->values() && is_array($failure->values()))
                        (Nilai yang diberikan:
                        @foreach($failure->values() as $key => $value)
                        @if(!is_array($value) && !is_object($value)) {{-- Avoid printing array/object as value --}}
                           {{ $key }}: '{{ $value ?? 'Kosong' }}'{{ !$loop->last ? ',' : '' }}
                        @endif
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
                    <select id="year_filter" class="form-select w-auto d-inline-block">
                        <option value="">All Years</option>
                        @if(isset($years) && count($years) > 0)
                        @foreach($years as $year)
                        <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                        @endif
                    </select>
                    <a href="{{ route('standard-budgets.download-sample') }}" class="btn btn-success">
                        Download Template
                    </a>
                </div>

                <table class="table table-bordered table-striped" id="standardBudgetsTable" style="width:100%;">
                    <thead>
                        <tr>
                            <th style="width:3%; text-align:center;"><input type="checkbox" id="select_all_ids"></th>
                            <th style="width:5%;">No</th>
                            <th>Brand Name</th>
                            <th>Name Region</th>
                            <th style="width:15%;">Amount (Tonnage)</th>
                            <th style="width:5%;">Month</th>
                            <th style="width:10%;">Year</th>
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
            <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl relative">
                <div class="flex items-center justify-between px-6 py-3 border-b">
                    <h5 class="text-xl font-semibold" id="budgetModalLabel">Form Standard Budget</h5>
                    <button id="btnCloseModal" type="button" class="text-gray-400 hover:text-red-600 text-2xl leading-none">×</button>
                </div>
                <form id="budgetForm" class="p-6">
                    <input type="hidden" name="id" id="budgetId">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="brand_name_modal" class="block text-sm font-medium text-gray-700 mb-1">Brand Name <span class="text-red-500">*</span></label>
                            <input type="text" id="brand_name_modal" name="brand_name" class="form-input mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="Brand Name" required>
                            <div class="text-red-500 text-xs mt-1" id="brand_name_error"></div>
                        </div>
                        <div>
                            <label for="name_region_modal" class="block text-sm font-medium text-gray-700 mb-1">Name Region <span class="text-red-500">*</span></label>
                            <input type="text" id="name_region_modal" name="name_region" class="form-input mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="Region Code, Country" required>
                            <div class="text-red-500 text-xs mt-1" id="name_region_error"></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label for="amount_modal" class="block text-sm font-medium text-gray-700 mb-1">Amount (Tonnage) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.0001" id="amount_modal" name="amount" class="form-input mt-1 block w-full border-gray-300 rounded-md shadow-sm text-right focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="0.0000" required>
                            <div class="text-red-500 text-xs mt-1" id="amount_error"></div>
                        </div>
                        <div>
                            <label for="month_modal" class="block text-sm font-medium text-gray-700 mb-1">Month <span class="text-red-500">*</span></label>
                            <select id="month_modal" name="month" class="form-select mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                <option value="">Choose Month</option>
                                @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                                @endfor
                            </select>
                            <div class="text-red-500 text-xs mt-1" id="month_error"></div>
                        </div>
                        <div>
                            <label for="year_modal" class="block text-sm font-medium text-gray-700 mb-1">Year <span class="text-red-500">*</span></label>
                            <input type="number" id="year_modal" name="year" class="form-input mt-1 block w-full border-gray-300 rounded-md shadow-sm text-center focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="YYYY" min="1990" max="{{ date('Y') + 10 }}" required>
                            <div class="text-red-500 text-xs mt-1" id="year_error"></div>
                        </div>
                    </div>
                    <div class="flex justify-end mt-6 space-x-2 border-t pt-4">
                        <button type="button" class="btn btn-light" id="btnCancelModal">Close</button>
                        <button type="submit" class="btn btn-primary" id="btnSaveBudget">Add</button>
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
                <form id="importExcelForm" action="{{ route('standard-budgets.import.excel') }}" method="POST" enctype="multipart/form-data" class="p-6">
                    @csrf
                    <div class="mb-4">
                        <p class="text-sm text-gray-600">
                            Pastikan file Excel Anda memiliki header pada <strong>baris ke-2</strong> dan data dimulai dari <strong>baris ke-3</strong>.
                            Kolom yang diperlukan (nama header case-insensitive, tapi disarankan sesuai contoh):
                        </p>
                        <ul class="list-disc list-inside text-sm text-gray-600 pl-2 my-2">
                            <li><code>brand_name</code> (Teks)</li>
                            <li><code>name_region</code> (Teks)</li>
                            <li><code>amount</code> (Angka, misal 1500.1234 atau 1500,1234. Maks. 4 desimal)</li>
                            <li><code>month</code> (Angka 1-12 atau Teks Nama Bulan: Januari, Feb, March, etc.)</li>
                            <li><code>year</code> (Angka: YYYY, min 1990)</li>
                        </ul>
                        <p class="text-sm mt-2">Contoh (Header di baris 2):</p>
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs table-auto border border-gray-300 my-1">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <td colspan="5" class="border px-2 py-1 text-center font-semibold italic">Judul File Anda (Baris 1, Opsional)</td>
                                    </tr>
                                    <tr>
                                        <th class="border px-2 py-1">brand_name</th>
                                        <th class="border px-2 py-1">name_region</th>
                                        <th class="border px-2 py-1">amount</th>
                                        <th class="border px-2 py-1">month</th>
                                        <th class="border px-2 py-1">year</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="border px-2 py-1">Brand ABC</td>
                                        <td class="border px-2 py-1">Region 1A</td>
                                        <td class="border px-2 py-1 text-right">1500.7512</td>
                                        <td class="border px-2 py-1 text-center">1</td>
                                        <td class="border px-2 py-1 text-center">2023</td>
                                    </tr>
                                    <tr>
                                        <td class="border px-2 py-1">brand abc</td> <!-- Case variation -->
                                        <td class="border px-2 py-1">region 1a</td> <!-- Case variation -->
                                        <td class="border px-2 py-1 text-right">2500,5000</td>
                                        <td class="border px-2 py-1 text-center">Maret</td>
                                        <td class="border px-2 py-1 text-center">2023</td> <!-- Same year and month as above -->
                                    </tr>
                                </tbody>
                            </table>
                        </div>
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
                            Import Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- Font Awesome for icons (if not globally included) --}}
    {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> --}}

    <script>
    $(document).ready(function() {
        const budgetModal = $('#budgetModal');
        const importExcelModal = $('#importExcelModal');
        const csrfToken = $('meta[name="csrf-token"]').attr('content');

        function showModal(modalElement) { modalElement.removeClass('hidden'); $('body').addClass('overflow-hidden'); }
        function hideModal(modalElement) { modalElement.addClass('hidden'); $('body').removeClass('overflow-hidden'); }

        function clearValidationErrors(formId = 'budgetForm') {
            const form = $('#' + formId);
            form.find('.form-input, .form-select').removeClass('border-red-500 is-invalid');
            form.find('.text-red-500.text-xs.mt-1').text('');
            form.find('.invalid-feedback').remove();
        }

        function displayValidationErrors(errors, formId = 'budgetForm') {
            const form = $('#' + formId);
            $.each(errors, (key, value) => {
                const inputField = form.find('#' + key + '_modal, #' + key);
                inputField.addClass('border-red-500 is-invalid');
                let errorDiv = form.find('#' + key + '_error');
                if (errorDiv.length === 0) {
                    inputField.after(`<div class="text-red-500 text-xs mt-1 invalid-feedback" id="${key}_error">${value[0]}</div>`);
                } else {
                    errorDiv.text(value[0]);
                }
            });
        }

        function showAjaxNotification(message, type = 'success') {
            const alertPlaceholder = $('#alert-placeholder');
            alertPlaceholder.find('.ajax-notification').remove();
            const alertDiv = $(
                `<div class="alert alert-${type} alert-dismissible fade show mt-3 ajax-notification" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>`
            );
            alertPlaceholder.prepend(alertDiv);
            $('html, body').animate({ scrollTop: alertPlaceholder.offset().top - 70 }, 'smooth');
            setTimeout(() => alertDiv.fadeOut(500, function() { $(this).remove(); }), type === 'danger' || type === 'warning' ? 15000 : 7000);
        }

        $('#alert-placeholder .alert').not('.ajax-notification').not('.alert-danger[role="alert"]').delay(7000).slideUp(300, function() { $(this).alert('close'); });
        $('#alert-placeholder .alert-danger[role="alert"]').not('.ajax-notification').delay(20000).slideUp(300, function() { $(this).alert('close'); });


        const table = $('#standardBudgetsTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true, // Keep responsive enabled
            ajax: {
                url: "{{ route('standard-budgets.index') }}",
                type: "GET",
                data: function(d) { d.year = $('#year_filter').val(); },
                error: function(xhr) {
                    console.error("DataTables AJAX error:", xhr.responseText);
                    showAjaxNotification('Gagal memuat data tabel. Coba lagi nanti.', 'danger');
                }
            },
            dom: '<"d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mb-3"<"text-muted"i><"d-flex gap-2"f<"mt-2 mt-md-0"l>>>rt<"d-flex justify-content-between align-items-center mt-3"<"text-muted"i>p>',
            language: { search: "_INPUT_", searchPlaceholder: "Cari...", lengthMenu: "Tampil _MENU_", info: "Data _START_-_END_ dari _TOTAL_", infoEmpty: "Kosong", infoFiltered: "(dari _MAX_ total)", paginate: { first: "<<", last: ">>", next: ">", previous: "<" }, processing: '<div class="spinner-border text-primary spinner-sm" role="status"></div> Loading...' },
            columns: [
                { data: 'checkbox',    name: 'checkbox',    className: 'text-center dt-body-center' }, // Index 0
                { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'text-center' },      // Index 1
                { data: 'brand_name',  name: 'brand_name' },                                 // Index 2
                { data: 'name_region', name: 'name_region' },                                // Index 3
                { data: 'amount',      name: 'amount',      className: 'text-right' },       // Index 4
                { data: 'month',       name: 'month',       className: 'text-center' },      // Index 5
                { data: 'year',        name: 'year',        className: 'text-center' },      // Index 6
                { data: 'action',      name: 'action',      className: 'text-center' }       // Index 7
            ],
            order: [[6, 'desc'], [5, 'asc'], [2, 'asc'], [3, 'asc']],

            columnDefs: [
                {
                    targets: 0, // Checkbox column
                    orderable: false,
                    searchable: false,
                    className: 'all text-center dt-body-center'
                },
                {
                    targets: 1, // DT_RowIndex column
                    orderable: false,
                    searchable: false,
                    className: 'dtr-control text-center'
                },
                {
                    targets: 7, // Action column
                    orderable: false,
                    searchable: false,
                    className: 'all text-center'
                }
            ],

            drawCallback: function(settings) {
                const api = this.api();
                const anyChecked = api.rows({ search: 'applied' }).nodes().to$().find('.checkbox_ids:checked').length > 0;
                $('#btnBulkDelete').toggle(anyChecked);

                const allCheckboxesOnPage = api.column(0, { page: 'current' }).nodes().to$().find('.checkbox_ids');
                const checkedCheckboxesOnPage = allCheckboxesOnPage.filter(':checked');
                $('#select_all_ids').prop('checked', allCheckboxesOnPage.length > 0 && checkedCheckboxesOnPage.length === allCheckboxesOnPage.length);
            }
        });

        $('#btnOpenCreateModal').on('click', () => {
            $('#budgetForm')[0].reset(); clearValidationErrors('budgetForm');
            $('#budgetModalLabel').text('Add Standard Budget'); $('#budgetId').val('');
            $('#month_modal').val(new Date().getMonth() + 1); $('#year_modal').val(new Date().getFullYear());
            showModal(budgetModal);
        });

        $('#btnCloseModal, #btnCancelModal').on('click', () => hideModal(budgetModal));

        $('#budgetForm').on('submit', function(e) {
            e.preventDefault(); clearValidationErrors('budgetForm');
            const btnSave = $('#btnSaveBudget');
            btnSave.html('<span class="spinner-border spinner-border-sm" role="status"></span> Menyimpan...').prop('disabled', true);
            const id = $('#budgetId').val();
            const url = id ? `{{ url('standard-budgets') }}/${id}` : "{{ route('standard-budgets.store') }}";
            const method = id ? 'PUT' : 'POST';

            $.ajax({
                url: url, type: method, data: $(this).serialize(), headers: { 'X-CSRF-TOKEN': csrfToken },
                success: (response) => {
                    hideModal(budgetModal); table.ajax.reload(null, false); showAjaxNotification(response.success, 'success');
                },
                error: (xhr) => {
                    if (xhr.status === 422) {
                        displayValidationErrors(xhr.responseJSON.errors, 'budgetForm');
                        showAjaxNotification(xhr.responseJSON.message || 'Periksa kembali input Anda.', 'danger');
                    } else {
                        showAjaxNotification(xhr.responseJSON?.error || xhr.responseJSON?.message || 'Gagal menyimpan data.', 'danger');
                    }
                    console.error("Save error:", xhr.responseText);
                },
                complete: () => btnSave.text('Simpan').prop('disabled', false)
            });
        });

        $('body').on('click', '.edit-btn', function() {
            const id = $(this).data('id'); clearValidationErrors('budgetForm');
            $.get(`{{ url('standard-budgets') }}/${id}/edit`, (data) => {
                $('#budgetId').val(data.id); $('#brand_name_modal').val(data.brand_name);
                $('#name_region_modal').val(data.name_region); $('#amount_modal').val(data.amount);
                $('#month_modal').val(data.month); $('#year_modal').val(data.year);
                $('#budgetModalLabel').text('Edit Standard Budget'); showModal(budgetModal);
            }).fail((xhr) => {
                showAjaxNotification(xhr.responseJSON?.error || 'Gagal memuat data untuk edit.', 'danger');
                console.error("Edit load error:", xhr.responseText);
            });
        });

        $('body').on('click', '.delete-btn', function() {
            const id = $(this).data('id');
            const $thisButton = $(this); // Store reference to the clicked button

            Swal.fire({
                title: 'Anda yakin?',
                text: "Data ini akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                returnFocus: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `{{ url('standard-budgets') }}/${id}`,
                        type: 'POST', // Using POST with _method for forms/simplicity
                        data: {
                            _method: 'DELETE',
                            _token: csrfToken
                        },
                        success: (response) => {
                            table.ajax.reload(null, false);
                            showAjaxNotification(response.success, 'success');
                        },
                        error: (xhr) => {
                            showAjaxNotification(xhr.responseJSON?.error || 'Gagal menghapus data.', 'danger');
                            console.error("Delete error:", xhr.responseText);
                        }
                    });
                }
                // After Swal closes, try to re-focus the button
                setTimeout(() => {
                    $thisButton.trigger('focus');
                }, 0); // A small timeout can help ensure focus is restored after Swal fully closes
            });
        });

        $('#btnOpenImportModal').on('click', () => {
            $('#importExcelForm')[0].reset(); clearValidationErrors('importExcelForm');
            $('#excel_file_error').text(''); showModal(importExcelModal);
        });
        $('#btnCloseImportModal, #btnCancelImportModal').on('click', () => hideModal(importExcelModal));

        $('#importExcelForm').on('submit', function(e) {
            const fileInput = $('#excel_file'); $('#excel_file_error').text('');
            fileInput.removeClass('border-red-500 is-invalid');
            if (!fileInput.val()) {
                e.preventDefault(); $('#excel_file_error').text('Silakan pilih file Excel.');
                fileInput.addClass('border-red-500 is-invalid'); return false;
            }
            $('#btnSubmitImport').html('<span class="spinner-border spinner-border-sm" role="status"></span> Mengimport...').prop('disabled', true);
        });

        $('#year_filter').on('change', () => table.ajax.reload());

        $(document).on('keydown', (event) => {
            if (event.key === "Escape") {
                if (!budgetModal.hasClass('hidden')) hideModal(budgetModal);
                if (!importExcelModal.hasClass('hidden')) hideModal(importExcelModal);
            }
        });

        $('#select_all_ids').on('click', function() {
            const isChecked = $(this).prop('checked');
            table.column(0, { page: 'current' }).nodes().to$().find('.checkbox_ids').prop('checked', isChecked).trigger('change');
        });


        $('#standardBudgetsTable tbody').on('change', '.checkbox_ids', function() {
            // Check if any checkbox (across all pages if filtered, or current page if not) is checked
            const anyCheckedOverall = table.rows({ search: 'applied' }).nodes().to$().find('.checkbox_ids:checked').length > 0;
            $('#btnBulkDelete').toggle(anyCheckedOverall);

            // Update select_all_ids checkbox based on current page's checkboxes
            const allCheckboxesOnPage = table.column(0, { page: 'current' }).nodes().to$().find('.checkbox_ids');
            const checkedCheckboxesOnPage = allCheckboxesOnPage.filter(':checked');
            $('#select_all_ids').prop('checked', allCheckboxesOnPage.length > 0 && checkedCheckboxesOnPage.length === allCheckboxesOnPage.length);
        });


        $('#btnBulkDelete').on('click', function() {
            const $thisButton = $(this); // Store reference
            let ids = table.rows({ search: 'applied' }).nodes().to$().find('.checkbox_ids:checked').map((_, el) => $(el).val()).get();

            if (ids.length === 0) {
                showAjaxNotification('Pilih minimal satu data untuk dihapus.', 'warning');
                return;
            }

            Swal.fire({
                title: `Yakin hapus ${ids.length} data terpilih?`,
                text: "Tindakan ini tidak dapat dibatalkan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                returnFocus: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('standard-budgets.bulk-delete') }}",
                        type: 'DELETE',
                        data: {
                            ids: ids,
                            _token: csrfToken
                        },
                        success: (response) => {
                            table.ajax.reload(null, false);
                            $('#select_all_ids').prop('checked', false); // Uncheck header
                            $thisButton.hide(); // Hide the bulk delete button as no items are selected now
                            showAjaxNotification(response.success, 'success');
                        },
                        error: (xhr) => {
                            showAjaxNotification(xhr.responseJSON?.error || 'Gagal menghapus data terpilih.', 'danger');
                            console.error("Bulk delete error:", xhr.responseText);
                        }
                    });
                }
                // After Swal closes, try to re-focus the button if it's still visible
                if ($thisButton.is(':visible')) {
                     setTimeout(() => {
                        $thisButton.trigger('focus');
                    }, 0);
                }
            });
        });
    });
    </script>
    @endpush
</x-app-layout>