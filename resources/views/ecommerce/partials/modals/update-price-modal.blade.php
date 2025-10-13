<!-- Modal untuk Update Harga -->
<div
    x-show="isPriceModalOpen"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
    style="display: none;"
>
    <div
        @click.away="isPriceModalOpen = false"
        x-data="{ activePlatform: '' }"
        class="bg-white rounded-lg shadow-xl w-full max-w-md mx-auto p-6"
    >
        <h3 class="text-lg font-medium text-gray-900 mb-2">
            Ubah Harga untuk <span x-text="modalProductTitle" class="font-bold"></span>
        </h3>

        <!-- Label untuk menampilkan harga saat ini -->
        <div class="mb-4 text-sm text-gray-600">
            <p>Harga Tokopedia: <span x-text="`Rp ${modalTokopediaPrice}`" class="font-semibold"></span></p>
            <p>Harga Shopee: <span x-text="`Rp ${modalShopeePrice}`" class="font-semibold"></span></p>
        </div>

        <form :action="modalUpdatePriceUrl" method="POST">
            @csrf

            <!-- Tombol Pilihan Platform (Berfungsi seperti Radio Button) -->
            <div class="mb-4 flex space-x-3">
                <span
                    @click="activePlatform = (activePlatform === 'tokopedia') ? '' : 'tokopedia'"
                    :class="{ 'bg-indigo-600 text-white border-indigo-600': activePlatform === 'tokopedia', 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50': activePlatform !== 'tokopedia' }"
                    class="px-4 py-2 rounded-md text-sm font-medium cursor-pointer border transition-colors"
                >
                    Tokopedia
                </span>
                <span
                    @click="activePlatform = (activePlatform === 'shopee') ? '' : 'shopee'"
                    :class="{ 'bg-indigo-600 text-white border-indigo-600': activePlatform === 'shopee', 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50': activePlatform !== 'shopee' }"
                    class="px-4 py-2 rounded-md text-sm font-medium cursor-pointer border transition-colors"
                >
                    Shopee
                </span>
            </div>

            <!-- SATU INPUT YANG BERUBAH SECARA DINAMIS -->
            <div>
                <label
                    :for="activePlatform || 'price'"
                    x-text="
                        activePlatform === 'tokopedia' ? 'Harga Baru Tokopedia (Rp)' :
                        (activePlatform === 'shopee' ? 'Harga Baru Shopee (Rp)' : 'Harga Baru (Rp)')
                    "
                    class="block text-sm font-medium text-gray-700"
                ></label>
                <div class="mt-1">
                    <input
                        type="number"
                        :name="
                            activePlatform === 'tokopedia' ? 'tokopedia_price' :
                            (activePlatform === 'shopee' ? 'shopee_price' : 'price')
                        "
                        :id="activePlatform || 'price'"
                        :placeholder="
                            activePlatform === 'tokopedia' ? 'Contoh: 52000' :
                            (activePlatform === 'shopee' ? 'Contoh: 51000' : 'Contoh: 50000')
                        "
                        class="text-black block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        step="1"
                        min="0"
                        required
                    >
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button
                    type="button"
                    @click="isPriceModalOpen = false"
                    class="px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700"
                >
                    Simpan Harga
                </button>
            </div>
        </form>
    </div>
</div>