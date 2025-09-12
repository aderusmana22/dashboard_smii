{{-- File: resources/views/ecommerce/partials/modals/belum-bayar.blade.php --}}
<div
    x-show="openModal === 'belumBayar'"
    x-cloak
    style="display: none;"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
>
    {{-- [MODIFIED] Latar belakang overlay HANYA menggunakan efek blur, tanpa warna background --}}
    <div @click="openModal = ''" class="fixed inset-0 backdrop-blur-sm"></div>

    {{-- Konten modal diperlebar untuk menampung tabel --}}
    <div
        @click.outside="openModal = ''"
        class="bg-white rounded-lg shadow-xl overflow-hidden max-w-4xl w-full z-10"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
    >
        {{-- Header Modal --}}
        <div class="flex justify-between items-center px-6 py-4 border-b">
            <h3 class="text-lg font-bold">Pesanan Belum Dibayar</h3>
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
                        class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition"
                    >
                        Shopee (3)
                    </button>
                    <button
                        @click="activeTab = 'tokopedia'"
                        :class="{ 'border-green-600 text-green-700': activeTab === 'tokopedia', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'tokopedia' }"
                        class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition"
                    >
                        Tokopedia (2)
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
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pembeli</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Pesanan</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Aksi</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            {{-- CONTOH DATA - Ganti dengan @foreach loop dari data Anda --}}
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap"><div class="flex items-center"><div class="flex-shrink-0 h-10 w-10"><img class="h-10 w-10 rounded-full" src="https://i.pravatar.cc/40?u=user1" alt=""></div><div class="ml-4"><div class="text-sm font-medium text-gray-900">Budi Susanto</div><div class="text-sm text-gray-500">budi.s@example.com</div></div></div></td>
                                <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-900">ID259A8B7C6D</div></td>
                                <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-900 font-semibold">Rp 155.000</div></td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"><a href="#" class="text-orange-600 hover:text-orange-900">Hubungi</a></td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap"><div class="flex items-center"><div class="flex-shrink-0 h-10 w-10"><img class="h-10 w-10 rounded-full" src="https://i.pravatar.cc/40?u=user2" alt=""></div><div class="ml-4"><div class="text-sm font-medium text-gray-900">Citra Lestari</div><div class="text-sm text-gray-500">citra.l@example.com</div></div></div></td>
                                <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-900">ID251E2F3G4H</div></td>
                                <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-900 font-semibold">Rp 80.000</div></td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"><a href="#" class="text-orange-600 hover:text-orange-900">Hubungi</a></td>
                            </tr>
                             <tr>
                                <td class="px-6 py-4 whitespace-nowrap"><div class="flex items-center"><div class="flex-shrink-0 h-10 w-10"><img class="h-10 w-10 rounded-full" src="https://i.pravatar.cc/40?u=user3" alt=""></div><div class="ml-4"><div class="text-sm font-medium text-gray-900">Dewi Ayu</div><div class="text-sm text-gray-500">dewi.a@example.com</div></div></div></td>
                                <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-900">ID255I6J7K8L</div></td>
                                <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-900 font-semibold">Rp 210.000</div></td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"><a href="#" class="text-orange-600 hover:text-orange-900">Hubungi</a></td>
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
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pembeli</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Pesanan</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Aksi</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            {{-- CONTOH DATA - Ganti dengan @foreach loop dari data Anda --}}
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap"><div class="flex items-center"><div class="flex-shrink-0 h-10 w-10"><img class="h-10 w-10 rounded-full" src="https://i.pravatar.cc/40?u=user4" alt=""></div><div class="ml-4"><div class="text-sm font-medium text-gray-900">Eko Prasetyo</div><div class="text-sm text-gray-500">eko.p@example.com</div></div></div></td>
                                <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-900">INV/2025/XXI/V/12345</div></td>
                                <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-900 font-semibold">Rp 350.000</div></td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"><a href="#" class="text-green-600 hover:text-green-900">Hubungi</a></td>
                            </tr>
                             <tr>
                                <td class="px-6 py-4 whitespace-nowrap"><div class="flex items-center"><div class="flex-shrink-0 h-10 w-10"><img class="h-10 w-10 rounded-full" src="https://i.pravatar.cc/40?u=user5" alt=""></div><div class="ml-4"><div class="text-sm font-medium text-gray-900">Fitri Nur</div><div class="text-sm text-gray-500">fitri.n@example.com</div></div></div></td>
                                <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-900">INV/2025/XXI/V/67890</div></td>
                                <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-900 font-semibold">Rp 55.000</div></td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"><a href="#" class="text-green-600 hover:text-green-900">Hubungi</a></td>
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