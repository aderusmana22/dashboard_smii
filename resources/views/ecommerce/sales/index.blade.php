<x-app-layout>
    @section('title')
        Data Penjualan
    @endsection

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>


    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script>
    
            <style>
        .dark-skin .bg-white { background-color: rgb(31 41 55 / 1); }
        .dark-skin .bg-gray-50 { background-color: rgb(55 65 81 / 1); }
        .dark-skin .bg-gray-100 { background-color: rgb(55 65 81 / 1); }
        .dark-skin .divide-gray-200> :not([hidden])~ :not([hidden]) { border-color: rgb(55 65 81 / 1); }
        .dark-skin .text-gray-900 { color: rgb(249 250 251 / 1); }
        .dark-skin .text-gray-800 { color: rgb(229 231 235 / 1); }
        .dark-skin .text-gray-700 { color: rgb(209 213 219 / 1); }
        .dark-skin .text-gray-500 { color: rgb(209 213 219 / 1); }
        .dark-skin .border-gray-300 { border-color: rgb(75 85 99 / 1); }
        .dark-skin .text-indigo-600 { color: #818cf8; }
        .dark-skin .text-indigo-600:hover { color: #a5b4fc; }
        .dark-skin .text-red-600 { color: #f87171; }
        .dark-skin .text-red-600:hover { color: #fca5a5; }
        .dark-skin .modal-cancel-button { background-color: rgb(75 85 99 / 1); color: rgb(229 231 235 / 1); }
        .dark-skin .modal-cancel-button:hover { background-color: rgb(107 114 128 / 1); }
    </style>
    
<style>
    /* ================================================== */
    /*      CSS Override - Tema Off-White & Hitam         */
    /* ================================================== */

    /* Sembunyikan label default */
    .dt-search label,
    .dt-length label {
      display: none !important;
    }

    /* Gaya untuk input dan select */
    .dt-search input,
    .dt-length select {
      background-color: #ffffff !important;
      border: 1px solid #d1d5db !important;
      color: #000000 !important;
      border-radius: 0.375rem !important;
      box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05) !important;
      font-size: 0.875rem !important;
      padding: 0.5rem 1.25rem !important;
    }
    .dt-search input:focus,
    .dt-length select:focus {
      outline: 2px solid transparent !important;
      outline-offset: 2px !important;
      border-color: #000000 !important;
      box-shadow: 0 0 0 1px #000000 !important;
    }

    /* Gaya untuk teks info */
    .dt-info {
      font-size: 0.875rem !important;
      color: #000000 !important;
    }

    /* --- PAGINASI DENGAN TEMA OFF-WHITE --- */

    /* Aturan dasar untuk tombol di KEDUA tabel */
    #shopee-sales-table_wrapper .dt-paging button,
    #tokopedia-sales-table_wrapper .dt-paging button {
      position: relative !important;
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      padding: 0.5rem 1rem !important;
      border: 1px solid #d1d5db !important;
      background-color: #f9fafb !important; /* DIUBAH: Warna Off-White / Putih Tulang */
      background-image: none !important;
      font-size: 0.875rem !important;
      font-weight: 500 !important;
      color: #000000 !important; /* Teks Hitam */
      transition: background-color 0.15s ease-in-out !important; /* Transisi diaktifkan kembali */
      box-shadow: none !important;
    }

    /* Aturan HOVER untuk KEDUA tabel */
    #shopee-sales-table_wrapper .dt-paging button:hover,
    #tokopedia-sales-table_wrapper .dt-paging button:hover {
      background-color: #f3f4f6 !important; /* DIUBAH: Warna Abu-abu Sangat Terang saat hover */
      background-image: none !important;
      color: #000000 !important;
    }

    /* Aturan untuk tombol AKTIF di KEDUA tabel */
    #shopee-sales-table_wrapper .dt-paging button.current,
    #tokopedia-sales-table_wrapper .dt-paging button.current {
      background-color: #f9fafb !important; /* DIUBAH: Kembali ke Off-White */
      background-image: none !important;
      border-color: #000000 !important; /* Border hitam sebagai penanda aktif */
      font-weight: 700 !important; /* Teks tebal sebagai penanda aktif */
    }

    /* Aturan untuk tombol non-aktif */
    .dt-paging button.disabled {
      opacity: 0.5 !important;
      cursor: not-allowed !important;
    }

    /* Sudut membulat */
    .dt-paging button:first-child {
      border-top-left-radius: 0.375rem !important;
      border-bottom-left-radius: 0.375rem !important;
    }
    .dt-paging button:last-child {
      border-top-right-radius: 0.375rem !important;
      border-bottom-right-radius: 0.375rem !important;
    }
