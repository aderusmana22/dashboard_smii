<div 
    x-show="isShopeeModalOpen" 
    style="display: none;" 
    id="shopee-modal"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" 
    x-cloak
>
    <div 
        @click.away="isShopeeModalOpen = false" 
        class="bg-white rounded-lg shadow-xl w-full max-w-6xl mx-4"
    >
        <div class="flex justify-between items-center p-4 border-b border-gray-200">
            <h3 class="text-xl font-bold text-gray-900">Daftar Lengkap Transaksi Shopee</h3>
            <button @click="isShopeeModalOpen = false" class="text-gray-500 hover:text-gray-800 text-3xl leading-none">&times;</button>
        </div>

        <div class="p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center bg-gray-50 gap-4">
            <div>
                <p class="text-xs text-gray-500">
                    Data terakhir diperbarui: 
                    <span class="font-medium text-gray-700">
                        {{ $shopee_orders_last_sync ? \Carbon\Carbon::parse($shopee_orders_last_sync)->isoFormat('D MMMM YYYY, HH:mm:ss') : 'Belum pernah disinkronisasi' }}
                    </span>
                </p>
            </div>
            <form action="{{ route('ecommerce.shopee.orders.sync') }}" method="POST" class="sync-form-shopee">
                @csrf
                <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700 text-sm font-medium flex items-center">
                    <svg class="w-4 h-4 mr-2 animate-spin hidden sync-spinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span class="sync-text">Perbarui Data</span>
                </button>
            </form>
        </div>

        <div class="p-4 border-b">
            <div class="relative">
                <input 
                    type="text" 
                    id="shopee-search-input"
                    placeholder="Cari Invoice, Pelanggan, atau Status..." 
                    class="w-full pl-10 pr-4 py-2 border rounded-lg"
                >
                <div class="absolute top-0 left-0 pl-3 pt-2">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>
        </div>

        <div id="shopee-table-container" class="p-6">
            {{-- Konten tabel akan dimuat di sini oleh AJAX --}}
            <div class="text-center py-10">
                <p class="text-gray-500">Memuat data...</p>
            </div>
        </div>
    </div>
</div>