<x-app-layout>

    @section('title')
    Department Approver Management
    @endsection

    <style>
        table.table.table-bordered td,
        table.table.table-bordered th {
            border: 1px solid rgb(102, 110, 117) !important;
        }

        div.card-header {
            border-bottom: 1px solid rgb(102, 110, 117) !important;
        }

        [data-bs-theme="dark"] .table-bordered th,
        [data-bs-theme="dark"] .table-bordered td {
            border-color: #495057 !important;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 1055;
            background-color: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
        }

        .modal.show {
            display: flex !important;
            animation: fadeIn 0.3s ease-out;
        }

        .modal-dialog {
            max-width: 800px;
            width: 90%;
            pointer-events: none;
            transform: translateY(-30px);
            transition: transform 0.3s ease-out;
        }

        .modal.show .modal-dialog {
            pointer-events: auto;
            transform: translateY(0);
        }

        .modal-content {
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            background-color: #fff;
        }

        [data-bs-theme="dark"] .modal-content {
            background-color: #212529;
        }


        .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }

        .modal-backdrop {
            display: none;
        }

        .modal-backdrop.show {
            display: block;
            z-index: 1050;
            background-color: rgba(0, 0, 0, 0.5);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        #approversTableContainer table {
            width: 100% !important;
            table-layout: fixed;
        }

        #approversTableContainer th,
        #approversTableContainer td {
            word-wrap: break-word;
            white-space: normal;
        }

        .card,
        .card-body,
        .container,
        .container-fluid {
            position: static !important;
            z-index: auto !important;
        }

        #btnTambahApprover {
            margin-left: auto;
        }
    </style>


    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Department Approver Management</h3>
                        <button type="button" class="btn btn-primary" id="btnTambahApprover">
                            <i class="fas fa-plus me-1"></i> Add Approver
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="approversTableContainer">
                            @if($approvers->isEmpty())
                            <div class="alert alert-info text-center" role="alert">
                                <p class="mb-0">No department approver data found.</p>
                            </div>
                            @else
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Department</th>
                                        <th scope="col">Approver Name</th>
                                        <th scope="col">Approver NIK</th>
                                        <th scope="col">Approver Email</th>
                                        <th scope="col">Status</th>
                                        <th scope="col" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($approvers as $index => $approver)
                                    <tr id="approver-row-{{ $approver->id }}">
                                        <td>{{ $approvers->firstItem() + $index }}</td>
                                        <td>{{ $approver->department->department_name ?? 'N/A' }}</td>
                                        <td>{{ $approver->user->name ?? 'User Not Found' }}</td>
                                        <td>{{ $approver->user_nik }}</td>
                                        <td>{{ $approver->user->email ?? '-' }}</td>
                                        <td>
                                            <span class="badge {{ $approver->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                                {{ ucfirst($approver->status) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-info me-1 btn-show" title="View Details"
                                                data-id="{{ $approver->id }}"
                                                data-url="{{ route('department-approvers.show', $approver->id) }}">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-warning me-1 btn-edit" title="Edit"
                                                data-id="{{ $approver->id }}"
                                                data-url="{{ route('department-approvers.edit', $approver->id) }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger btn-delete" title="Delete"
                                                data-id="{{ $approver->id }}"
                                                data-url="{{ route('department-approvers.destroy', $approver->id) }}"
                                                data-name="{{ $approver->user->name ?? $approver->user_nik }}">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="mt-3 d-flex justify-content-center">
                                {{ $approvers->links() }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="approverModal" tabindex="-1" aria-labelledby="approverModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="approverModalLabel">Approver Form</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <form id="approverForm">
                        @csrf
                        <input type="hidden" name="_method" id="formMethod">
                        <input type="hidden" name="approver_id" id="approverId">

                        <div class="mb-3">
                            <label for="department_id" class="form-label">Department <span class="text-danger">*</span></label>
                            <select class="form-select" id="department_id" name="department_id" required>
                                <option value="" disabled selected>Select Department...</option>
                            </select>
                            <div class="invalid-feedback d-block" id="department_id_error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="user_nik" class="form-label">User (Approver) <span class="text-danger">*</span></label>
                            <select class="form-select" id="user_nik" name="user_nik" required>
                                <option value="" disabled selected>Select Approver User...</option>
                            </select>
                            <div class="invalid-feedback d-block" id="user_nik_error"></div>
                            <div class="text-warning small mt-1" id="user_nik_warning_empty" style="display:none;">No users available or matching the criteria.</div>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                            </select>
                            <div class="invalid-feedback d-block" id="status_error"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="btnSaveApprover">
                        <i class="fas fa-save me-1"></i> Save
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel">Department Approver Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="detailModalBody" style="max-height: 70vh; overflow-y: auto;">
                    <p class="text-center text-muted">Loading details...</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

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
            }

            function resetForm() {
                const form = $('#approverForm');
                form[0].reset();
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback').text('');

                $('#user_nik_warning_empty').hide();
                $('#user_nik').prop('disabled', false);
                $('#approverId').val('');
                $('#formMethod').val('');

                populateDropdown($('#department_id'), allDepartments, null, 'Select Department...');
                populateDropdown($('#user_nik'), allUsers, null, 'Select Approver User...');
                if ($.isEmptyObject(allUsers)) {
                    $('#user_nik_warning_empty').show();
                    $('#user_nik').append($('<option>', {
                        value: '',
                        text: 'No users available',
                        disabled: true
                    })).prop('disabled', true);
                }
                populateDropdown($('#status'), allStatuses, 'active', null, false);
            }

            $('#btnTambahApprover').on('click', function() {
                resetForm();
                $('#approverModalLabel').text('Add New Department Approver');
                $('#formMethod').val('POST');
                $('#btnSaveApprover').html('<i class="fas fa-save me-1"></i> Save')
                    .removeClass('btn-warning').addClass('btn-primary');
                approverModal.show();
            });

            $('body').on('click', '.btn-edit', function() {
                const approverId = $(this).data('id');
                const url = $(this).data('url');
                resetForm();
                $('#approverModalLabel').text('Edit Department Approver');
                $('#formMethod').val('PUT');
                $('#approverId').val(approverId);
                $('#btnSaveApprover').html('<i class="fas fa-sync-alt me-1"></i> Update')
                    .removeClass('btn-primary').addClass('btn-warning');

                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.approver) {
                            populateDropdown($('#department_id'), response.departments || allDepartments, response.approver.department_id, 'Select Department...');
                            let usersForEdit = {
                                ...(response.users || allUsers)
                            };
                            if (response.currentUser && response.approver.user_nik && !usersForEdit[response.approver.user_nik]) {
                                usersForEdit[response.approver.user_nik] = `${response.currentUser.name} (NIK: ${response.approver.user_nik}) (Current)`;
                            }
                            populateDropdown($('#user_nik'), usersForEdit, response.approver.user_nik, 'Select Approver User...');

                            if ($.isEmptyObject(usersForEdit) && !response.approver.user_nik) {
                                $('#user_nik_warning_empty').show();
                                $('#user_nik').append($('<option>', {
                                    value: '',
                                    text: 'No users available',
                                    disabled: true
                                })).prop('disabled', true);
                            } else {
                                $('#user_nik').prop('disabled', false);
                            }

                            populateDropdown($('#status'), response.statuses || allStatuses, response.approver.status, null, false);
                            approverModal.show();
                        } else {
                            Swal.fire('Error!', 'Approver data not found.', 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', 'Failed to load data for editing. ' + (xhr.responseJSON?.message || xhr.statusText), 'error');
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

                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback').text('');
                $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        approverModal.hide();
                        Swal.fire('Success!', response.success, 'success');
                        $("#approversTableContainer").load(window.location.href + " #approversTableContainer > *");
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                $('#' + key).addClass('is-invalid');
                                $('#' + key + '_error').text(value[0]);
                            });
                            Swal.fire('Validation Failed!', 'Please check your input.', 'warning');
                        } else {
                            Swal.fire('Error!', xhr.responseJSON?.message || xhr.responseJSON?.error || 'An error occurred while saving.', 'error');
                        }
                    },
                    complete: function() {
                        const saveButtonText = method === 'PUT' ? '<i class="fas fa-sync-alt me-1"></i> Update' : '<i class="fas fa-save me-1"></i> Save';
                        $('#btnSaveApprover').prop('disabled', false).html(saveButtonText);
                    }
                });
            });

            $('body').on('click', '.btn-delete', function() {
                const approverId = $(this).data('id');
                const url = $(this).data('url');
                const approverName = $(this).data('name') || 'this approver';
                Swal.fire({
                    title: 'Are you sure?',
                    text: `You are about to delete ${approverName}. This action cannot be undone!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel',
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
                                Swal.fire('Deleted!', response.success, 'success');
                                $('#approver-row-' + approverId).fadeOut(function() {
                                    $(this).remove();
                                    if ($('#approversTableContainer tbody tr').length === 0) {
                                        $("#approversTableContainer").load(window.location.pathname + " #approversTableContainer > *");
                                    }
                                });
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON?.error || xhr.responseJSON?.message || 'Failed to delete data.', 'error');
                            }
                        });
                    }
                });
            });

            $('body').on('click', '.btn-show', function() {
                const url = $(this).data('url');
                $('#detailModalBody').html('<div class="text-center p-3"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div> <span class="text-muted">Loading details...</span></div>');
                detailModal.show();

                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        if (!data || $.isEmptyObject(data)) {
                            $('#detailModalBody').html('<p class="text-center text-danger">Failed to load details: Invalid data.</p>');
                            return;
                        }
                        let statusBadge = `<span class="badge ${data.status === 'active' ? 'bg-success' : 'bg-secondary'}">${data.status ? (data.status.charAt(0).toUpperCase() + data.status.slice(1)) : 'N/A'}</span>`;
                        let departmentName = data.department ? data.department.department_name : 'N/A';
                        let userName = data.user ? data.user.name : 'User Not Found';
                        let userEmail = data.user ? data.user.email : '-';
                        const createdAt = data.created_at ? new Date(data.created_at).toLocaleString('en-US', {
                            year: 'numeric',
                            month: 'short',
                            day: '2-digit',
                            hour: '2-digit',
                            minute: '2-digit'
                        }) : '-';
                        const updatedAt = data.updated_at ? new Date(data.updated_at).toLocaleString('en-US', {
                            year: 'numeric',
                            month: 'short',
                            day: '2-digit',
                            hour: '2-digit',
                            minute: '2-digit'
                        }) : '-';

                        const content = `
                            <dl class="row">
                                <dt class="col-sm-4">ID</dt><dd class="col-sm-8">${data.id || 'N/A'}</dd>
                                <dt class="col-sm-4">Department</dt><dd class="col-sm-8">${departmentName}</dd>
                                <dt class="col-sm-4">Approver Name</dt><dd class="col-sm-8">${userName}</dd>
                                <dt class="col-sm-4">Approver NIK</dt><dd class="col-sm-8">${data.user_nik || 'N/A'}</dd>
                                <dt class="col-sm-4">Approver Email</dt><dd class="col-sm-8">${userEmail}</dd>
                                <dt class="col-sm-4">Status</dt><dd class="col-sm-8">${statusBadge}</dd>
                                <dt class="col-sm-4">Created At</dt><dd class="col-sm-8">${createdAt}</dd>
                                <dt class="col-sm-4">Updated At</dt><dd class="col-sm-8">${updatedAt}</dd>
                            </dl>`;
                        $('#detailModalBody').html(content);
                    },
                    error: function(xhr) {
                        $('#detailModalBody').html('<p class="text-center text-danger">Failed to load details. ' + (xhr.responseJSON?.message || xhr.statusText) + '</p>');
                    }
                });
            });

            $(document).on('click', '#approversTableContainer .pagination a', function(event) {
                event.preventDefault();
                var pageUrl = $(this).attr('href');
                if (pageUrl && pageUrl !== '#') {
                    $("#approversTableContainer").html('<div class="text-center p-5"><div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status"><span class="visually-hidden">Loading...</span></div> <p class="mt-2 text-muted">Loading...</p></div>')
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