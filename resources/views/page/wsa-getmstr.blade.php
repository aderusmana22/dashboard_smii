<x-app-layout>
    @section('title')
        Get WSA
    @endsection
    <div class="content-header">
        <div class="flex items-center justify-between">

            <h4 class="page-title text-2xl font-medium">WSA Get Master Data</h4>
            <div class="inline-flex items-center">
                <nav>
                    <ol class="breadcrumb flex items-center">
                        <li class="breadcrumb-item pr-1"><a href="{{ route('dashboard') }}"><i
                                    class="mdi mdi-home-outline"></i></a></li>
                        <li class="breadcrumb-item pr-1" aria-current="page"> WSA</li>
                        <li class="breadcrumb-item active" aria-current="page"> Get Master Data</li>
                    </ol>
                </nav>
            </div>

        </div>
    </div>

    <section class="content">
    <div class="flex flex-wrap justify-center py-8 gap-4">
        <div class="inline-flex flex-wrap justify-between gap-4">
            
            <form method="POST" action="{{ route('dashboard.production.wsa') }}">
                @csrf
                <button type="submit" class="box pull-up bg-green-500 p-4">
                    <div class="box-body">
                        <div class="flex justify-between items-center">
                            <div class="bs-5 ps-10">
                                <h4 class="text-white mb-10">Master Production</h4>
                                <h2 class="my-0 fw-700 text-3xl">Get</h2>
                            </div>
                            <div class="icon">
                                <i class="fa-solid fa-user-check me-0 fs-24 rounded-3"></i>
                            </div>
                        </div>
                    </div>
                </button>
            </form>
            <form method="POST" action="{{ route('dashboard.inventory.wsa') }}">
                @csrf
                <button type="submit" class="box pull-up bg-green-500 p-4">
                    <div class="box-body">
                        <div class="flex justify-between items-center">
                            <div class="bs-5 ps-10">
                                <h4 class="text-white mb-10">Master Inventory</h4>
                                <h2 class="my-0 fw-700 text-3xl">Get</h2>
                            </div>
                            <div class="icon">
                                <i class="fa-solid fa-user-check me-0 fs-24 rounded-3"></i>
                            </div>
                        </div>
                    </div>
                </button>
            </form>
            <form method="POST" action="{{ route('dashboard.sales.wsa') }}">
                @csrf
                <button type="submit" class="box pull-up bg-green-500 p-4">
                    <div class="box-body">
                        <div class="flex justify-between items-center">
                            <div class="bs-5 ps-10">
                                <h4 class="text-white mb-10">Master Sales</h4>
                                <h2 class="my-0 fw-700 text-3xl">Get</h2>
                            </div>
                            <div class="icon">
                                <i class="fa-solid fa-user-check me-0 fs-24 rounded-3"></i>
                            </div>
                        </div>
                    </div>
                </button>
            </form>

             <form method="POST" action="{{ route('dashboard.standardshipment.wsa') }}">
                @csrf
                <button type="submit" class="box pull-up bg-blue-500 p-4">
                    <div class="box-body">
                        <div class="flex justify-between items-center">
                            <div class="bs-5 ps-10">
                                <h4 class="text-white mb-10">Master Standard Shipment</h4>
                                <h2 class="my-0 fw-700 text-3xl">Get</h2>
                            </div>
                            <div class="icon">
                                <i class="fa-solid fa-user-check me-0 fs-24 rounded-3"></i>
                            </div>
                        </div>
                    </div>
                </button>
            </form>
        </div>
    </div>
    </section>
    @push('scripts')
    <script>
        $(document).ready(function() {
            var toastMessage = "<?php echo session('toastMessage'); ?>";
            var toastType = "<?php echo session('toastType'); ?>";

            if (toastMessage) {
                $.toast({
                    heading: toastType === 'success' ? 'Success' : 'Error',
                    text: toastMessage,
                    showHideTransition: 'slide', // Can be 'fade', 'slide', or 'plain'
                    icon: toastType,
                    position: 'top-right',
                    loaderBg: '#ff6849', // Background color of the loader bar
                    hideAfter: 5000 // Duration before the toast disappears in milliseconds
                });
            }
        });
    </script>
    @endpush

</x-app-layout>
