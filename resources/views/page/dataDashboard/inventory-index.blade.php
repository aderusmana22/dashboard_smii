{{-- filepath: resources/views/page/dataDashboard/inventory-index.blade.php --}}
<x-app-layout>
    @section('title')
        Inventory
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
                        <li class="breadcrumb-item active" aria-current="page"> Inventory</li>
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
                        <h4 class="page-title text-2xl font-medium">Inventory</h4>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table id="tableInventory"
                                class="!border-separate table table-bordered w-full ">
                                <thead>
                                    <tr class="text-dark text-center">
                                        <th>Part Number</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Quantity</th>
                                        <th>UM</th>
                                        <th>Date</th>
                                        <th>Location</th>
                                        <th>Lot Number</th>
                                        <th>Aging Days</th>
                                        <th>Expiration Date</th>
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
                var table = $('#tableInventory').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '{{ route("dashboard.inventory") }}', // Pastikan route sesuai
                    columns: [
                        { data: 'ld_part', name: 'ld_part' },
                        { data: 'pt_desc1', name: 'pt_desc1' },
                        { data: 'ld_status', name: 'ld_status' },
                        { data: 'ld_qty_oh', name: 'ld_qty_oh' },
                        { data: 'pt_um', name: 'pt_um' },
                        { data: 'ld_date', name: 'ld_date' },
                        { data: 'ld_loc', name: 'ld_loc' },
                        { data: 'ld_lot', name: 'ld_lot' },
                        { data: 'aging_days', name: 'aging_days' },
                        { data: 'ld_expire', name: 'ld_expire' },
                    ],
                    lengthMenu: [10, 25, 50, { label: 'All', value: -1 }],
                   
                });
                $('#tableInventory').css('width', '100%');
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
