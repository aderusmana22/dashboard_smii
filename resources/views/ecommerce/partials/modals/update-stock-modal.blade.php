<div x-show="isModalOpen" 
     x-transition
     class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">

    <div @click.away="isModalOpen = false" 
         class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">

        <div class="flex justify-between items-center p-4 border-b">
            <h3 class="text-lg font-bold text-gray-900">Perbarui Stok Produk</h3>
            <button @click="isModalOpen = false" class="text-gray-500 hover:text-gray-800 text-3xl leading-none">&times;</button>
        </div>

        <form :action="modalUpdateUrl" method="POST">
            @csrf
            <div class="p-6">
                <p class="text-sm text-gray-600 mb-1">Produk:</p>
                <p class="text-md font-semibold text-gray-800 mb-4" x-text="modalProductTitle"></p>
                
                <div>
                    <label for="stock" class="block text-sm font-medium text-gray-700">Jumlah Stok Baru</label>
                    <div class="mt-1">
                        <input type="number" name="stock" id="stock" :value="modalCurrentStock" class="bg-white text-black shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" required min="0">
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                    Simpan Perubahan
                </button>
                <button @click="isModalOpen = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>
