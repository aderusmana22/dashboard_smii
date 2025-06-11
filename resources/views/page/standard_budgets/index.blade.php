<x-app-layout>
    @section('title')
    Standar Budget
    @endsection

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="">
        <div class="flex justify-end mb-3">
            <button type="button" class="btn btn-info" id="btnOpenImportModal">Import Excel</button>
            <button type="button" class="ml-2 btn btn-primary" id="btnOpenCreateModal">
                Add Data
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
                    <select id="year_filter" class="form-select w-auto d-inline-block">
                        <option value="">All Years</option>
                        @if(isset($years) && count($years) > 0)
                        @foreach($years as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
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
                            <th style="width:5%;">No</th>
                            <th>Brand Name</th>
                            <th>Name Region</th>
                            <th style="width:15%;">Amount</th>
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
            <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl relative"> {{-- Adjusted max-width --}}
                <div class="flex items-center justify-between px-6 py-3 border-b"> {{-- Adjusted padding --}}
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
                            <label for="amount_modal" class="block text-sm font-medium text-gray-700 mb-1">Amount <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" id="amount_modal" name="amount" class="form-input mt-1 block w-full border-gray-300 rounded-md shadow-sm text-right focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="0.00" required>
                            <div class="text-red-500 text-xs mt-1" id="amount_error"></div>
                        </div>
                        <div>
                            <label for="month_modal" class="block text-sm font-medium text-gray-700 mb-1">Month <span class="text-red-500">*</span></label>
                            <select id="month_modal" name="month" class="form-select mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                <option value="">Choose Mont</option>
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

                <div id="importErrorContainer" class="hidden p-4"></div>

                <form id="importExcelForm" action="{{ route('standard-budgets.import.excel') }}" method="POST" enctype="multipart/form-data" class="p-6">
                    @csrf
                    <div class="mb-4">
                        <p class="text-sm text-gray-600">
                            Pastikan file Excel Anda memiliki header pada <strong>baris ke-2</strong> dan data dimulai dari <strong>baris ke-3</strong>.
                            Kolom yang diperlukan (nama header harus persis):
                        </p>
                        <ul class="list-disc list-inside text-sm text-gray-600 pl-2 my-2">
                            <li><code>brand_name</code> (Teks: Nama Brand)</li>
                            <li><code>name_region</code> (Teks: Kode Region, Negara)</li>
                            <li><code>amount</code> (Angka: Jumlah budget, gunakan format angka standar, misal 1500.75 atau 1500,75)</li>
                            <li><code>month</code> (Angka: Bulan budget, 1-12)</li>
                            <li><code>year</code> (Angka: Tahun budget, 4 digit, min 1990)</li>
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
                                        <td class="border px-2 py-1 text-right">1500000.75</td>
                                        <td class="border px-2 py-1 text-center">1</td>
                                        <td class="border px-2 py-1 text-center">2023</td>
                                    </tr>
                                    <tr>
                                        <td class="border px-2 py-1">Brand XYZ</td>
                                        <td class="border px-2 py-1">China</td>
                                        <td class="border px-2 py-1 text-right">250000</td>
                                        <td class="border px-2 py-1 text-center">3</td>
                                        <td class="border px-2 py-1 text-center">2024</td>
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

    <script>
        $(document).ready(function() {
            const budgetModal = $('#budgetModal');
            const importExcelModal = $('#importExcelModal');
            const csrfToken = $('meta[name="csrf-token"]').attr('content');

            function showBudgetModal() {
                budgetModal.removeClass('hidden');
                $('body').addClass('overflow-hidden');
            }

            function hideBudgetModal() {
                budgetModal.addClass('hidden');
                $('body').removeClass('overflow-hidden');
            }

            function showImportExcelModal() {
                importExcelModal.removeClass('hidden');
                $('body').addClass('overflow-hidden');
            }

            function hideImportExcelModal() {
                importExcelModal.addClass('hidden');
                $('body').removeClass('overflow-hidden');
            }

            function clearValidationErrors(formId = 'budgetForm') {
                $('#' + formId + ' .form-input').removeClass('border-red-500 is-invalid');
                $('#' + formId + ' .form-select').removeClass('border-red-500 is-invalid');
                $('#' + formId + ' .text-red-500').text('');
                $('#' + formId + ' .invalid-feedback').remove();
            }

            function displayValidationErrors(errors, formId = 'budgetForm') {
                $.each(errors, (key, value) => {
                    const inputField = $('#' + key + '_modal, #' + key);
                    inputField.addClass('border-red-500 is-invalid');
                    let errorDiv = $('#' + key + '_error');
                    if (errorDiv.length === 0) {
                        inputField.after('<div class="text-red-500 text-xs mt-1 invalid-feedback" id="' + key + '_error">' + value[0] + '</div>');
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
                $('html, body').animate({
                    scrollTop: alertPlaceholder.offset().top - 20
                }, 'smooth');
                setTimeout(() => {
                    alertDiv.fadeOut(500, function() {
                        $(this).remove();
                    });
                }, 7000);
            }

            $('#alert-placeholder .alert').not('.ajax-notification').not('.alert-danger').delay(7000).slideUp(300, function() {
                $(this).alert('close');
            });
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
                    },
                    error: function(xhr, error, code) {
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
                    infoEmpty: "No data",
                    infoFiltered: "(disaring dari _MAX_ total data)",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    },
                    processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Memuat...</span></div> <span class="ms-2">Memuat data...</span>'
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    { data: 'brand_name', name: 'brand_name' },
                    { data: 'name_region', name: 'name_region' },
                    {
                        data: 'amount',
                        name: 'amount',
                        className: 'text-right',
                        render: function(data, type, row) {
                            return data; // Already formatted from controller
                        }
                    },
                    { data: 'month', name: 'month', className: 'text-center' }, // Display formatted month
                    { data: 'year', name: 'year', className: 'text-center' },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                order: [
                    [5, 'desc'], // year
                    [4, 'asc'],  // month (will sort by formatted month name if server sends it, or by number if not)
                    [1, 'asc'],  // brand_name
                    [2, 'asc']   // name_region
                ]
            });

            $('#btnOpenCreateModal').on('click', () => {
                $('#budgetForm')[0].reset();
                clearValidationErrors();
                $('#budgetModalLabel').text('Add Standard Budget');
                $('#budgetId').val('');
                $('#month_modal').val(new Date().getMonth() + 1);
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
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function(response) {
                        hideBudgetModal();
                        table.ajax.reload(null, false);
                        showAjaxNotification(response.success, 'success');
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            if (errors) {
                                displayValidationErrors(errors);
                                showAjaxNotification('Terdapat kesalahan input. Mohon periksa kembali form.', 'danger');
                            } else if (xhr.responseJSON.error) { // Custom error for uniqueness
                                showAjaxNotification(xhr.responseJSON.error, 'danger');
                            } else {
                                showAjaxNotification('Terdapat kesalahan validasi yang tidak diketahui.', 'danger');
                            }
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
                    $('#brand_name_modal').val(data.brand_name);
                    $('#name_region_modal').val(data.name_region);
                    $('#amount_modal').val(parseFloat(data.amount).toFixed(2)); // Amount is decimal from model
                    $('#month_modal').val(data.month);
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
                            type: 'POST',
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
                $('#excel_file_error').text('');
                $('#importErrorContainer').addClass('hidden').html('');
                showImportExcelModal();
            });
            $('#btnCloseImportModal, #btnCancelImportModal').on('click', hideImportExcelModal);

            $('#importExcelForm').on('submit', function(e) {
                const fileInput = $('#excel_file');
                $('#excel_file_error').text('');

                if (!fileInput.val()) {
                    e.preventDefault();
                    $('#excel_file_error').text('Silakan pilih file Excel untuk diimport.');
                    fileInput.addClass('border-red-500');
                    return false;
                }
                $('#btnSubmitImport').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mengimport...').prop('disabled', true);
                // Form will submit normally, page will reload
            });

            $('#year_filter').on('change', () => table.ajax.reload());

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