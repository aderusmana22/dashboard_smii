{{-- filepath: resources/views/page/dataDashboard/production-index.blade.php --}}
<x-app-layout>
    @section('title')
        Production Output
    @endsection

    <div class="content-header">
        <div class="flex items-center justify-between">
            <h4 class="page-title text-2xl font-medium"></h4>
            <div class="inline-flex items-center">
                <nav>
                    <ol class="breadcrumb flex items-center">
                        <li class="breadcrumb-item pr-1"><a href="{{ route('dashboard') }}"><i
                                    class="mdi mdi-home-outline"></i></a></li>
                        <li class="breadcrumb-item pr-1" aria-current="page"> Data Dashboard</li>
                        <li class="breadcrumb-item active" aria-current="page"> Production</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="row">
            <div class="col-12">
                <div class="box">
                    <div class="box-header">
                        <h4 class="page-title text-2xl font-medium">Production</h4>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table id="tableProduction"
                                class="!border-separate table  table-bordered w-full ">
                                <thead>
                                    <tr class="text-dark text-center" >
                                        <th class="text-lg">Transaction Number</th>
                                        <th class="text-lg">Effective Date</th>
                                        <th class="text-lg">Transaction Type</th>
                                        <th class="text-lg">Production Line</th>
                                        <th class="text-lg">Part Number</th>
                                        <th class="text-lg">Description</th>
                                        <th class="text-lg">Quantity in Location</th>
                                        <th class="text-lg">Weight in KG</th>
                                        <th class="text-lg">Line</th>
                                        <th class="text-lg">Part Drawing</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            function showSuccessMessage(message) {
                Swal.fire({
                    title: 'Success!',
                    text: message,
                    icon: 'success',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
            }

            document.addEventListener('DOMContentLoaded', function() {
                var table = $('#tableProduction').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '{{ route("data.production") }}', // Pastikan route sesuai
                    columns: [
                        { data: 'tr_nbr', name: 'tr_nbr' },
                        { data: 'tr_effdate', name: 'tr_effdate' },
                        { data: 'tr_type', name: 'tr_type' },
                        { data: 'tr_prod_line', name: 'tr_prod_line' },
                        { data: 'tr_part', name: 'tr_part' },
                        { data: 'pt_desc1', name: 'pt_desc1' },
                        { data: 'tr_qty_loc', name: 'tr_qty_loc' },
                        { data: 'Weight_in_KG', name: 'Weight_in_KG' },
                        { data: 'Line', name: 'Line' },
                        { data: 'pt_draw', name: 'pt_draw' },
                    ],
                    lengthMenu: [10, 25, 50, { label: 'All', value: -1 }],

                });
                $('#tableProduction').css('width', '100%');
            });

            // Penanganan pesan sukses
            @if (session()->has('success'))
                Swal.fire({
                    icon: 'success',
                    title: '{{ session()->get('success') }}',
                    text: '{{ session()->get('message') }}',
                });
            @endif
        </script>
    @endpush
</x-app-layout>
