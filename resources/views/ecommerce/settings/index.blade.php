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

            <!-- Notifikasi Global (untuk redirect dari TikTok) -->
            @if (session('success'))
                <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md" role="alert">
                    <p class="font-bold">Sukses</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md" role="alert">
                    <p class="font-bold">Gagal</p>
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <!-- Kartu Integrasi TikTok Shop -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Integrasi TikTok Shop</h2>
                    <p class="text-sm text-gray-500 mt-1">Hubungkan toko TikTok Anda untuk sinkronisasi produk, stok, dan pesanan secara otomatis.</p>
                </div>
                <div class="px-6 py-4 bg-gray-50 flex items-center justify-between">
                    @if ($tiktokShop)
                        <!-- Status: Terhubung -->
                        <div class="flex items-center space-x-3">
                            <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Terhubung dengan: <strong>{{ $tiktokShop->seller_name }}</strong></p>
                                <p class="text-xs text-gray-500">Otorisasi diberikan pada {{ $tiktokShop->created_at->format('d M Y, H:i') }}</p>
                            </div>
                        </div>
                        <form action="{{ route('tiktok.disconnect') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memutuskan koneksi dengan TikTok Shop?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                Putuskan Hubungan
                            </button>
                        </form>
                    @else
                        <!-- Status: Belum Terhubung -->
                        <p class="text-sm text-gray-700">Status: <span class="font-semibold text-yellow-600">Belum Terhubung</span></p>
                        <a href="{{ route('tiktok.auth') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-gray-800 hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-700">
                            <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M21.6 8.2l-2.5-1.2c-.5-.2-1.1.1-1.3.6-.2.5.1 1.1.6 1.3l2.5 1.2c.5.2 1.1-.1 1.3-.6.3-.5-.1-1.1-.6-1.3zM4.9 10.1l2.5-1.2c.5-.2 1.1.1 1.3.6.2.5-.1 1.1-.6 1.3L5.6 12c-.5.2-1.1-.1-1.3-.6-.2-.5.1-1.1.6-1.3zM12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm0 18c-4.4 0-8-3.6-8-8s3.6-8 8-8 8 3.6 8 8-3.6 8-8 8zm-3.1-8.9c-.5-.2-1.1.1-1.3.6s.1 1.1.6 1.3l3.1 1.5c.1 0 .2.1.3.1s.2 0 .3-.1l3.1-1.5c.5-.2.8-.8.6-1.3s-.8-.8-1.3-.6L12 13.4l-3.1-1.5z"/></svg>
                            Hubungkan dengan TikTok Shop
                        </a>
                    @endif
                </div>
            </div>

            <!-- Kartu Form Konfigurasi (Kode Anda yang sudah ada) -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form action="{{ route('ecommerce.settings.update') }}" method="POST">
                    @csrf
                    <div class="p-6 bg-white border-b border-gray-200 space-y-6">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Pengaturan Umum</h2>
                            <p class="text-sm text-gray-500 mt-1">Pengaturan yang berlaku secara global untuk fitur E-Commerce.</p>
                        </div>
                        <div class="border-t border-gray-200 pt-6">
                            <label for="stock_alert_threshold" class="block text-sm font-medium text-gray-700">
                                Batas Stok Peringatan
                            </label>
                            <div class="mt-1">
                                <input type="number" name="stock_alert_threshold" id="stock_alert_threshold"
                                    class="bg-white shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:w-1/2 md:w-1/3 rounded-md border-gray-300 @error('stock_alert_threshold') border-red-500 @enderror"
                                    value="{{ old('stock_alert_threshold', $stockAlertLimit) }}"
                                    placeholder="Contoh: 10"
                                    min="0">
                            </div>
                            <p class="mt-2 text-xs text-gray-500">
                                Peringatan "Stok Rendah" akan muncul di dashboard jika sisa stok produk kurang dari atau sama dengan nilai ini.
                            </p>
                            @error('stock_alert_threshold')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
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