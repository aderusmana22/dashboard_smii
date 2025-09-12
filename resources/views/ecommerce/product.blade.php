<x-app-layout>
    @section('title')
        Daftar Produk
    @endsection

        <style>
        .dark-skin .bg-white { background-color: rgb(31 41 55 / 1); }
        .dark-skin .bg-gray-50 { background-color: rgb(55 65 81 / 1); }
        .dark-skin .bg-gray-100 { background-color: rgb(55 65 81 / 1); }
        .dark-skin .divide-gray-200> :not([hidden])~ :not([hidden]) { border-color: rgb(55 65 81 / 1); }
        .dark-skin .text-gray-900 { color: rgb(249 250 251 / 1); }
        .dark-skin .text-gray-800 { color: rgb(229 231 235 / 1); }
        .dark-skin .text-gray-700 { color: rgb(209 213 219 / 1); }
        .dark-skin .text-gray-500 { color: rgb(209 213 219 / 1); }
        .dark-skin .border-gray-300 { border-color: rgb(75 85 99 / 1); }
        .dark-skin .text-indigo-600 { color: #818cf8; }
        .dark-skin .text-indigo-600:hover { color: #a5b4fc; }
        .dark-skin .text-red-600 { color: #f87171; }
        .dark-skin .text-red-600:hover { color: #fca5a5; }
        .dark-skin .modal-cancel-button { background-color: rgb(75 85 99 / 1); color: rgb(229 231 235 / 1); }
        .dark-skin .modal-cancel-button:hover { background-color: rgb(107 114 128 / 1); }
    </style>

    <div class="py-12">
        <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-2xl font-bold mb-4">Daftar Produk</h2>

                    <!-- Wrapper untuk tabel agar bisa scroll horizontal di layar kecil -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left">
                                        <input type="checkbox" class="form-checkbox h-4 w-4 text-indigo-600 transition duration-150 ease-in-out">
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Produk
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Penjualan 30 hari terakhir
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tren Penjualan 30 hari terakhir
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Harga
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Stok
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Kualitas Informasi Produk
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 text-sm">

                                <!-- Produk 1: Kacamata Wanita -->
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" class="form-checkbox h-4 w-4 text-indigo-600 transition duration-150 ease-in-out">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-16 w-16">
                                                {{-- Gambar diubah ke URL yang diberikan --}}
                                                <img class="h-16 w-16 object-cover" src="https://cdn.shopify.com/s/files/1/0601/5415/1102/files/casablanca_1000x.jpg" alt="Kacamata">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 mr-2">Diarsipkan</span>
                                                    Kacamata Wanita
                                                </div>
                                                <div class="text-xs text-gray-500">SKU Induk: -</div>
                                                <div class="text-xs text-gray-500">ID Produk: 25926297785</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-500">-</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-500">-</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-900">Rp55.000 - Rp60.000</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-900">200</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 font-semibold">Perlu Ditingkatkan</div>
                                        <div class="text-xs text-orange-600">1 informasi tidak sesuai standar dan mempengaruhi penjualanmu</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="#" class="text-blue-600 hover:text-blue-900 block">Ubah</a>
                                        <a href="#" class="text-blue-600 hover:text-blue-900 block">Iklankan</a>
                                        <a href="#" class="text-blue-600 hover:text-blue-900 block">Lainnya</a>
                                    </td>
                                </tr>

                                
                                <!-- Produk 1: Kacamata Wanita -->
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" class="form-checkbox h-4 w-4 text-indigo-600 transition duration-150 ease-in-out">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-16 w-16">
                                                {{-- Gambar diubah ke URL yang diberikan --}}
                                                <img class="h-16 w-16 object-cover" src="https://cdn.shopify.com/s/files/1/0601/5415/1102/files/casablanca_1000x.jpg" alt="Kacamata">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 mr-2">Diarsipkan</span>
                                                    Kacamata Wanita
                                                </div>
                                                <div class="text-xs text-gray-500">SKU Induk: -</div>
                                                <div class="text-xs text-gray-500">ID Produk: 25926297785</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-500">-</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-500">-</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-900">Rp55.000 - Rp60.000</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-900">200</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 font-semibold">Perlu Ditingkatkan</div>
                                        <div class="text-xs text-orange-600">1 informasi tidak sesuai standar dan mempengaruhi penjualanmu</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="#" class="text-blue-600 hover:text-blue-900 block">Ubah</a>
                                        <a href="#" class="text-blue-600 hover:text-blue-900 block">Iklankan</a>
                                        <a href="#" class="text-blue-600 hover:text-blue-900 block">Lainnya</a>
                                    </td>
                                </tr>

                                <!-- Produk 1: Kacamata Wanita -->
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" class="form-checkbox h-4 w-4 text-indigo-600 transition duration-150 ease-in-out">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-16 w-16">
                                                {{-- Gambar diubah ke URL yang diberikan --}}
                                                <img class="h-16 w-16 object-cover" src="https://cdn.shopify.com/s/files/1/0601/5415/1102/files/casablanca_1000x.jpg" alt="Kacamata">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 mr-2">Diarsipkan</span>
                                                    Kacamata Wanita
                                                </div>
                                                <div class="text-xs text-gray-500">SKU Induk: -</div>
                                                <div class="text-xs text-gray-500">ID Produk: 25926297785</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-500">-</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-500">-</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-900">Rp55.000 - Rp60.000</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-900">200</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 font-semibold">Perlu Ditingkatkan</div>
                                        <div class="text-xs text-orange-600">1 informasi tidak sesuai standar dan mempengaruhi penjualanmu</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="#" class="text-blue-600 hover:text-blue-900 block">Ubah</a>
                                        <a href="#" class="text-blue-600 hover:text-blue-900 block">Iklankan</a>
                                        <a href="#" class="text-blue-600 hover:text-blue-900 block">Lainnya</a>
                                    </td>
                                </tr>
                                
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>