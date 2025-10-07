<x-app-layout>
    @section('title')
        Data Penjualan
    @endsection

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <style>
        /* ... (CSS Anda tetap di sini, tidak perlu diubah) ... */
    </style>

    <div class="py-12" 
         x-data="{ isShopeeModalOpen: false, isTokopediaModalOpen: false }"
         x-init="
            $watch('isTokopediaModalOpen', value => {
                if (value) {
                    loadTokopediaTable('{{ $tokopedia_ajax_url }}');
                }
            });
            $watch('isShopeeModalOpen', value => {
                if (value) {
                    loadShopeeTable('{{ $shopee_ajax_url }}');
                }
            });
         "
    >
        <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
            
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-white px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Berhasil!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Gagal!</strong>
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Laporan Penjualan per Platform</h1>
                    <p class="mt-1 text-sm text-gray-500">Analisis performa penjualan produk Anda secara terpisah di Shopee dan Tokopedia.</p>
                </div>
                <div class="mt-4 md:mt-0 flex space-x-2 items-center">
                    <form id="filter-form" method="GET" action="{{ route('ecommerce.sales.index') }}" class="flex items-center space-x-2">
                        <div class="flex items-center space-x-2">
                            <label for="start_date_input" class="text-sm font-medium text-gray-700">Dari:</label>
                            <input type="date" id="start_date_input" name="start_date" value="{{ $startDate }}">
                        </div>
                        <div class="flex items-center space-x-2">
                            <label for="end_date_input" class="text-sm font-medium text-gray-700">Sampai:</label>
                            <input type="date" id="end_date_input" name="end_date" value="{{ $endDate }}">
                        </div>
                        <button type="submit" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm font-medium">
                            Filter
                        </button>
                        @if(request()->has('start_date'))
                            <a href="{{ route('ecommerce.sales.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm font-medium">
                                Reset
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            {{-- BAGIAN SHOPEE --}}
            <div class="mb-12 p-6 bg-white rounded-lg shadow-md">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">
                    <img src="https://logospng.org/download/shopee/logo-shopee-1024.png" alt="Shopee Logo" class="inline-block h-8 mr-2">
                    Laporan Penjualan Shopee
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-[#EE4D2D] border border-orange-200 p-6 rounded-lg">
                        <h3 class="text-sm font-medium text-white">Total Pendapatan (Shopee)</h3>
                        <p class="mt-2 text-3xl font-bold text-white">Rp {{ number_format($shopee_summary['total_revenue'], 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-[#EE4D2D] border border-orange-200 p-6 rounded-lg">
                        <h3 class="text-sm font-medium text-white">Total Pesanan (Shopee)</h3>
                        <p class="mt-2 text-3xl font-bold text-white">{{ number_format($shopee_summary['total_orders'], 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-[#EE4D2D] border border-orange-200 p-6 rounded-lg">
                        <h3 class="text-sm font-medium text-white">Produk Terjual (Shopee)</h3>
                        <p class="mt-2 text-3xl font-bold text-white">{{ number_format($shopee_summary['total_products_sold'], 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-[#EE4D2D] border border-orange-200 p-6 rounded-lg">
                        <h3 class="text-sm font-medium text-white">Rata-rata Nilai Pesanan (Shopee)</h3>
                        <p class="mt-2 text-3xl font-bold text-white">Rp {{ number_format($shopee_summary['average_order_value'], 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2">
                         <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-bold">Transaksi Terakhir (Shopee)</h3>
                            <button @click="isShopeeModalOpen = true" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Lihat Semua &rarr;</button>
                        </div>
                        <div class="overflow-x-auto border rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-[#EE4D2D]">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Invoice</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Pelanggan</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($shopee_sales_list as $sale)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600"><a href="#">{{ $sale->order_sn }}</a></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">{{ $sale->recipient_name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 font-semibold">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="px-6 py-4 text-center text-white">Tidak ada data transaksi Shopee.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold mb-4">Produk Terlaris (Shopee)</h3>
                        <div class="border rounded-lg p-4">
                            <ul class="space-y-4">
                                @forelse($shopee_top_products as $product)
                                <li class="flex items-center space-x-4">
                                    <img class="h-14 w-14 rounded-md object-cover" src="{{ $product->image_url }}" alt="{{ $product->item_name }}">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">{{ $product->item_name }}</p>
                                        <p class="text-sm text-white">{{ $product->sold_count }} unit terjual</p>
                                    </div>
                                </li>
                                @empty
                                 <p class="text-sm text-white">Tidak ada produk terlaris.</p>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            {{-- BAGIAN TOKOPEDIA --}}
            <div class="p-6 bg-white rounded-lg shadow-md">
                 <h2 class="text-2xl font-bold text-gray-800 mb-6">
                    <img src="https://assets.tokopedia.net/assets-tokopedia-lite/v2/zeus/production/e5b8438b.svg" alt="Tokopedia Logo" class="inline-block h-8 mr-2">
                    Laporan Penjualan Tokopedia
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                     <div class="bg-[#42B549] border border-green-200 p-6 rounded-lg">
                        <h3 class="text-sm font-medium text-white">Total Pendapatan (Tokopedia)</h3>
                        <p class="mt-2 text-3xl font-bold text-white">Rp {{ number_format($tokopedia_summary['total_revenue'], 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-[#42B549] border border-green-200 p-6 rounded-lg">
                        <h3 class="text-sm font-medium text-white">Total Pesanan (Tokopedia)</h3>
                        <p class="mt-2 text-3xl font-bold text-white">{{ number_format($tokopedia_summary['total_orders'], 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-[#42B549] border border-green-200 p-6 rounded-lg">
                        <h3 class="text-sm font-medium text-white">Produk Terjual (Tokopedia)</h3>
                        <p class="mt-2 text-3xl font-bold text-white">{{ number_format($tokopedia_summary['total_products_sold'], 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-[#42B549] border border-green-200 p-6 rounded-lg">
                        <h3 class="text-sm font-medium text-white">Rata-rata Nilai Pesanan (Tokopedia)</h3>
                        <p class="mt-2 text-3xl font-bold text-white">Rp {{ number_format($tokopedia_summary['average_order_value'], 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2">
                         <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-bold">Transaksi Terakhir (Tokopedia)</h3>
                             <button @click="isTokopediaModalOpen = true" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Lihat Semua &rarr;</button>
                        </div>
                        <div class="overflow-x-auto border rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-[#42B549]">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Invoice</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Pelanggan</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($tokopedia_sales_list as $sale)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600"><a href="#">{{ $sale->tiktok_order_id }}</a></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">{{ $sale->recipient_name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 font-semibold">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="px-6 py-4 text-center text-white">Tidak ada data transaksi untuk rentang tanggal yang dipilih.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold mb-4">Produk Terlaris (Tokopedia)</h3>
                        <div class="border rounded-lg p-4">
                            <ul class="space-y-4">
                                @forelse($tokopedia_top_products as $product)
                                <li class="flex items-center space-x-4">
                                    <img class="h-14 w-14 rounded-md object-cover" src="{{ $product->image_url }}" alt="{{ $product->product_name }}">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">{{ $product->product_name }}</p>
                                        <p class="text-sm text-white">{{ $product->sold_count }} unit terjual</p>
                                    </div>
                                </li>
                                @empty
                                    <p class="text-sm text-white">Tidak ada produk terlaris untuk ditampilkan.</p>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('ecommerce.sales.partials.modal-shopee-sales')
        @include('ecommerce.sales.partials.modal-tokopedia-sales')

    </div>
    
<script>
    function loadShopeeTable(url) {
        const tableContainer = jQuery('#shopee-table-container');
        tableContainer.html('<div class="p-6 text-center py-10"><p class="text-white">Memuat data...</p></div>');
        jQuery.ajax({
            url: url, type: 'GET',
            success: function(response) {
                if (response && response.html) { tableContainer.html(response.html); } 
                else { tableContainer.html('<div class="p-6 text-center py-10"><p class="text-red-500">Format respons tidak valid.</p></div>'); }
            },
            error: function() { tableContainer.html('<div class="p-6 text-center py-10"><p class="text-red-500">Gagal memuat data.</p></div>'); }
        });
    }

    function loadTokopediaTable(url) {
        const tableContainer = jQuery('#tokopedia-table-container');
        tableContainer.html('<div class="p-6 text-center py-10"><p class="text-white">Memuat data...</p></div>');
        jQuery.ajax({
            url: url, type: 'GET',
            success: function(response) {
                if (response && response.html) { tableContainer.html(response.html); } 
                else { tableContainer.html('<div class="p-6 text-center py-10"><p class="text-red-500">Format respons tidak valid.</p></div>'); }
            },
            error: function() { tableContainer.html('<div class="p-6 text-center py-10"><p class="text-red-500">Gagal memuat data.</p></div>'); }
        });
    }

    document.addEventListener("DOMContentLoaded", function() {
        (function($) {
            const shopeeModal = $('#shopee-modal');
            const tokopediaModal = $('#tokopedia-modal');
            let searchTimeout;

            // Event Listener untuk Modal Shopee
            shopeeModal.on('click', '#shopee-pagination-links a', function(e) {
                e.preventDefault(); 
                const paginationUrl = $(this).attr('href');
                if (paginationUrl && paginationUrl !== '#') loadShopeeTable(paginationUrl);
            });
            shopeeModal.on('keyup', '#shopee-search-input', function() {
                clearTimeout(searchTimeout);
                const url = new URL("{{ route('ecommerce.shopee.orders.data') }}");
                url.searchParams.set('search', $(this).val());
                if ($('#start_date_input').val()) url.searchParams.set('start_date', $('#start_date_input').val());
                if ($('#end_date_input').val()) url.searchParams.set('end_date', $('#end_date_input').val());
                searchTimeout = setTimeout(() => loadShopeeTable(url.href), 500);
            });
            $('.sync-form-shopee').on('submit', function() {
                $(this).find('.sync-spinner').removeClass('hidden');
                $(this).find('.sync-text').text('Menyinkronkan...');
                $(this).find('button[type="submit"]').prop('disabled', true);
            });

            // Event Listener untuk Modal Tokopedia
            tokopediaModal.on('click', '#tokopedia-pagination-links a', function(e) {
                e.preventDefault();
                const paginationUrl = $(this).attr('href');
                if (paginationUrl && paginationUrl !== '#') loadTokopediaTable(paginationUrl);
            });
            tokopediaModal.on('keyup', '#tokopedia-search-input', function() {
                clearTimeout(searchTimeout);
                const url = new URL("{{ route('ecommerce.tokopedia.orders.data') }}");
                url.searchParams.set('search', $(this).val());
                if ($('#start_date_input').val()) url.searchParams.set('start_date', $('#start_date_input').val());
                if ($('#end_date_input').val()) url.searchParams.set('end_date', $('#end_date_input').val());
                searchTimeout = setTimeout(() => loadTokopediaTable(url.href), 500);
            });
            $('#sync-form').on('submit', function() { // Asumsi ini untuk Tokopedia
                $('#sync-spinner').removeClass('hidden');
                $('#sync-text').text('Menyinkronkan...');
                $(this).find('button[type="submit"]').prop('disabled', true);
            });

        })(jQuery);
    });
</script>

</x-app-layout>