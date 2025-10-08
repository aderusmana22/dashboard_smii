{{-- File: resources/views/ecommerce/partials/modals/transaksi-dibatalkan.blade.php --}}
<div x-show="activeModal === 'transaksiDibatalkan'" x-cloak style="display: none;" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center p-4">
    {{-- Overlay --}}
    <div @click="activeModal = ''" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm"></div>

    {{-- Konten Modal --}}
    <div @click.outside="activeModal = ''" class="bg-white rounded-lg shadow-xl overflow-hidden max-w-4xl w-full z-10" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95">
        
        {{-- Header --}}
        <div class="flex justify-between items-center px-6 py-4 border-b">
            <h3 class="text-lg font-bold">Transaksi Dibatalkan</h3>
            <button @click="activeModal = ''" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        {{-- Body --}}
        <div class="p-0" x-data="{ activeTab: 'shopee' }">
            {{-- Navigasi Tab --}}
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-6 px-6" aria-label="Tabs">
                    {{-- [PERBAIKAN] Membuat jumlah pesanan dinamis --}}
                    <button @click="activeTab = 'shopee'" :class="{ 'border-orange-500 text-orange-600': activeTab === 'shopee', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'shopee' }" class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition">
                        Shopee <span x-text="`(${modalData.shopee ? modalData.shopee.length : 0})`"></span>
                    </button>
                    <button @click="activeTab = 'tokopedia'" :class="{ 'border-green-600 text-green-700': activeTab === 'tokopedia', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'tokopedia' }" class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition">
                        Tokopedia <span x-text="`(${modalData.tokopedia ? modalData.tokopedia.length : 0})`"></span>
                    </button>
                </nav>
            </div>

            {{-- Konten Tab --}}
            <div class="max-h-[60vh] overflow-y-auto p-6">
                {{-- [PERBAIKAN] Menambahkan Indikator Loading --}}
                <div x-show="isLoading" class="text-center py-8">
                    <p class="text-gray-500">Memuat data...</p>
                </div>
                
                {{-- [PERBAIKAN] Menambahkan wrapper untuk konten setelah loading --}}
                <div x-show="!isLoading">
                    {{-- Panel Shopee --}}
                    <div x-show="activeTab === 'shopee'">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pembeli</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Pesanan</th>
                                    {{-- [PERBAIKAN] Mengganti Alasan menjadi Total --}}
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Aksi</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                {{-- [PERBAIKAN] Menambahkan template untuk data kosong --}}
                                <template x-if="!modalData.shopee || modalData.shopee.length === 0">
                                    <tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">Tidak ada transaksi yang dibatalkan di Shopee.</td></tr>
                                </template>
                                {{-- [PERBAIKAN] Melakukan perulangan data dinamis --}}
                                <template x-for="order in modalData.shopee" :key="order.id">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm font-medium text-gray-900" x-text="order.recipient_name"></div></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-500" x-text="order.order_id"></div></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-900 font-semibold" x-text="new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(order.total_amount)"></div></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"><a href="#" class="text-orange-600 hover:text-orange-900">Detail</a></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    {{-- Panel Tokopedia --}}
                    <div x-show="activeTab === 'tokopedia'">
                         <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pembeli</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Pesanan</th>
                                    {{-- [PERBAIKAN] Mengganti Alasan menjadi Total --}}
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Aksi</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                {{-- [PERBAIKAN] Menambahkan template untuk data kosong --}}
                                <template x-if="!modalData.tokopedia || modalData.tokopedia.length === 0">
                                    <tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">Tidak ada transaksi yang dibatalkan di Tokopedia.</td></tr>
                                </template>
                                {{-- [PERBAIKAN] Melakukan perulangan data dinamis --}}
                                <template x-for="order in modalData.tokopedia" :key="order.id">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm font-medium text-gray-900" x-text="order.recipient_name"></div></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-500" x-text="order.order_id"></div></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-900 font-semibold" x-text="new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(order.total_amount)"></div></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"><a href="#" class="text-green-600 hover:text-green-900">Detail</a></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-6 py-4 bg-gray-50 flex justify-end items-center space-x-3 border-t">
            {{-- TOMBOL BARU: Link ke Halaman Penjualan --}}
            <a href="{{ route('ecommerce.sales.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                {{-- Ikon bisa disesuaikan, ini contoh menggunakan SVG Heroicons --}}
                <svg class="w-5 h-5 mr-2 -ml-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
                </svg>
                Lihat Laporan Penjualan
            </a>

            {{-- Tombol Tutup yang sudah ada --}}
            <button @click="activeModal = ''" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm font-medium">
                Tutup
            </button>
        </div>
    </div>
    </div>
</div>