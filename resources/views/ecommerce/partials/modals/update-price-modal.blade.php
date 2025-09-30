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
        class="bg-white rounded-lg shadow-xl w-full max-w-md mx-auto p-6"
    >
        <h3 class="text-lg font-medium text-gray-900 mb-4">
            Ubah Harga untuk <span x-text="modalProductTitle" class="font-bold"></span>
        </h3>

        <form :action="modalUpdatePriceUrl" method="POST">
            @csrf
            <div>
                <label for="price" class="block text-sm font-medium text-gray-700">Harga Baru (Rp)</label>
                <div class="mt-1">
                    <input
                        type="number"
                        name="price"
                        id="price"
                        class="text-black block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="Contoh: 50000"
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