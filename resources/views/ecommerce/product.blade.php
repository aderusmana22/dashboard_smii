<x-app-layout>
    @section('title', 'Daftar Produk')

    <div class="py-12">
        <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-2xl font-bold mb-4 text-gray-900">Daftar Produk TikTok</h2>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 text-sm">
                                @forelse ($products ?? [] as $product)
                                    @php
                                        // Kalkulasi harga dari data detail
                                        $prices = data_get($product, 'skus.*.price.sale_price', []);
                                        $min_price = !empty($prices) ? min($prices) : 0;
                                        $max_price = !empty($prices) ? max($prices) : 0;
                                        $price_range = ($min_price == $max_price) 
                                            ? 'Rp' . number_format($min_price) 
                                            : 'Rp' . number_format($min_price) . ' - Rp' . number_format($max_price);

                                        // Kalkulasi stok dari data detail
                                        $inventories = data_get($product, 'skus.*.inventory', []);
                                        $total_stock = 0;
                                        if (is_array($inventories)) {
                                            foreach ($inventories as $inventory_group) {
                                                if(is_array($inventory_group)) {
                                                    $total_stock += array_sum(array_column($inventory_group, 'quantity'));
                                                }
                                            }
                                        }

                                        // Logika pemetaan status (versi light-mode)
                                        $status_map = [
                                            'SELLER_DEACTIVATED' => ['text' => 'Nonaktif', 'class' => 'bg-yellow-100 text-yellow-800'],
                                            'ACTIVE'  => ['text' => 'Aktif', 'class' => 'bg-green-100 text-green-800'],
                                            'DELETED' => ['text' => 'Dihapus', 'class' => 'bg-red-100 text-red-800'],
                                            'FROZEN'  => ['text' => 'Dibekukan', 'class' => 'bg-gray-200 text-gray-800'],
                                            'DRAFT'   => ['text' => 'Draf', 'class' => 'bg-blue-100 text-blue-800'],
                                        ];
                                        $status_key = $product['product_status'] ?? 'UNKNOWN';
                                        $product_status = $status_map[$status_key] ?? ['text' => 'Tidak Diketahui', 'class' => 'bg-gray-100 text-gray-800'];
                                    @endphp
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-16 w-16">
                                                    <img class="h-16 w-16 object-cover rounded" src="{{ data_get($product, 'main_images.0.urls.0', 'https://via.placeholder.com/150') }}" alt="{{ $product['title'] ?? 'Gambar Produk' }}">
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ $product['title'] ?? 'Tanpa Judul' }}</div>
                                                    <div class="text-xs text-gray-500">ID: {{ $product['id'] ?? '-' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $price_range }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $total_stock }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $product_status['class'] }}">{{ $product_status['text'] }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{-- route('ecommerce.products.show', ['productId' => $product['id']]) --}}" class="text-indigo-600 hover:text-indigo-900">Detail</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                            Tidak ada produk yang ditemukan atau gagal memuat data dari TikTok Shop.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>