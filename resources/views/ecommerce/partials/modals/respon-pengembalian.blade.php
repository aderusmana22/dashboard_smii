{{-- File: resources/views/ecommerce/partials/modals/respon-pengembalian.blade.php --}}
<div
    x-show="openModal === 'responPengembalian'"
    x-cloak
    style="display: none;"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
>
    {{-- Latar belakang overlay HANYA menggunakan efek blur, tanpa transisi --}}
    <div @click="openModal = ''" class="fixed inset-0 backdrop-blur-sm"></div>

    {{-- [MODIFIED] Konten modal diperlebar (max-w-6xl) untuk detail pengembalian, tanpa transisi --}}
    <div
        @click.outside="openModal = ''"
        class="bg-white rounded-lg shadow-xl overflow-hidden max-w-6xl w-full z-10"
    >
        {{-- Header Modal --}}
        <div class="flex justify-between items-center px-6 py-4 border-b">
            <h3 class="text-lg font-bold">Respon Pengembalian Barang/Dana</h3>
            <button @click="openModal = ''" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        {{-- Body Modal dengan Tabs dan Tabel Data --}}
        <div class="p-0" x-data="{ activeTab: 'shopee' }">
            <!-- Navigasi Tabs -->
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-6 px-6" aria-label="Tabs">
                    <button
                        @click="activeTab = 'shopee'"
                        :class="{ 'border-orange-500 text-orange-600': activeTab === 'shopee', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'shopee' }"
                        class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm"
                    >
                        Shopee (1)
                    </button>
                    <button
                        @click="activeTab = 'tokopedia'"
                        :class="{ 'border-green-600 text-green-700': activeTab === 'tokopedia', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'tokopedia' }"
                        class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm"
                    >
                        Tokopedia (1)
                    </button>
                </nav>
            </div>

            <!-- Konten Panel Tabs -->
            <div class="max-h-[60vh] overflow-y-auto">
                {{-- Panel Shopee --}}
                <div x-show="activeTab === 'shopee'" class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pesanan & Pembeli</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alasan</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            {{-- CONTOH DATA --}}
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">ID251E2F3G4H</div>
                                    <div class="text-sm text-gray-500">Budi Susanto</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-12 w-12"><img class="h-12 w-12 rounded-md object-cover" src="https://cdn.shopify.com/s/files/1/0601/5415/1102/files/casablanca_1000x.jpg" alt="Produk"></div>
                                        <div class="ml-4"><div class="text-sm font-medium text-gray-900">Baju Kemeja Polos</div></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 max-w-xs whitespace-normal">Barang tidak sesuai dengan deskripsi, warna seharusnya navy blue tapi yang datang hitam.</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <button class="px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700">Setujui</button>
                                        <button class="px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Tolak</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Panel Tokopedia --}}
                <div x-show="activeTab === 'tokopedia'" class="p-6">
                     <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                             <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pesanan & Pembeli</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alasan</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            {{-- CONTOH DATA --}}
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">INV/2025/XXI/V/98765</div>
                                    <div class="text-sm text-gray-500">Citra Lestari</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-12 w-12"><img class="h-12 w-12 rounded-md object-cover" src="https://cdn.shopify.com/s/files/1/0601/5415/1102/files/casablanca_1000x.jpg" alt="Produk"></div>
                                        <div class="ml-4"><div class="text-sm font-medium text-gray-900">Celana Jeans Biru</div></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 max-w-xs whitespace-normal">Produk rusak saat diterima, ada sobekan di bagian lutut.</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <button class="px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700">Setujui</button>
                                        <button class="px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Tolak</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Footer Modal --}}
        <div class="px-6 py-3 bg-gray-50 text-right border-t">
            <button @click="openModal = ''" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                Tutup
            </button>
        </div>
    </div>
</div>