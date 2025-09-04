<x-app-layout>
    @section('title')
        Data Penjualan
    @endsection

    <div class="py-12">
        <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
            <!-- Header Halaman -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Laporan Penjualan</h1>
                    <p class="mt-1 text-sm text-gray-500">Analisis performa penjualan produk Anda di semua platform.</p>
                </div>
                <!-- Tombol Aksi -->
                <div class="mt-4 md:mt-0 flex space-x-2">
                    <button class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm font-medium">
                        <svg class="w-5 h-5 inline-block mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v10a1 1 0 01-1 1H4a1 1 0 01-1-1V10zM15 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4zM15 17a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1v-2z" />
                        </svg>
                        Filter
                    </button>
                    <button class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium">
                        <svg class="w-5 h-5 inline-block mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export Data
                    </button>
                </div>
            </div>

            <!-- Bagian Ringkasan Penjualan -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Card Total Pendapatan -->
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500">Total Pendapatan</h3>
                    <p class="mt-2 text-3xl font-bold text-gray-900">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</p>
                </div>
                <!-- Card Total Pesanan -->
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500">Total Pesanan</h3>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($summary['total_orders'], 0, ',', '.') }}</p>
                </div>
                <!-- Card Produk Terjual -->
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500">Produk Terjual</h3>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($summary['total_products_sold'], 0, ',', '.') }}</p>
                </div>
                <!-- Card Rata-rata Nilai Pesanan -->
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500">Rata-rata Nilai Pesanan</h3>
                    <p class="mt-2 text-3xl font-bold text-gray-900">Rp {{ number_format($summary['average_order_value'], 0, ',', '.') }}</p>
                </div>
            </div>

            <!-- Bagian Tabel dan Produk Terlaris -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Kiri: Tabel Transaksi Terakhir -->
                <div class="lg:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h2 class="text-xl font-bold mb-4">Transaksi Terakhir</h2>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pelanggan</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($sales_list as $sale)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600">
                                                <a href="#">{{ $sale['invoice_id'] }}</a>
                                                <span class="block text-xs text-gray-500">{{ $sale['platform'] }}</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">{{ $sale['customer_name'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($sale['date'])->isoFormat('D MMM YYYY, HH:mm') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 font-semibold">Rp {{ number_format($sale['total_amount'], 0, ',', '.') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                @if($sale['status'] == 'Selesai')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Dibatalkan</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">Tidak ada data transaksi.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Kanan: Produk Terlaris -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h2 class="text-xl font-bold mb-4">Produk Terlaris</h2>
                        <ul class="space-y-4">
                            @foreach($top_products as $product)
                            <li class="flex items-center space-x-4">
                                <img class="h-14 w-14 rounded-md object-cover" src="{{ $product['image_url'] }}" alt="{{ $product['name'] }}">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ $product['name'] }}</p>
                                    <p class="text-sm text-gray-500">{{ $product['sold_count'] }} unit terjual</p>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>