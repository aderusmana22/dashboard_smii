{{-- File: resources/views/ecommerce/partials/modals/produk-habis.blade.php --}}
<div
    x-show="openModal === 'produkHabis'"
    x-cloak
    style="display: none;"
        x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
>
    {{-- Latar belakang overlay HANYA menggunakan efek blur, tanpa transisi --}}
    <div @click="openModal = ''" class="fixed inset-0 backdrop-blur-sm"></div>

    {{-- [MODIFIED] Konten modal diperlebar (max-w-4xl), tanpa transisi --}}
    <div
        @click.outside="openModal = ''"
        class="bg-white rounded-lg shadow-xl overflow-hidden max-w-4xl w-full z-10"
    >
        {{-- Header Modal --}}
        <div class="flex justify-between items-center px-6 py-4 border-b">
            <h3 class="text-lg font-bold">Produk Habis Stok</h3>
            <button @click="openModal = ''" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        {{-- [MODIFIED] Body Modal dengan Tabel Daftar Produk --}}
        <div class="p-6 max-h-[60vh] overflow-y-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Habis</th>
                        <th scope="col" class="relative px-6 py-3"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    {{-- CONTOH DATA --}}
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-12 w-12"><img class="h-12 w-12 rounded-md object-cover" src="https://cdn.shopify.com/s/files/1/0601/5415/1102/files/casablanca_1000x.jpg" alt="Produk"></div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">Kaos Putih Polos</div>
                                    <div class="text-sm text-gray-500">Ukuran: L</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 font-mono">KPP-L-001</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                             <div class="text-sm text-gray-900">08 Sep 2025</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="#" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">Tambah Stok</a>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-12 w-12"><img class="h-12 w-12 rounded-md object-cover" src="https://cdn.shopify.com/s/files/1/0601/5415/1102/files/casablanca_1000x.jpg" alt="Produk"></div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">Celana Jeans Biru</div>
                                    <div class="text-sm text-gray-500">Ukuran: 32</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 font-mono">CJB-32-005</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                             <div class="text-sm text-gray-900">06 Sep 2025</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="#" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">Tambah Stok</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Footer Modal --}}
        <div class="px-6 py-3 bg-gray-50 text-right border-t">
            <button @click="openModal = ''" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                Tutup
            </button>
        </div>
    </div>
</div>