<x-app-layout>
    @section('title', 'List Users')

    <div class="content-header">
        <div class="flex items-center justify-between">
            <h4 class="page-title text-2xl font-lg"></h4>
            <div class="inline-flex items-center">
                <nav>
                    <ol class="breadcrumb flex items-center">
                        <li class="breadcrumb-item pr-1"><a href="{{ route('dashboard') }}"><i
                                    class="mdi mdi-home-outline"></i></a></li>
                        <li class="breadcrumb-item pr-1">Users</li>
                        <li class="breadcrumb-item active">List Users</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="content">
        <!-- Add User Button -->
        <div class="mb-4 flex justify-end">
            <button type="button"
                class="text-white bg-gradient-to-r from-blue-500 via-blue-600 to-blue-700 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-blue-300 dark:focus:ring-blue-800 shadow-lg shadow-blue-500/50 dark:shadow-lg dark:shadow-blue-800/80 font-medium rounded-lg text-base px-3 py-3 text-center me-2 mb-2 float-right"
                data-modal-target="createUserModal" data-modal-toggle="createUserModal">
                Add User
            </button>
        </div>

        <div class="card">
            <div class="card-header">
                <h1 class="card-title text-2xl font-medium">List Users</h1>
            </div>
            <div class="card-body">
                <div class="relative overflow-x-auto sm:rounded-lg">
                    <table id="usersTable" class="table table-striped w-full text-left rtl:text-right table-bordered">
                        <thead class="uppercase border-b">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-lg">#</th>
                                <th scope="col" class="px-6 py-3 text-lg">NIK</th>
                                <th scope="col" class="px-6 py-3 text-lg">Username</th>
                                <th scope="col" class="px-6 py-3 text-lg">Name</th>
                                <th scope="col" class="px-6 py-3 text-lg">Email</th>
                                <th class="px-6 py-3 text-lg text-center">Position</th>
                                <th class="px-6 py-3 text-lg text-center">Department</th>
                                <th class="px-6 py-3 text-lg text-center">Role</th>
                                <th class="px-6 py-3 text-lg text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td class="px-6 py-4 text-lg">{{ $loop->iteration }}</td>
                                    <td class="px-6 py-4 text-lg">{{ $user->nik }}</td>
                                    <td class="px-6 py-4 text-lg">{{ $user->username }}</td>
                                    <td class="px-6 py-4 text-lg">{{ $user->name }}</td>
                                    <td class="px-6 py-4 text-lg">{{ $user->email }}</td>
                                    <td class="px-6 py-4 text-lg">{{ $user->position->position_name }}</td>
                                    <td class="px-6 py-4 text-lg">{{ $user->department->department_name }}
                                    <td class="px-6 py-4 text-lg">
                                        @foreach ($user->roles as $role)
                                            <span class="badge bg-primary">{{ $role->name }}</span>
                                        @endforeach
                                    </td>
                                    </td>
                                    <td class="flex items-center justify-center text-lg space-x-4">
                                        <button type="button" class="text-fade btn btn-warning"
                                            data-modal-target="editUserModal-{{ $user->id }}"
                                            data-modal-toggle="editUserModal-{{ $user->id }}">
                                            <i class="fa-solid fa-pencil text-white"></i>
                                        </button>
                                        <form action="{{ url('/users/' . $user->id . '/delete') }}" method="POST"
                                            class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-fade btn btn-danger">
                                                <i class="fas fa-trash-alt text-white"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            $(document).ready(function() {
                // DataTable initialization
                var table = $('#usersTable').DataTable({
                    "lengthChange": false,
                    "pagingType": "simple_numbers",
                    "dom": 'Bfrtip',
                    "buttons": ['copy', 'csv', 'excel', 'pdf', 'print'],
                    "drawCallback": function(settings) {
                        // Modal reposition function
                        function repositionModal(modalId) {
                            var modal = $('#' + modalId);
                            var modalDialog = modal.find('.modal-dialog');
                            var modalTop = Math.max(0, ($(window).height() - modalDialog.outerHeight()) /
                                2) + $(window).scrollTop();
                            var modalLeft = Math.max(0, ($(window).width() - modalDialog.outerWidth()) / 2);
                            modalDialog.css({
                                'margin-top': modalTop,
                                'margin-left': modalLeft
                            });
                        }

                        // SweetAlert2 confirmation for delete action
                        $('.delete-form').on('submit', function(e) {
                            e.preventDefault();
                            var form = this;
                            Swal.fire({
                                title: 'Are you sure?',
                                text: "You won't be able to revert this!",
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#d33',
                                cancelButtonColor: '#3085d6',
                                confirmButtonText: 'Yes, delete it!',
                                cancelButtonText: 'Cancel'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    form.submit();
                                }
                            });
                        });

                        // Trigger reposition on modal show event
                        $('[data-modal-toggle]').off('click').on('click', function() {
                            var target = $(this).data('modal-target');
                            $('#' + target).removeClass('hidden').addClass('flex').attr(
                                'aria-modal', 'true').attr('role', 'dialog');
                            repositionModal(target); // Reposition modal when shown
                        });

                        $('[data-modal-hide]').off('click').on('click', function() {
                            var target = $(this).data('modal-hide');
                            $('#' + target).addClass('hidden').removeClass('flex').removeAttr(
                                'aria-modal').removeAttr('role');
                        });
                    }
                });

                @if ($errors->any())
                    $('#createUserModal').modal('show');
                @endif
                // Success message handling
                @if (session()->has('success'))
                    Swal.fire({
                        icon: 'success',
                        title: '{{ session()->get('success') }}',
                        text: '{{ session()->get('message') }}',
                    });
                @endif
            });
        </script>
    @endpush
    {{-- {-- Create User Modal --} --}}
    <div id="createUserModal" tabindex="-1" aria-hidden="true"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-4xl max-h-full">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700" style="margin-top: 10%;">
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                    <h3 class="text-3xl font-semibold text-white">Create User</h3>
                    <button type="button"
                        class="end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                        data-modal-hide="createUserModal">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewbox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"></path>
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <div class="p-4 md:p-5">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form class="space-y-4" action="{{ route('users.store') }}" method="POST" id="createUserForm">
                        @csrf
                        <div class="grid grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="form-label text-white text-xl">NIK<span
                                        class="text-danger">*</span></label>
                                <div class="controls">
                                    <input type="text" name="nik" id="nik"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-xl rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                                        required placeholder="NIK">
                                    <div class="help-block"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label text-white text-xl">Username<span
                                        class="text-danger">*</span></label>
                                <div class="controls">
                                    <input type="text" name="username" id="username"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-xl rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                                        required placeholder="Username">
                                    <div class="help-block"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label text-white text-xl">Name<span
                                        class="text-danger">*</span></label>
                                <div class="controls">
                                    <input type="text" name="name" id="name"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-xl rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                                        required placeholder="User Name">
                                    <div class="help-block"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label text-white text-xl">Email <span
                                        class="text-danger">*</span></label>
                                <div class="controls">
                                    <input type="email" name="email" id="email"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-xl rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                                        required placeholder="Email">
                                    <div class="help-block"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label text-white text-xl">Password</label>
                                <div class="controls">
                                    <input type="password" name="password"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-xl rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                                        placeholder="Password">
                                    <div class="help-block"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label text-white text-xl">Position <span
                                        class="text-danger">*</span></label>
                                <div class="controls">
                                    <select name="position_id" id="position_id" required=""
                                        class="form-select w-full text-xl" aria-invalid="false"
                                        placeholder="Position">
                                        @foreach ($positions as $position)
                                            <option value="{{ $position->id }}">{{ $position->position_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="help-block"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label text-white text-xl">Department <span
                                        class="text-danger">*</span></label>
                                <div class="controls">
                                    <select name="department_id" id="department_id" required=""
                                        class="form-select w-full text-xl" aria-invalid="false"
                                        placeholder="Department">
                                        @foreach ($departments as $department)
                                            <option value="{{ $department->id }}">{{ $department->department_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="help-block"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label text-white text-xl">Roles</label>
                                <div class="controls">
                                    <select name="roles[]" class="form-select w-full select2 text-xl" multiple
                                        style="width: 100%" required>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role }}">{{ $role }}</option>
                                        @endforeach
                                    </select>
                                    <div class="help-block"></div>
                                </div>
                            </div>
                        </div>
                        <button type="submit"
                            class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xl px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Create</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    @foreach ($users as $user)
        <div id="editUserModal-{{ $user->id }}" tabindex="-1" aria-hidden="true"
            class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
            <div class="relative p-4 w-full max-w-4xl max-h-full">
                <div class="relative bg-white rounded-lg shadow dark:bg-gray-700" style="margin-top: 10%;">
                    <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                        <h3 class="text-3xl font-semibold text-white">Edit User</h3>
                        <button type="button"
                            class="end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                            data-modal-hide="editUserModal-{{ $user->id }}">
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewbox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"></path>
                            </svg>
                            <span class="sr-only">Close modal</span>
                        </button>
                    </div>
                    <div class="p-4 md:p-5 overflow-y-auto max-h-[calc(100vh-10rem)]">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <form action="{{ route('users.update', $user) }}" method="POST" class="space-y-4" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="grid grid-cols-2 gap-4">
                                <div class="form-group">
                                    <label class="form-label text-white text-xl">NIK<span
                                            class="text-danger">*</span></label>
                                    <div class="controls">
                                        <input type="text" name="nik" value="{{ $user->nik }}"
                                            id="nik"
                                            class="bg-gray-50 border border-gray-300 text-gray-900 text-xl rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                                            required placeholder="NIK">
                                        <div class="help-block"></div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label text-white text-xl">Username<span
                                            class="text-danger">*</span></label>
                                    <div class="controls">
                                        <input type="text" name="username" value="{{ $user->username }}"
                                            id="username"
                                            class="bg-gray-50 border border-gray-300 text-gray-900 text-xl rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                                            required placeholder="Username">
                                        <div class="help-block"></div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label text-white text-xl">Name<span
                                            class="text-danger">*</span></label>
                                    <div class="controls">
                                        <input type="text" name="name" value="{{ $user->name }}"
                                            id="name"
                                            class="bg-gray-50 border border-gray-300 text-gray-900 text-xl rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                                            required placeholder="User Name">
                                        <div class="help-block"></div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label text-white text-xl">Email <span
                                            class="text-danger">*</span></label>
                                    <div class="controls">
                                        <input type="email" name="email" value="{{ $user->email }}"
                                            id="email"
                                            class="bg-gray-50 border border-gray-300 text-gray-900 text-xl rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                                            required placeholder="Email">
                                        <div class="help-block"></div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label text-white text-xl">Password</label>
                                    <div class="controls">
                                        <input type="password" name="password"
                                            class="bg-gray-50 border border-gray-300 text-gray-900 text-xl rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                                            placeholder="Password">
                                        <div class="help-block"></div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label text-white text-xl">Position <span
                                            class="text-danger">*</span></label>
                                    <div class="controls">
                                        <select name="position_id" id="position_id" required=""
                                            class="form-select w-full text-xl" aria-invalid="false"
                                            placeholder="Position">
                                            @foreach ($positions as $position)
                                                <option value="{{ $position->id }}"
                                                    {{ $user->position_id == $position->id ? 'selected' : '' }}>
                                                    {{ $position->position_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="help-block"></div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label text-white text-xl">Department <span
                                            class="text-danger">*</span></label>
                                    <div class="controls">
                                        <select name="department_id" id="department_id" required=""
                                            class="form-select w-full text-xl" aria-invalid="false"
                                            placeholder="Department">
                                            @foreach ($departments as $department)
                                                <option value="{{ $department->id }}"
                                                    {{ $user->department_id == $department->id ? 'selected' : '' }}>
                                                    {{ $department->department_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="help-block"></div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label text-white text-xl">Roles</label>
                                    <div class="controls">
                                        <select name="roles[]" class="form-select w-full select2 text-xl p-5" multiple
                                            style="width: 100%" required>
                                            @foreach ($roles as $role)
                                                <option value="{{ $role }}"
                                                    {{ $user->hasRole($role) ? 'selected' : '' }}>
                                                    {{ $role }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="help-block"></div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label text-white text-xl">Avatar</label>
                                    <div class="controls">
                                        @if ($user->avatar)
                                            <img id="avatar-preview-{{ $user->id }}" src="{{ Storage::url('public/user_avatars/' . $user->avatar) }}" alt="Avatar" class="mb-4" style="width: 100px; height: 100px;">
                                        @else
                                            <img id="avatar-preview-{{ $user->id }}" src="#" alt="Avatar" class="mb-4 hidden" style="width: 100px; height: 100px;">
                                        @endif
                                        <input type="file" name="avatar" id="avatar-{{ $user->id }}"
                                            class="bg-gray-50 border border-gray-300 text-gray-900 text-xl rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                                            placeholder="Avatar" onchange="previewAvatar({{ $user->id }})">
                                        <div class="help-block"></div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label text-white text-xl">Status <span
                                            class="text-danger">*</span></label>
                                    <div class="controls">
                                        <select name="status" id="status" required=""
                                            class="form-select w-full text-xl" aria-invalid="false"
                                            placeholder="Status">
                                            <option value="active" {{ $user->status == 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="non active" {{ $user->status == 'non active' ? 'selected' : '' }}>Non Active</option>
                                        </select>
                                        <div class="help-block"></div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label text-white text-xl">Password Sim</label>
                                    <div class="controls">
                                        <input type="text" name="passwordsim" value="{{ $user->passwordsim }}"
                                            class="bg-gray-50 border border-gray-300 text-gray-900 text-xl rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                                            placeholder="Password Sim">
                                        <div class="help-block"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="w-full mt-4">
                                <button type="submit"
                                    class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xl px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <script>
        function previewAvatar(userId) {
            var fileInput = document.getElementById('avatar-' + userId);
            var file = fileInput.files[0];
            var reader = new FileReader();

            reader.onloadend = function () {
                var img = document.getElementById('avatar-preview-' + userId);
                img.src = reader.result;
                img.classList.remove('hidden');
            }

            if (file) {
                reader.readAsDataURL(file);
            } else {
                var img = document.getElementById('avatar-preview-' + userId);
                img.src = "";
                img.classList.add('hidden');
            }
        }
    </script>

    @push('scripts')
        <script>
            $(document).ready(function() {
                // Initialize Select2
                $('.select2').select2({
                    width: 'resolve'
                });
            });

            document.addEventListener('DOMContentLoaded', function() {
                var form = document.getElementById('createUserForm');

                form.addEventListener('submit', function(event) {
                    // Check if the form is valid
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }

                    // Add custom validation checks if necessary
                    var isValid = true;

                    // Example custom validation (you can add more checks here)
                    var email = document.getElementById('email').value;
                    if (!email.includes('@')) {
                        isValid = false;
                        alert('Please enter a valid email address.');
                    }

                    // If the form is invalid, prevent submission
                    if (!isValid) {
                        event.preventDefault();
                        event.stopPropagation();
                    }

                    // Add the Bootstrap was-validated class to display validation feedback
                    form.classList.add('was-validated');
                });
            });
        </script>
    @endpush
</x-app-layout>
