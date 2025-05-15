<x-app-layout>
    @section('title')
        Standard Production
    @endsection
    <div class="content-header">
        <div class="flex items-center justify-between">
            <h4 class="page-title text-2xl font-lg"></h4>
            <div class="inline-flex items-center">
                <nav>
                    <ol class="breadcrumb flex items-center">
                        <li class="breadcrumb-item pr-1"><a href="{{ route('dashboard') }}"><i
                                    class="mdi mdi-home-outline"></i></a></li>
                        <li class="breadcrumb-item pr-1" aria-current="page">Standard</li>
                        <li class="breadcrumb-item active" aria-current="page"> Standard Production</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="content">
        <!-- Add Department Button -->
        <div class="mb-4 flex justify-end">
            <button type="button"
                class="text-white bg-gradient-to-r from-blue-500 via-blue-600 to-blue-700 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-blue-300 dark:focus:ring-blue-800 shadow-lg shadow-blue-500/50 dark:shadow-lg dark:shadow-blue-800/80 font-medium rounded-lg text-base px-3 py-3 text-center me-2 mb-2 float-right"
                data-modal-target="createStandardProductonModal" data-modal-toggle="createStandardProductonModal">
                Add Standard Production
            </button>
        </div>
        <div class="card">
            <div class="card-header">
                <h1 class="card-title text-2xl font-medium"> Standard Production</h1>
            </div>
            <div class="card-body">
                <div class="relative overflow-x-auto sm:rounded-lg">
                    <table id="standardProductionTable"
                        class="table table-striped w-full  text-left rtl:text-right table-bordered">
                        <thead class="uppercase border-b">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-lg">
                                    Line
                                </th>
                                <th scope="col" class="px-6 py-3 text-lg">
                                    Total
                                </th>
                                <th class="px-6 py-3 text-lg text-center">
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($standardProductions as $item)
                                <tr>
                                    <td class="px-6 py-4 text-lg">
                                        {{ $item->line }}
                                    </td>
                                    <td class="px-6 py-4 text-lg">
                                        {{ $item->total }}
                                    </td>
                                    <td class="flex items-center justify-center text-lg space-x-4">
                                        <button type='button'
                                            data-modal-target="createStandardProductonModal-{{ $item->id }}"
                                            data-modal-toggle="createStandardProductonModal-{{ $item->id }}"
                                            class="text-fade btn btn-warning"><i
                                                class="fa-solid fa-pencil text-white"></i>
                                        </button>
                                        <form action="{{ route('dashboard.standard-production.destroy', ['standardproduction' => $item->id]) }}" method="POST"
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
                var table = $('#standardProductionTable').DataTable({
                    "lengthChange": false,
                    "pagingType": "simple_numbers",
                    "dom": 'Bfrtip',
                    "buttons": ['copy', 'csv', 'excel', 'pdf', 'print'],
                    "drawCallback": function(settings) {
                        // Function to reposition modal in the viewport
                        function repositionModal(modalId) {
                            var modal = $('#' + modalId);
                            var modalDialog = modal.find('.modal-dialog');

                            // Calculate top position based on viewport and scroll
                            var modalTop = Math.max(0, ($(window).height() - modalDialog.outerHeight()) /
                                2) + $(window).scrollTop();
                            var modalLeft = Math.max(0, ($(window).width() - modalDialog.outerWidth()) / 2);

                            modalDialog.css({
                                'margin-top': modalTop,
                                'margin-left': modalLeft
                            });
                        }

                        // SweetAlert2 confirmation dialog for delete action
                        $('.delete-form').off('submit').on('submit', function(e) {
                            e.preventDefault(); // Prevent the form from submitting

                            var form = this; // Store a reference to the form

                            Swal.fire({
                                title: 'Apakah Anda yakin?',
                                text: "Anda tidak akan dapat mengembalikan ini!",
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#d33',
                                cancelButtonColor: '#3085d6',
                                confirmButtonText: 'Ya, hapus!',
                                cancelButtonText: 'Batal'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    form.submit(); // Submit the form if confirmed
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

    {{-- Modal Create --}}
    <div id="createStandardProductonModal" tabindex="-1" aria-hidden="true"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Create Standard Production
                    </h3>
                    <button type="button"
                        class="end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                        data-modal-hide="createStandardProductonModal">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewbox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"></path>
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="p-4 md:p-5">
                    <form class="space-y-4" action="{{ route('dashboard.production.standard.store') }}" method="POST">
                        @csrf
                        <div>
                            <label for="line"
                                class="block mb-2 text-xl font-medium text-gray-900 dark:text-white">Nama
                                Line</label>
                            <input type="text" name="line" id="line"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                                placeholder="Line" required="">
                        </div>
                        <div>
                            <label for="Total"
                                class="block mb-2 text-xl font-medium text-gray-900 dark:text-white">Nama
                                Total</label>
                            <input type="text" name="total" id="total"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                                placeholder="Total" required="">
                        </div>
                        <button type="submit"
                            class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Create</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    {{-- Modal Edit --}}
    @foreach ($standardProductions as $item)
        <div id="createStandardProductonModal-{{ $item->id }}" tabindex="-1" aria-hidden="true"
            class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
            <div class="relative p-4 w-full max-w-md max-h-full">
                <!-- Modal content -->
                <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                    <!-- Modal header -->
                    <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                            Edit Standard Production
                        </h3>
                        <button type="button"
                            class="end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                            data-modal-hide="createStandardProductonModal-{{ $item->id }}">
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewbox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"></path>
                            </svg>
                            <span class="sr-only">Close modal</span>
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="p-4 md:p-5">
                        <form class="space-y-4"
                            action="{{ route('dashboard.standard-production.update', ['standardproduction' => $item->id]) }}"
                            method="POST">
                            @csrf
                            @method('PUT')
                            <div>
                                <label for="department_name"
                                    class="block mb-2 text-xl font-medium text-gray-900 dark:text-white">Nama
                                    Line</label>
                                <input type="text" name="line" id="line"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                                    placeholder="Line" required=""
                                    value="{{ $item->line }}">
                            </div>
                            <div>
                                <label for="total"
                                    class="block mb-2 text-xl font-medium text-gray-900 dark:text-white">Nama
                                    Line</label>
                                <input type="number" name="total" id="total"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                                    placeholder="Total" required=""
                                    value="{{ $item->total }}">
                            </div>
                            <button type="submit"
                                class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Edit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach


</x-app-layout>
