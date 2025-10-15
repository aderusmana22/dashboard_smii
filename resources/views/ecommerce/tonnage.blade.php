<x-app-layout>
    @section('title', 'Pemetaan Tonase Produk')

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Notifikasi Success/Error --}}
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form action="{{ route('ecommerce.products.tonnage.store') }}" method="POST">
                    @csrf
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">Pemetaan Tonase Produk</h2>
                                <p class="text-sm text-gray-500 mt-1">
                                    Masukkan nilai berat (tonase) untuk setiap produk.
                                </p>
                            </div>
                            <div>
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                    Simpan Perubahan
                                </button>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-3/5">Nama Produk</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Platform</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/5">Tonase</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 text-sm">
                                    @forelse ($products as $product)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $product->title }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex flex-col space-y-1">
                                                    {{-- Menampilkan asal platform produk --}}
                                                    @if($product->tiktok_product_id)
                                                        <span class="inline-block bg-green-600 text-white text-xs font-semibold px-2 py-1 rounded-full">Tokopedia</span>
                                                    @endif
                                                    @if($product->shopee_product_id)
                                                        <span class="inline-block bg-orange-500 text-white text-xs font-semibold px-2 py-1 rounded-full">Shopee</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <input 
                                                    type="number" 
                                                    name="tonnages[{{ $product->id }}]"
                                                    value="{{ old('tonnages.' . $product->id, optional($product->tonnage)->tonnage ?? 0) }}"
                                                    step="0.000001" 
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                    placeholder="0.000"
                                                >
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-4">Tidak ada data produk. Silakan lakukan sinkronisasi terlebih dahulu.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>