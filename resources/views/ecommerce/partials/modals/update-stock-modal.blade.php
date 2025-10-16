{{-- ====================================================================== --}}
{{-- ================== MODAL UNTUK TAMBAH STOK (VERSI BARU) ================== --}}
{{-- ====================================================================== --}}
<div
    x-show="isStockModalOpen"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
    style="display: none;"
    x-cloak {{-- Atribut ini mencegah modal "berkedip" saat halaman dimuat --}}
>
    <div
        @click.away="isStockModalOpen = false"
        class="bg-white rounded-lg shadow-xl w-full max-w-md mx-auto p-6"
    >
        {{-- Judul Modal --}}
        <h3 class="text-lg font-medium text-gray-900 mb-2">
            Tambah Stok untuk <span x-text="modalProductTitle" class="font-bold"></span>
        </h3>
        <p class="text-sm text-gray-500 mb-4">Stok saat ini: <span x-text="modalCurrentStock"></span></p>

        {{-- Form Baru yang Mengarah ke Rute add.stock --}}
        <form :action="modalAddStockUrl" method="POST">
            @csrf
            <div>
                <label for="additional_stock" class="block text-sm font-medium text-gray-700">
                    Jumlah Stok yang Ingin Ditambah
                </label>
                <div class="mt-1">
                    <input
                        type="number"
                        name="additional_stock" {{-- NAMA INPUT DIUBAH --}}
                        id="additional_stock"
                        class="text-black block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="Contoh: 10"
                        min="1" {{-- Input harus lebih dari 0 --}}
                        required
                    >
                </div>
            </div>

            {{-- Tombol Aksi --}}
            <div class="mt-6 flex justify-end space-x-3">
                <button
                    type="button"
                    @click="isStockModalOpen = false"
                    class="px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700"
                >
                    Tambah Stok
                </button>
            </div>
        </form>
    </div>
</div>