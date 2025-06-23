<x-app-layout>

    @section('title')
    Manajemen Department Approver
    @endsection

    <style>
        /* Ensure modals are hidden by default */
        .modal {
            display: none !important;
        }

        .modal.show {
            display: block !important;
        }

        /* Ensure no backdrop shows on load */
        .modal-backdrop {
            display: none;
        }

        .modal-backdrop.show {
            display: block;
        }
    </style>

    {{-- Main content area --}}
    <div class="px-4 sm:px-6 lg:px-8 mt-4">
        <div class="flex flex-wrap items-center justify-between mb-3">
            <div class="w-full sm:w-auto mb-2 sm:mb-0">
                <h1 class="text-2xl font-semibold mb-0">Manajemen Department Approver</h1>
            </div>
            <div class="w-full sm:w-auto text-left sm:text-right">
                <button type="button" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring focus:ring-blue-200 disabled:opacity-25 transition" id="btnTambahApprover">
                    <i class="fas fa-plus mr-1"></i> Tambah Approver
                </button>
            </div>
        </div>

        {{-- Card with bg-white dark:bg-gray-800 --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm dark:shadow-lg rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                    Daftar Department Approver
                </h3>
            </div>
            <div class="p-6">
                <div id="approversTableContainer">
                    @if($approvers->isEmpty())
                    <div class="bg-blue-100 dark:bg-blue-900/50 border-l-4 border-blue-500 dark:border-blue-400 text-blue-700 dark:text-blue-300 p-4 text-center" role="alert">
                        <p>Belum ada data department approver.</p>
                    </div>
                    @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">#</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Departemen</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama Approver</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">NIK Approver</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email Approver</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            {{-- Table body also uses bg-white dark:bg-gray-800 --}}
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($approvers as $index => $approver)
                                <tr id="approver-row-{{ $approver->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $approvers->firstItem() + $index }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $approver->department->department_name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $approver->user->name ?? 'User Tidak Ditemukan' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $approver->user_nik }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $approver->user->email ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $approver->status === 'active' ? 
                                                'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100' : 
                                                'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200' }}">
                                            {{ ucfirst($approver->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium space-x-1">
                                        <button type="button" class="p-1 text-sky-600 hover:text-sky-900 dark:text-sky-400 dark:hover:text-sky-300 focus:outline-none btn-show" title="Lihat Detail" data-id="{{ $approver->id }}" data-url="{{ route('department-approvers.show', $approver->id) }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="p-1 text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300 focus:outline-none btn-edit" title="Edit" data-id="{{ $approver->id }}" data-url="{{ route('department-approvers.edit', $approver->id) }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="p-1 text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 focus:outline-none btn-delete" title="Hapus" data-id="{{ $approver->id }}" data-url="{{ route('department-approvers.destroy', $approver->id) }}" data-name="{{ $approver->user->name ?? $approver->user_nik }}">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 flex justify-center">
                        {{ $approvers->links() }} 
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Universal Modal for Create/Edit - CENTERED & LARGER -->
    <div class="modal fade d-none" id="approverModal" tabindex="-1" aria-labelledby="approverModalLabel" aria-hidden="true">
        <div class="modal-dialog fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 m-0 z-[1055] w-[800px] max-w-[90vw]">
            {{-- Modal content with bg-white dark:bg-gray-800 --}}
            <div class="modal-content relative flex flex-col w-full pointer-events-auto bg-white dark:bg-gray-800 bg-clip-padding border border-gray-300 dark:border-gray-600 rounded-lg shadow-xl outline-none">
                <div class="modal-header flex flex-shrink-0 items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700 rounded-t-md">
                    <h5 class="text-xl font-medium leading-normal text-gray-800 dark:text-gray-100" id="approverModalLabel">Form Approver</h5>
                    <button type="button" class="box-content w-4 h-4 p-1 text-black dark:text-gray-300 border-none rounded-none opacity-50 focus:shadow-none focus:outline-none focus:opacity-100 hover:text-black dark:hover:text-white hover:opacity-75 hover:no-underline" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body relative p-4" style="max-height: 70vh; overflow-y: auto;">
                    <form id="approverForm">
                        @csrf
                        <input type="hidden" name="_method" id="formMethod">
                        <input type="hidden" name="approver_id" id="approverId">

                        <div class="mb-4">
                            <label for="department_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Departemen <span class="text-red-500 dark:text-red-400">*</span></label>
                            {{-- Select with bg-white dark:bg-gray-700 --}}
                            <select class="mt-1 block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-indigo-500 dark:focus:border-indigo-400 sm:text-sm" id="department_id" name="department_id" required>
                                <option value="" disabled selected class="text-gray-500 dark:text-gray-400">Pilih Departemen...</option>
                            </select>
                            <div class="text-red-500 dark:text-red-400 text-xs mt-1" id="department_id_error"></div>
                        </div>

                        <div class="mb-4">
                            <label for="user_nik" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">User (Approver) <span class="text-red-500 dark:text-red-400">*</span></label>
                            {{-- Select with bg-white dark:bg-gray-700 --}}
                            <select class="mt-1 block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-indigo-500 dark:focus:border-indigo-400 sm:text-sm" id="user_nik" name="user_nik" required>
                                <option value="" disabled selected class="text-gray-500 dark:text-gray-400">Pilih User Approver...</option>
                            </select>
                            <div class="text-red-500 dark:text-red-400 text-xs mt-1" id="user_nik_error"></div>
                            <div class="text-yellow-600 dark:text-yellow-400 text-xs mt-1" id="user_nik_warning_empty" style="display:none;">Tidak ada user yang memenuhi kriteria atau tersedia.</div>
                        </div>

                        <div class="mb-4">
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status <span class="text-red-500 dark:text-red-400">*</span></label>
                            {{-- Select with bg-white dark:bg-gray-700 --}}
                            <select class="mt-1 block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-indigo-500 dark:focus:border-indigo-400 sm:text-sm" id="status" name="status" required>
                            </select>
                            <div class="text-red-500 dark:text-red-400 text-xs mt-1" id="status_error"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer flex flex-shrink-0 flex-wrap items-center justify-end p-4 border-t border-gray-200 dark:border-gray-700 rounded-b-md space-x-2">
                    <button type="button" class="px-4 py-2 bg-transparent hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold border border-gray-300 dark:border-gray-500 rounded-md text-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring focus:ring-blue-200 disabled:opacity-25 transition" id="btnSaveApprover">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- Detail Modal - CENTERED & LARGER -->
    <div class="modal fade d-none" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 m-0 z-[1055] w-[700px] max-w-[90vw]">
            {{-- Modal content with bg-white dark:bg-gray-800 --}}
            <div class="modal-content relative flex flex-col w-full pointer-events-auto bg-white dark:bg-gray-800 bg-clip-padding border border-gray-300 dark:border-gray-600 rounded-lg shadow-xl outline-none">
                <div class="modal-header flex flex-shrink-0 items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700 rounded-t-md">
                    <h5 class="text-xl font-medium leading-normal text-gray-800 dark:text-gray-100" id="detailModalLabel">Detail Department Approver</h5>
                    <button type="button" class="box-content w-4 h-4 p-1 text-black dark:text-gray-300 border-none rounded-none opacity-50 focus:shadow-none focus:outline-none focus:opacity-100 hover:text-black dark:hover:text-white hover:opacity-75 hover:no-underline" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body relative p-4" id="detailModalBody" style="max-height: 70vh; overflow-y: auto;">
                    <p class="text-center text-gray-500 dark:text-gray-400">Memuat detail...</p>
                </div>
                <div class="modal-footer flex flex-shrink-0 flex-wrap items-center justify-end p-4 border-t border-gray-200 dark:border-gray-700 rounded-b-md">
                    <button type="button" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-md text-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>


    @push('scripts')
    <script>
        // JavaScript remains the same as previous correct version
        $(document).ready(function() {
            // Setup CSRF token for all AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Initialize Bootstrap Modals
            const approverModalElement = document.getElementById('approverModal');
            const approverModal = new bootstrap.Modal(approverModalElement); 
            const detailModalElement = document.getElementById('detailModal');
            const detailModal = new bootstrap.Modal(detailModalElement); 

            const allDepartments = @json($departments ?? []);
            const allUsers = @json($users ?? []);
            const allStatuses = @json($statuses ?? []);

            function populateDropdown(selectElement, data, selectedValue = null, placeholderText = null, addEmptyOption = true) {
                selectElement.empty();
                if (placeholderText && addEmptyOption) {
                    selectElement.append($('<option>', {
                        value: '',
                        text: placeholderText,
                        disabled: true,
                        selected: !selectedValue,
                        class: 'text-gray-500 dark:text-gray-400'
                    }));
                }
                $.each(data, function(value, text) {
                    const option = $('<option>', {
                        value: value,
                        text: text
                    });
                    if (selectedValue !== null && String(value) === String(selectedValue)) {
                        option.prop('selected', true);
                    }
                    selectElement.append(option);
                });
                selectElement.trigger('change');
            }

            function resetForm() {
                const form = $('#approverForm');
                form[0].reset();
                form.find('.border-red-500').removeClass('border-red-500').addClass('border-gray-300 dark:border-gray-600');
                form.find('.text-red-500.text-xs.mt-1, .dark\\:text-red-400.text-xs.mt-1').text('');


                $('#user_nik_warning_empty').hide();
                $('#user_nik').prop('disabled', false);
                $('#approverId').val('');
                $('#formMethod').val('');

                populateDropdown($('#department_id'), allDepartments, null, 'Pilih Departemen...');
                populateDropdown($('#user_nik'), allUsers, null, 'Pilih User Approver...');
                if ($.isEmptyObject(allUsers)) {
                    $('#user_nik_warning_empty').show();
                    $('#user_nik').append($('<option>', {
                        value: '',
                        text: 'Tidak ada user tersedia',
                        disabled: true,
                        class: 'text-gray-500 dark:text-gray-400'
                    })).prop('disabled', true);
                }
                populateDropdown($('#status'), allStatuses, 'active');
            }

            $('#btnTambahApprover').on('click', function() {
                resetForm();
                $('#approverModalLabel').text('Tambah Department Approver Baru');
                $('#formMethod').val('POST');
                $('#btnSaveApprover').html('<i class="fas fa-save mr-1"></i> Simpan')
                    .removeClass('bg-yellow-500 hover:bg-yellow-600').addClass('bg-blue-600 hover:bg-blue-500');
                approverModal.show();
            });

            $('body').on('click', '.btn-edit', function() {
                const approverId = $(this).data('id');
                const url = $(this).data('url');
                resetForm();
                $('#approverModalLabel').text('Edit Department Approver');
                $('#formMethod').val('PUT');
                $('#approverId').val(approverId);
                $('#btnSaveApprover').html('<i class="fas fa-sync-alt mr-1"></i> Perbarui')
                    .removeClass('bg-blue-600 hover:bg-blue-500').addClass('bg-yellow-500 hover:bg-yellow-600');

                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.approver) {
                            populateDropdown($('#department_id'), response.departments || allDepartments, response.approver.department_id, 'Pilih Departemen...');
                            let usersForEdit = {
                                ...(response.users || allUsers)
                            };
                            if (response.currentUser && response.approver.user_nik && !usersForEdit[response.approver.user_nik]) {
                                usersForEdit[response.approver.user_nik] = `${response.currentUser.name} (NIK: ${response.approver.user_nik}) (Saat Ini)`;
                            }
                            populateDropdown($('#user_nik'), usersForEdit, response.approver.user_nik, 'Pilih User Approver...');
                            if ($.isEmptyObject(usersForEdit)) {
                                $('#user_nik_warning_empty').show();
                                $('#user_nik').append($('<option>', {
                                    value: '',
                                    text: 'Tidak ada user tersedia',
                                    disabled: true,
                                    class: 'text-gray-500 dark:text-gray-400'
                                })).prop('disabled', true);
                            } else {
                                $('#user_nik').prop('disabled', false);
                            }
                            populateDropdown($('#status'), response.statuses || allStatuses, response.approver.status);
                            approverModal.show();
                        } else {
                            Swal.fire('Error!', 'Data approver tidak ditemukan.', 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', 'Gagal memuat data untuk edit. ' + (xhr.responseJSON?.message || xhr.statusText), 'error');
                    }
                });
            });

            $('#btnSaveApprover').on('click', function() {
                const form = $('#approverForm');
                const method = $('#formMethod').val();
                const approverId = $('#approverId').val();
                let url = "{{ route('department-approvers.store') }}";
                if (method === 'PUT' && approverId) {
                    url = "{{ url('department-approvers') }}/" + approverId;
                }

                form.find('.border-red-500').removeClass('border-red-500').addClass('border-gray-300 dark:border-gray-600');
                form.find('.text-red-500.text-xs.mt-1, .dark\\:text-red-400.text-xs.mt-1').text('');
                $(this).prop('disabled', true).html('<span class="animate-spin rounded-full h-4 w-4 border-t-2 border-b-2 border-white mr-2" role="status" aria-hidden="true"></span> Menyimpan...');

                $.ajax({
                    url: url,
                    type: 'POST', 
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        approverModal.hide();
                        Swal.fire('Sukses!', response.success, 'success');
                        $("#approversTableContainer").load(window.location.href + " #approversTableContainer > *", function() {
                        });
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) { 
                            const errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                $('#' + key).removeClass('border-gray-300 dark:border-gray-600').addClass('border-red-500 dark:border-red-500'); 
                                $('#' + key + '_error').text(value[0]); 
                            });
                            Swal.fire('Validasi Gagal!', 'Mohon periksa input Anda.', 'warning');
                        } else {
                            Swal.fire('Error!', xhr.responseJSON?.message || xhr.responseJSON?.error || 'Terjadi kesalahan saat menyimpan.', 'error');
                        }
                    },
                    complete: function() {
                        const saveButtonText = method === 'PUT' ? '<i class="fas fa-sync-alt mr-1"></i> Perbarui' : '<i class="fas fa-save mr-1"></i> Simpan';
                        $('#btnSaveApprover').prop('disabled', false).html(saveButtonText);
                    }
                });
            });

            $('body').on('click', '.btn-delete', function() {
                const approverId = $(this).data('id');
                const url = $(this).data('url');
                const approverName = $(this).data('name') || 'approver ini';
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: `Anda akan menghapus ${approverName}. Tindakan ini tidak dapat dibatalkan!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e53e3e',
                    cancelButtonColor: '#718096', 
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'POST', 
                            data: {
                                _method: 'DELETE'
                            },
                            dataType: 'json',
                            success: function(response) {
                                Swal.fire('Dihapus!', response.success, 'success');
                                $('#approver-row-' + approverId).fadeOut(function() {
                                    $(this).remove();
                                    if ($('#approversTableContainer tbody tr').length === 0) {
                                        if ($('.pagination').length > 0 || window.location.search.includes('page=')) {
                                            $("#approversTableContainer").load(window.location.pathname + " #approversTableContainer > *"); 
                                        } else {
                                            $("#approversTableContainer").load(window.location.href + " #approversTableContainer > *");
                                        }
                                    }
                                });
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON?.error || xhr.responseJSON?.message || 'Gagal menghapus data.', 'error');
                            }
                        });
                    }
                });
            });

            $('body').on('click', '.btn-show', function() {
                const url = $(this).data('url');
                $('#detailModalBody').html('<div class="text-center p-3"><span class="inline-block animate-spin rounded-full h-5 w-5 border-t-2 border-b-2 border-gray-900 dark:border-gray-100" role="status" aria-hidden="true"></span> <span class="dark:text-gray-300">Memuat detail...</span></div>');
                detailModal.show();

                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        if (!data || $.isEmptyObject(data)) {
                            $('#detailModalBody').html('<p class="text-center text-red-500 dark:text-red-400">Gagal memuat detail: Data tidak valid.</p>');
                            return;
                        }
                        let statusBadge = `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${data.status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100' : 'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200'}">${data.status ? (data.status.charAt(0).toUpperCase() + data.status.slice(1)) : 'N/A'}</span>`;
                        let departmentName = data.department ? data.department.department_name : 'N/A';
                        let userName = data.user ? data.user.name : 'User Tidak Ditemukan';
                        let userEmail = data.user ? data.user.email : '-';
                        const createdAt = data.created_at ? new Date(data.created_at).toLocaleString('id-ID', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        }) : '-';
                        const updatedAt = data.updated_at ? new Date(data.updated_at).toLocaleString('id-ID', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        }) : '-';

                        const content = `
                            <div class="space-y-2 text-sm">
                                <div class="flex"><dt class="w-1/3 font-medium text-gray-500 dark:text-gray-400">ID</dt><dd class="w-2/3 text-gray-900 dark:text-gray-100">${data.id || 'N/A'}</dd></div>
                                <div class="flex"><dt class="w-1/3 font-medium text-gray-500 dark:text-gray-400">Departemen</dt><dd class="w-2/3 text-gray-900 dark:text-gray-100">${departmentName}</dd></div>
                                <div class="flex"><dt class="w-1/3 font-medium text-gray-500 dark:text-gray-400">Nama Approver</dt><dd class="w-2/3 text-gray-900 dark:text-gray-100">${userName}</dd></div>
                                <div class="flex"><dt class="w-1/3 font-medium text-gray-500 dark:text-gray-400">NIK Approver</dt><dd class="w-2/3 text-gray-900 dark:text-gray-100">${data.user_nik || 'N/A'}</dd></div>
                                <div class="flex"><dt class="w-1/3 font-medium text-gray-500 dark:text-gray-400">Email Approver</dt><dd class="w-2/3 text-gray-900 dark:text-gray-100">${userEmail}</dd></div>
                                <div class="flex"><dt class="w-1/3 font-medium text-gray-500 dark:text-gray-400">Status</dt><dd class="w-2/3 text-gray-900 dark:text-gray-100">${statusBadge}</dd></div>
                                <div class="flex"><dt class="w-1/3 font-medium text-gray-500 dark:text-gray-400">Dibuat Pada</dt><dd class="w-2/3 text-gray-900 dark:text-gray-100">${createdAt}</dd></div>
                                <div class="flex"><dt class="w-1/3 font-medium text-gray-500 dark:text-gray-400">Diperbarui Pada</dt><dd class="w-2/3 text-gray-900 dark:text-gray-100">${updatedAt}</dd></div>
                            </div>`;
                        $('#detailModalBody').html(content);
                    },
                    error: function(xhr) {
                        $('#detailModalBody').html('<p class="text-center text-red-500 dark:text-red-400">Gagal memuat detail. ' + (xhr.responseJSON?.message || xhr.statusText) + '</p>');
                    }
                });
            });

            $(document).on('click', '#approversTableContainer .pagination a', function(event) {
                event.preventDefault();
                var pageUrl = $(this).attr('href');
                if (pageUrl && pageUrl !== '#') {
                    $("#approversTableContainer").html('<div class="text-center p-5"><span class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-gray-900 dark:border-gray-100" role="status" aria-hidden="true"></span> <span class="dark:text-gray-300">Memuat...</span></div>')
                        .load(pageUrl + " #approversTableContainer > *");
                }
            });

            approverModalElement.addEventListener('shown.bs.modal', function() {
                $('#department_id', this).focus();
            });
        });
    </script>
    @endpush
</x-app-layout>