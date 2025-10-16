<x-app-layout>
    @section('title', 'Daftar Produk Master')

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <div class="py-12" x-data="{
        isStockModalOpen: false,
        isPriceModalOpen: false,
        modalProductTitle: '',
        modalCurrentStock: 0,
        modalAddStockUrl: '',
        modalUpdatePriceUrl: '',
        modalTokopediaPrice: '',
        modalShopeePrice: ''
    }">
        <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">

            {{-- Blok Notifikasi Success/Error --}}
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                    role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Daftar Produk Master</h2>
                            <p class="text-sm text-gray-500 mt-1">
                                Tokopedia Sync: <span
                                    class="font-medium">{{ $lastSyncTiktok ? \Carbon\Carbon::parse($lastSyncTiktok)->diffForHumans() : 'Belum pernah' }}</span>
                            </p>
                            <p class="text-sm text-gray-500 mt-1">
                                Shopee Sync: <span
                                    class="font-medium">{{ $lastSyncShopee ? \Carbon\Carbon::parse($lastSyncShopee)->diffForHumans() : 'Belum pernah' }}</span>
                            </p>
                        </div>
                        <div class="flex items-center space-x-2 mt-4 sm:mt-0">
                            {{-- Tombol Kondisional untuk Show All / Show Paginated --}}
                            @if (request('show') === 'all')
                                <a href="{{ route('ecommerce.products.index') }}"
                                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                    Show Paginated
                                </a>
                            @else
                                <a href="{{ route('ecommerce.products.index', ['show' => 'all']) }}"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                    Show All
                                </a>
                            @endif

                            {{-- Tombol Sinkronisasi --}}
                            <form action="{{ route('ecommerce.products.sync.tiktok') }}" method="POST"
                                class="sync-form">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                    <svg class="w-4 h-4 mr-2 -ml-1 animate-spin hidden sync-spinner"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    <span class="sync-text">Sync Tokopedia</span>
                                </button>
                            </form>
                            <form action="{{ route('ecommerce.products.sync.shopee') }}" method="POST"
                                class="sync-form">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-500">
                                    <svg class="w-4 h-4 mr-2 -ml-1 animate-spin hidden sync-spinner"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    <span class="sync-text">Sync Shopee</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Produk</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Platform</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Harga</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Stok Total</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status Platform</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Aksi</th>
                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-gray-200 text-sm">
                                @forelse ($products as $product)
                                                                    <tr>
                                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                                            <div class="flex items-center">
                                                                                <div class="flex-shrink-0 h-16 w-16">
                                                                                    <img class="h-16 w-16 object-cover rounded"
                                                                                        src="{{ $product->main_image_url ?? 'https://via.placeholder.com/150' }}"
                                                                                        alt="{{ $product->title }}">
                                                                                </div>
                                                                                <div class="ml-4">
                                                                                    <div class="text-sm font-medium text-gray-900">{{ $product->title }}
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                                            <div class="flex flex-col space-y-1">
                                                                                @if($product->tiktok_product)
                                                                                    <span
                                                                                        class="inline-block bg-green-600 text-white text-xs font-semibold px-2 py-1 rounded">Tokopedia</span>
                                                                                @endif
                                                                                @if($product->shopee_product)
                                                                                    <span
                                                                                        class="inline-block bg-orange-500 text-white text-xs font-semibold px-2 py-1 rounded">Shopee</span>
                                                                                @endif
                                                                            </div>
                                                                        </td>
                                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                                            <div class="flex flex-col space-y-1 text-xs">
                                                                                @if($product->tiktok_product)
                                                                                    <span>Tokopedia: <span
                                                                                            class="font-semibold">{{ $product->tiktok_product->display_price }}</span></span>
                                                                                @endif
                                                                                @if($product->shopee_product)
                                                                                    <span>Shopee: <span
                                                                                            class="font-semibold">{{ $product->shopee_product->display_price }}</span></span>
                                                                                @endif
                                                                            </div>
                                                                        </td>
                                                                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 font-bold text-lg">
                                                                            {{ $product->total_stock }}</td>
                                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                                            <div class="flex flex-col space-y-1 text-xs">
                                                                                @if($product->tiktok_product)
                                                                                    <span>Tokopedia: <span
                                                                                            class="font-semibold">{{ $product->tiktok_product->status }}</span></span>
                                                                                @endif
                                                                                @if($product->shopee_product)
                                                                                    <span>Shopee: <span
                                                                                            class="font-semibold">{{ $product->shopee_product->item_status }}</span></span>
                                                                                @endif
                                                                            </div>
                                                                        </td>
                                                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                                            <div class="flex space-x-4">
                                                                                <button @click="
                                        isStockModalOpen = true;
                                        modalProductTitle = '{{ addslashes($product->title) }}';
                                        modalCurrentStock = {{ $product->total_stock ?? 0 }};
                                        modalAddStockUrl = '{{ route('ecommerce.products.add.stock', $product->id) }}';
                                    " class="text-indigo-600 hover:text-indigo-900">
                                                                                    Tambah Stok
                                                                                </button>
                                                                                <button @click="
                                                                                            isPriceModalOpen = true;
                                                                                            modalProductTitle = @js($product->title);
                                                                                            modalUpdatePriceUrl = '{{ route('ecommerce.products.price.update', $product) }}';
                                                                                            modalTokopediaPrice = @js($product->tiktok_product?->display_price ?? 'Tidak terhubung');
                                                                                            modalShopeePrice = @js($product->shopee_product?->display_price ?? 'Tidak terhubung');
                                                                                        " class="text-green-600 hover:text-green-900">
                                                                                    Ubah Harga
                                                                                </button>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">Tidak ada data produk. Silakan lakukan
                                            sinkronisasi.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Blok Paginasi Kondisional --}}
                    <div class="mt-6">
                        {{-- Hanya tampilkan link paginasi jika $products adalah instance dari Paginator --}}
                        @if ($products instanceof \Illuminate\Pagination\LengthAwarePaginator)
                            {{ $products->links() }}
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Include Modals --}}
        @include('ecommerce.partials.modals.update-stock-modal')
        @include('ecommerce.partials.modals.update-price-modal')

    </div>

    <script>
        document.querySelectorAll('.sync-form').forEach(form => {
            form.addEventListener('submit', function () {
                this.querySelector('.sync-spinner').classList.remove('hidden');
                this.querySelector('.sync-text').textContent = 'Loading...';
                this.querySelector('button[type="submit"]').disabled = true;
            });
        });
    </script>
</x-app-layout>