{{-- resources/views/ecommerce/sales/partials/modal-shopee-sales.blade.php --}}

<div 
    x-show="isShopeeModalOpen" 
    style="display: none;" 
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" 
    x-cloak
>
    <div 
        @click.away="isShopeeModalOpen = false" 
        class="bg-white rounded-lg shadow-xl w-full max-w-6xl mx-4" {{-- Dibuat lebih lebar agar konsisten --}}
    >
        <div class="flex justify-between items-center p-4 border-b border-gray-200">
            <h3 class="text-xl font-bold text-gray-900">Daftar Lengkap Transaksi Shopee</h3>
            <button @click="isShopeeModalOpen = false" class="text-gray-500 hover:text-gray-800 text-3xl leading-none">&times;</button>
        </div>

        {{-- BAGIAN BARU: Tombol & Info Sinkronisasi (disesuaikan untuk Shopee) --}}
        <div class="p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center bg-gray-50 gap-4">
            <div>
                <p class="text-xs text-gray-500">
                    Data terakhir diperbarui: 
                    <span class="font-medium text-gray-700">
                        {{-- Variabel ini perlu di-pass dari controller --}}
                        {{ $shopee_last_sync ?? 'N/A (Dummy Data)' }}
                    </span>
                </p>
            </div>
            {{-- Form ini disiapkan untuk fungsionalitas sinkronisasi Shopee di masa depan --}}
            <form action="#" method="POST" id="shopee-sync-form">
                @csrf
                <button type="submit" disabled class="px-4 py-2 bg-gray-400 text-white rounded-md text-sm font-medium flex items-center cursor-not-allowed" title="Fungsionalitas belum tersedia">
                    <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0011.667 0l3.181-3.183m-4.991-2.691V5.25a8.25 8.25 0 00-11.667 0v4.992" />
                    </svg>
                    <span>Perbarui Data dari Shopee</span>
                </button>
            </form>
        </div>

        <div class="p-6">
            <table id="shopee-sales-table" class="min-w-full divide-y divide-gray-200" style="width:100%">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelanggan</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    {{-- Menggunakan @forelse untuk menangani kasus data kosong --}}
                    @forelse ($all_shopee_sales as $sale)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600 hover:text-indigo-800"><a href="#">{{ $sale['invoice_id'] }}</a></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">{{ $sale['customer_name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ isset($sale['date']) ? \Carbon\Carbon::parse($sale['date'])->isoFormat('D MMM YYYY, HH:mm') : '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 font-semibold text-right">Rp {{ number_format($sale['total_amount'], 0, ',', '.') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if(isset($sale['status']) && $sale['status'] == 'Selesai')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Dibatalkan</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-10 text-gray-500">
                                Tidak ada data transaksi Shopee untuk ditampilkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>