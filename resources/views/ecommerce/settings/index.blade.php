<x-app-layout>
    @section('title')
        Konfigurasi E-Commerce
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
            <!-- Header Halaman -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Konfigurasi</h1>
                <p class="mt-1 text-sm text-gray-500">Atur berbagai parameter untuk toko online Anda.</p>
            </div>

            <!-- Kartu Form Konfigurasi -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form action="{{ route('ecommerce.settings.update') }}" method="POST">
                    @csrf {{-- Token keamanan wajib untuk form POST di Laravel --}}

                    <div class="p-6 bg-white border-b border-gray-200 space-y-6">
                        <!-- Notifikasi Sukses -->
                        @if (session('success'))
                            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md" role="alert">
                                <p class="font-bold">Sukses</p>
                                <p>{{ session('success') }}</p>
                            </div>
                        @endif

                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Pengaturan Umum</h2>
                            <p class="text-sm text-gray-500 mt-1">Pengaturan yang berlaku secara global untuk fitur E-Commerce.</p>
                        </div>

                        <!-- Input: Batas Stok Peringatan -->
                        <div class="border-t border-gray-200 pt-6">
                            <label for="stock_alert_threshold" class="block text-sm font-medium text-gray-700">
                                Batas Stok Peringatan
                            </label>
                            <div class="mt-1">
                                <input type="number" name="stock_alert_threshold" id="stock_alert_threshold"
                                    class="bg-white shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:w-1/2 md:w-1/3 rounded-md border-gray-300 @error('stock_alert_threshold') border-red-500 @enderror"
                                    value="{{ old('stock_alert_threshold', $stockAlertLimit) }}"
                                    placeholder="Contoh: 10"
                                    min="0"
                                >
                            </div>
                            <p class="mt-2 text-xs text-gray-500">
                                Peringatan "Stok Rendah" akan muncul di dashboard jika sisa stok produk kurang dari atau sama dengan nilai ini.
                            </p>
                            
                            {{-- Menampilkan pesan error validasi di bawah input --}}
                            @error('stock_alert_threshold')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Anda bisa menambahkan field konfigurasi lain di sini -->
                        {{-- <div class="border-t border-gray-200 pt-6">
                            <label for="another_setting" class="block text-sm font-medium text-gray-700">Pengaturan Lain</label>
                            ...
                        </div> --}}

                    </div>

                    <!-- Footer Form dengan Tombol Simpan -->
                    <div class="px-6 py-4 bg-gray-50 text-right">
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Simpan Perubahan
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</x-app-layout>