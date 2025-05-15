<x-app-layout>
    @section('title')
        Sales
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
                        <li class="breadcrumb-item active" aria-current="page"> Sales</li>
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
                        <h4 class="page-title text-2xl font-medium">Sales</h4>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table id="tableSales"
                                class="display responsive nowrap table table-bordered w-full">
                                <thead>
                                    <tr class="text-dark text-center">
                                        <th>Tr Number</th>
                                        <th>Address</th>
                                        <th>Effective Date</th>
                                        <th>Ton</th>
                                        <th>Region</th>
                                        <th>Remarks</th>
                                        <th>Comment Code</th>
                                        <th>Margin</th>
                                        <th>Value</th>
                                        <th>Description</th>
                                        <th>Prod Line</th>
                                        <th>Prod Line Desc</th>
                                        <th>Address Name</th>
                                        <th>Sales Order</th>
                                        <th>Sales Name</th>
                                        <th>Part</th>
                                        <th>Drawing</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data akan diisi oleh DataTables AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <!-- DataTables Responsive JS -->
        <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
        <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css"/>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var table = $('#tableSales').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: '{{ route("data.sales") }}', // Pastikan route sesuai
                    columns: [
                        { data: 'tr_trnbr', name: 'tr_trnbr' },
                        { data: 'tr_addr', name: 'tr_addr' },
                        { data: 'tr_effdate', name: 'tr_effdate' },
                        { data: 'tr_ton', name: 'tr_ton' },
                        { data: 'cm_region', name: 'cm_region' },
                        { data: 'cm_rmks', name: 'cm_rmks' },
                        { data: 'code_cmmt', name: 'code_cmmt' },
                        { data: 'margin', name: 'margin' },
                        { data: 'value', name: 'value' },
                        { data: 'pt_desc1', name: 'pt_desc1' },
                        { data: 'pt_prod_line', name: 'pt_prod_line' },
                        { data: 'pl_desc', name: 'pl_desc' },
                        { data: 'ad_name', name: 'ad_name' },
                        { data: 'tr_slspsn', name: 'tr_slspsn' },
                        { data: 'sales_name', name: 'sales_name' },
                        { data: 'pt_part', name: 'pt_part' },
                        { data: 'pt_draw', name: 'pt_draw' },
                    ],
                    lengthMenu: [10, 25, 50, { label: 'All', value: -1 }],
                });
                $('#tableSales').css('width', '100%');
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
