<x-app-layout>
    @section('title', 'Daftar Produk')

    {{-- Alpine.js --}}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    {{-- State Alpine.js --}}
    <div class="py-12" 
         x-data="{ 
            isModalOpen: false, 
            modalProductTitle: '', 
            modalCurrentStock: 0,
            modalUpdateUrl: '' 
         }">

        <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">

            {{-- Flash message --}}
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
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

            {{-- Card --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    {{-- Header --}}
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Daftar Produk TikTok</h2>
                            <p class="text-sm text-gray-500 mt-1">
                                Data terakhir diperbarui: 
                                <span class="font-medium text-gray-700">
                                    {{ $lastSync ? \Carbon\Carbon::parse($lastSync)->isoFormat('D MMMM YYYY, HH:mm:ss') : 'Belum pernah disinkronisasi' }}
                                </span>
                            </p>
                        </div>
                        <form action="{{ route('ecommerce.products.sync') }}" method="POST" class="mt-4 sm:mt-0" id="sync-form">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2 -ml-1 animate-spin hidden" id="sync-spinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span id="sync-text">Perbarui Data</span>
                            </button>
                        </form>
                    </div>

                    {{-- Table --}}
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
                                @forelse ($products as $product)
                                    @php
                                        $status_map = [
                                            'SELLER_DEACTIVATED' => ['text' => 'Nonaktif', 'class' => 'bg-yellow-100 text-yellow-800'],
                                            'ACTIVATE'  => ['text' => 'Aktif', 'class' => 'bg-green-100 text-green-800'],
                                            'DELETED' => ['text' => 'Dihapus', 'class' => 'bg-red-100 text-red-800'],
                                            'FROZEN'  => ['text' => 'Dibekukan', 'class' => 'bg-gray-200 text-gray-800'],
                                            'DRAFT'   => ['text' => 'Draf', 'class' => 'bg-blue-100 text-blue-800'],
                                        ];
                                        $product_status = $status_map[strtoupper($product->status)] ?? ['text' => 'Tidak Diketahui', 'class' => 'bg-gray-100 text-gray-800'];
                                    @endphp
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-16 w-16">
                                                    <img class="h-16 w-16 object-cover rounded" src="{{ $product->main_image_url ?? 'https://via.placeholder.com/150' }}" alt="{{ $product->title }}">
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ $product->title }}</div>
                                                    <div class="text-xs text-gray-500">ID: {{ $product->tiktok_product_id }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $product->price_range }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $product->total_stock }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $product_status['class'] }}">{{ $product_status['text'] }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-4">
                                            <a href="#" class="text-indigo-600 hover:text-indigo-900">Detail</a>
                                            <button 
                                                @click="
                                                    isModalOpen = true;
                                                    modalProductTitle = @js($product->title);
                                                    modalCurrentStock = {{ $product->total_stock }};
                                                    modalUpdateUrl = '{{ route('ecommerce.products.stock.update', $product) }}';
                                                "
                                                class="text-green-600 hover:text-green-900">
                                                Ubah Stok
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                            Tidak ada produk di database. Coba klik tombol "Perbarui Data".
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal dari partial (pastikan path sesuai) --}}
        @include('ecommerce.partials.modals.update-stock-modal')

    </div>

    {{-- JS Spinner untuk sync --}}
    <script>
        document.getElementById('sync-form').addEventListener('submit', function() {
            document.getElementById('sync-spinner').classList.remove('hidden');
            document.getElementById('sync-text').textContent = 'Menyinkronkan...';
            this.querySelector('button[type="submit"]').disabled = true;
        });
    </script>
</x-app-layout>