</style>

    @php
        // Data Dummy untuk Ringkasan Shopee
        $shopee_summary = [
            'total_revenue' => 125850000,
            'total_orders' => 480,
            'total_products_sold' => 912,
            'average_order_value' => 262187,
        ];

        // Data Dummy Produk Terlaris Shopee
        $shopee_top_products = [
            ['image_url' => 'https://placehold.co/100x100/f97316/white?text=SP1', 'name' => 'Tas Ransel Eiger Pro', 'sold_count' => 152],
            ['image_url' => 'https://placehold.co/100x100/f97316/white?text=SP2', 'name' => 'Sepatu Lari Adidas Run', 'sold_count' => 110],
            ['image_url' => 'https://placehold.co/100x100/f97316/white?text=SP3', 'name' => 'Kaos Polos Cotton Combed', 'sold_count' => 98],
        ];

        // Data Dummy untuk Ringkasan Tokopedia
        $tokopedia_summary = [
            'total_revenue' => 98500000,
            'total_orders' => 355,
            'total_products_sold' => 780,
            'average_order_value' => 277465,
        ];
        
        // Data Dummy Produk Terlaris Tokopedia
        $tokopedia_top_products = [
            ['image_url' => 'https://placehold.co/100x100/4ade80/white?text=TK1', 'name' => 'Headphone Gaming Pro X', 'sold_count' => 121],
            ['image_url' => 'https://placehold.co/100x100/4ade80/white?text=TK2', 'name' => 'Mouse Wireless Logitech', 'sold_count' => 95],
            ['image_url' => 'https://placehold.co/100x100/4ade80/white?text=TK3', 'name' => 'Keyboard Mechanical Rexus', 'sold_count' => 88],
        ];

        // Data RINGKAS untuk tabel di halaman utama
        $shopee_sales_list = [
            ['invoice_id' => 'INV/SP/2023/00123', 'platform' => 'Shopee', 'customer_name' => 'Andi Wijaya', 'date' => '2023-10-26 14:30', 'total_amount' => 350000, 'status' => 'Selesai'],
            ['invoice_id' => 'INV/SP/2023/00122', 'platform' => 'Shopee', 'customer_name' => 'Bunga Citra', 'date' => '2023-10-26 11:15', 'total_amount' => 185000, 'status' => 'Selesai'],
            ['invoice_id' => 'INV/SP/2023/00121', 'platform' => 'Shopee', 'customer_name' => 'Rian Hidayat', 'date' => '2023-10-25 20:05', 'total_amount' => 75000, 'status' => 'Dibatalkan'],
             ['invoice_id' => 'INV/SP/2023/00123', 'platform' => 'Shopee', 'customer_name' => 'Andi Wijaya', 'date' => '2023-10-26 14:30', 'total_amount' => 350000, 'status' => 'Selesai'],
            ['invoice_id' => 'INV/SP/2023/00122', 'platform' => 'Shopee', 'customer_name' => 'Bunga Citra', 'date' => '2023-10-26 11:15', 'total_amount' => 185000, 'status' => 'Selesai'],
            ['invoice_id' => 'INV/SP/2023/00121', 'platform' => 'Shopee', 'customer_name' => 'Rian Hidayat', 'date' => '2023-10-25 20:05', 'total_amount' => 75000, 'status' => 'Dibatalkan'],
             ['invoice_id' => 'INV/SP/2023/00123', 'platform' => 'Shopee', 'customer_name' => 'Andi Wijaya', 'date' => '2023-10-26 14:30', 'total_amount' => 350000, 'status' => 'Selesai'],
            ['invoice_id' => 'INV/SP/2023/00122', 'platform' => 'Shopee', 'customer_name' => 'Bunga Citra', 'date' => '2023-10-26 11:15', 'total_amount' => 185000, 'status' => 'Selesai'],
            ['invoice_id' => 'INV/SP/2023/00121', 'platform' => 'Shopee', 'customer_name' => 'Rian Hidayat', 'date' => '2023-10-25 20:05', 'total_amount' => 75000, 'status' => 'Dibatalkan'],
        ];
        $tokopedia_sales_list = [
            ['invoice_id' => 'INV/TKP/2023/00543', 'platform' => 'Tokopedia', 'customer_name' => 'Fajar Nugroho', 'date' => '2023-10-26 16:00', 'total_amount' => 420000, 'status' => 'Selesai'],
            ['invoice_id' => 'INV/TKP/2023/00542', 'platform' => 'Tokopedia', 'customer_name' => 'Gita Permata', 'date' => '2023-10-26 12:45', 'total_amount' => 210000, 'status' => 'Selesai'],
        ];

        // Data LENGKAP untuk ditampilkan di dalam Modal
        $all_shopee_sales = array_merge($shopee_sales_list, [
            ['invoice_id' => 'INV/SP/2023/00120', 'platform' => 'Shopee', 'customer_name' => 'Dewi Lestari', 'date' => '2023-10-25 18:45', 'total_amount' => 550000, 'status' => 'Selesai'],
            ['invoice_id' => 'INV/SP/2023/00119', 'platform' => 'Shopee', 'customer_name' => 'Siska Amelia', 'date' => '2023-10-25 15:00', 'total_amount' => 210000, 'status' => 'Selesai'],
            ['invoice_id' => 'INV/SP/2023/00118', 'platform' => 'Shopee', 'customer_name' => 'Bambang Susilo', 'date' => '2023-10-24 10:10', 'total_amount' => 89000, 'status' => 'Selesai'],
        ]);
        $all_tokopedia_sales = array_merge($tokopedia_sales_list, [
            ['invoice_id' => 'INV/TKP/2023/00541', 'platform' => 'Tokopedia', 'customer_name' => 'Hendra Setiawan', 'date' => '2023-10-25 19:30', 'total_amount' => 115000, 'status' => 'Selesai'],
            ['invoice_id' => 'INV/TKP/2023/00540', 'platform' => 'Tokopedia', 'customer_name' => 'Lina Marlina', 'date' => '2023-10-24 21:00', 'total_amount' => 320000, 'status' => 'Dibatalkan'],
            ['invoice_id' => 'INV/TKP/2023/00539', 'platform' => 'Tokopedia', 'customer_name' => 'Yoga Pratama', 'date' => '2023-10-24 14:20', 'total_amount' => 150000, 'status' => 'Selesai'],
        ]);
    @endphp

    {{-- ====================================================================== --}}
    {{--                   BAGIAN 3: HALAMAN UTAMA & STRUKTUR                   --}}
    {{-- ====================================================================== --}}
    
    <div class="py-12" x-data="{ isShopeeModalOpen: false, isTokopediaModalOpen: false }">
        <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Laporan Penjualan per Platform</h1>
                    <p class="mt-1 text-sm text-gray-500">Analisis performa penjualan produk Anda secara terpisah di Shopee dan Tokopedia.</p>
                </div>
                <div class="mt-4 md:mt-0 flex space-x-2">
                    <button class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm font-medium">
                        <svg class="w-5 h-5 inline-block mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v10a1 1 0 01-1 1H4a1 1 0 01-1-1V10zM15 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4zM15 17a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1v-2z" /></svg>
                        Filter
                    </button>
                    <button class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium">
                        <svg class="w-5 h-5 inline-block mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        Export Data
                    </button>
                </div>
            </div>

            {{-- ... (Sisa dari BAGIAN 3 dan 4 tetap sama, tidak perlu diubah) ... --}}
            {{-- ****************************************************** --}}
            {{-- *                  BAGIAN SHOPEE                     * --}}
            {{-- ****************************************************** --}}
            <div class="mb-12 p-6 bg-white rounded-lg shadow-md">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">
                    <img src="https://logospng.org/download/shopee/logo-shopee-1024.png" alt="Shopee Logo" class="inline-block h-8 mr-2">
                    Laporan Penjualan Shopee
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-sm font-medium text-gray-500">Total Pendapatan (Shopee)</h3>
                        <p class="mt-2 text-3xl font-bold text-gray-900">Rp {{ number_format($shopee_summary['total_revenue'], 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-sm font-medium text-gray-500">Total Pesanan (Shopee)</h3>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($shopee_summary['total_orders'], 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-sm font-medium text-gray-500">Produk Terjual (Shopee)</h3>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($shopee_summary['total_products_sold'], 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-sm font-medium text-gray-500">Rata-rata Nilai Pesanan (Shopee)</h3>
                        <p class="mt-2 text-3xl font-bold text-gray-900">Rp {{ number_format($shopee_summary['average_order_value'], 0, ',', '.') }}</p>
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
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pelanggan</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse (array_slice($shopee_sales_list, 0, 3) as $sale)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600"><a href="#">{{ $sale['invoice_id'] }}</a></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">{{ $sale['customer_name'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 font-semibold">Rp {{ number_format($sale['total_amount'], 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="px-6 py-4 text-center text-gray-500">Tidak ada data transaksi Shopee.</td></tr>
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
                                    <img class="h-14 w-14 rounded-md object-cover" src="{{ $product['image_url'] }}" alt="{{ $product['name'] }}">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">{{ $product['name'] }}</p>
                                        <p class="text-sm text-gray-500">{{ $product['sold_count'] }} unit terjual</p>
                                    </div>
                                </li>
                                @empty
                                 <p class="text-sm text-gray-500">Tidak ada produk terlaris.</p>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ****************************************************** --}}
            {{-- *                 BAGIAN TOKOPEDIA                   * --}}
            {{-- ****************************************************** --}}
            <div class="p-6 bg-white rounded-lg shadow-md">
                 <h2 class="text-2xl font-bold text-gray-800 mb-6">
                    <img src="https://assets.tokopedia.net/assets-tokopedia-lite/v2/zeus/production/e5b8438b.svg" alt="Tokopedia Logo" class="inline-block h-8 mr-2">
                    Laporan Penjualan Tokopedia
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                     <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-sm font-medium text-gray-500">Total Pendapatan (Tokopedia)</h3>
                        <p class="mt-2 text-3xl font-bold text-gray-900">Rp {{ number_format($tokopedia_summary['total_revenue'], 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-sm font-medium text-gray-500">Total Pesanan (Tokopedia)</h3>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($tokopedia_summary['total_orders'], 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-sm font-medium text-gray-500">Produk Terjual (Tokopedia)</h3>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($tokopedia_summary['total_products_sold'], 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-sm font-medium text-gray-500">Rata-rata Nilai Pesanan (Tokopedia)</h3>
                        <p class="mt-2 text-3xl font-bold text-gray-900">Rp {{ number_format($tokopedia_summary['average_order_value'], 0, ',', '.') }}</p>
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
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pelanggan</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse (array_slice($tokopedia_sales_list, 0, 3) as $sale)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600"><a href="#">{{ $sale['invoice_id'] }}</a></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">{{ $sale['customer_name'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 font-semibold">Rp {{ number_format($sale['total_amount'], 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="px-6 py-4 text-center text-gray-500">Tidak ada data transaksi Tokopedia.</td></tr>
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
                                    <img class="h-14 w-14 rounded-md object-cover" src="{{ $product['image_url'] }}" alt="{{ $product['name'] }}">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">{{ $product['name'] }}</p>
                                        <p class="text-sm text-gray-500">{{ $product['sold_count'] }} unit terjual</p>
                                    </div>
                                </li>
                                @empty
                                    <p class="text-sm text-gray-500">Tidak ada produk terlaris.</p>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ====================================================================== --}}
        {{--                 BAGIAN 4: MEMANGGIL MODAL PARTIALS                     --}}
        {{-- ====================================================================== --}}

        @include('ecommerce.sales.partials.modal-shopee-sales')
        @include('ecommerce.sales.partials.modal-tokopedia-sales')

    </div>
    <script>
        jQuery.noConflict();
        (function($) {
            $(document).ready(function() {
                if (!$.fn.DataTable.isDataTable('#shopee-sales-table')) {
                    $('#shopee-sales-table').DataTable({
                        dom: "<'flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-4'lf>" +
                            "<'w-full overflow-x-auto't>" +
                            "<'flex flex-col sm:flex-row justify-between items-start sm:items-center mt-4 gap-4'ip>",
                    });
                }

                if (!$.fn.DataTable.isDataTable('#tokopedia-sales-table')) {
                    $('#tokopedia-sales-table').DataTable({
                        dom: "<'flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-4'lf>" +
                            "<'w-full overflow-x-auto't>" +
                            "<'flex flex-col sm:flex-row justify-between items-start sm:items-center mt-4 gap-4'ip>",
                    });
                }
            });
        })(jQuery);
    </script>
</x-app-layout>