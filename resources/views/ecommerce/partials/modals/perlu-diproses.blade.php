{{-- File: resources/views/ecommerce/partials/modals/perlu-diproses.blade.php --}}
<div
    x-show="openModal === 'perluDiproses'"
    x-cloak
    style="display: none;"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
>
    {{-- Latar belakang overlay HANYA menggunakan efek blur --}}
    <div @click="openModal = ''" class="fixed inset-0 backdrop-blur-sm"></div>

    {{-- [MODIFIED] Konten modal diperlebar (max-w-5xl) untuk informasi pengiriman --}}
    <div
        @click.outside="openModal = ''"
        class="bg-white rounded-lg shadow-xl overflow-hidden max-w-5xl w-full z-10"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
    >
        {{-- Header Modal --}}
        <div class="flex justify-between items-center px-6 py-4 border-b">
            <h3 class="text-lg font-bold">Pengiriman Perlu Diproses</h3>
            <button @click="openModal = ''" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        {{-- [MODIFIED] Body Modal dengan Tabs dan Tabel Data Pengiriman --}}
        <div class="p-0" x-data="{ activeTab: 'shopee' }">
            <!-- Navigasi Tabs -->
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-6 px-6" aria-label="Tabs">
                    <button
                        @click="activeTab = 'shopee'"
                        :class="{ 'border-orange-500 text-orange-600': activeTab === 'shopee', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'shopee' }"
                        class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition"
                    >
                        Shopee (2)
                    </button>
                    <button
                        @click="activeTab = 'tokopedia'"
                        :class="{ 'border-green-600 text-green-700': activeTab === 'tokopedia', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'tokopedia' }"
                        class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition"
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
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pesanan</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pembeli & Alamat</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kurir</th>
                                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Aksi</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            {{-- CONTOH DATA - Ganti dengan @foreach loop dari data Anda --}}
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">ID251E2F3G4H</div>
                                    <div class="text-sm text-gray-500">08 Sep 2025, 14:30</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">Gita Sari</div>
                                    <div class="text-sm text-gray-500 max-w-xs truncate">Jl. Merdeka No. 123, Kel. Cempaka, Kec. Bunga, Kota Bandung...</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        J&T Express
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="#" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-orange-600 hover:bg-orange-700 focus:outline-none">Proses Pesanan</a>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">ID259A8B7C6D</div>
                                    <div class="text-sm text-gray-500">08 Sep 2025, 11:15</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">Rian Hidayat</div>
                                    <div class="text-sm text-gray-500 max-w-xs truncate">Perumahan Graha Indah Blok C7/21, Surabaya, Jawa Timur...</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Anteraja
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="#" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-orange-600 hover:bg-orange-700 focus:outline-none">Proses Pesanan</a>
                                </td>
                            </tr>
                            {{-- Akhir Contoh Data --}}
                        </tbody>
                    </table>
                </div>

                {{-- Panel Tokopedia --}}
                <div x-show="activeTab === 'tokopedia'" class="p-6">
                     <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pesanan</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pembeli & Alamat</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kurir</th>
                                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Aksi</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            {{-- CONTOH DATA - Ganti dengan @foreach loop dari data Anda --}}
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">INV/2025/XXI/V/67890</div>
                                    <div class="text-sm text-gray-500">07 Sep 2025, 20:05</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">Linda Wati</div>
                                    <div class="text-sm text-gray-500 max-w-xs truncate">Jl. Gatot Subroto Kav. 55, Apartemen The Peak, Tower A Lt. 22...</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                        SiCepat
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="#" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none">Proses Pesanan</a>
                                </td>
                            </tr>
                            {{-- @empty
                             <tr>
                                 <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                     Tidak ada pesanan dari Tokopedia yang perlu diproses.
                                 </td>
                             </tr>
                             @endforelse --}}
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