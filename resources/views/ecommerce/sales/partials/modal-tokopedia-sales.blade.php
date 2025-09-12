{{-- resources/views/ecommerce/sales/partials/modal-tokopedia-sales.blade.php --}}

<div 
    x-show="isTokopediaModalOpen" 
    style="display: none;" 
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" 
    x-cloak
>
    <div 
        @click.away="isTokopediaModalOpen = false" 
        class="bg-white rounded-lg shadow-xl w-full max-w-5xl mx-4"
    >
        <div class="flex justify-between items-center p-4 border-b border-gray-200">
            <h3 class="text-xl font-bold text-gray-900">Daftar Lengkap Transaksi Tokopedia</h3>
            <button @click="isTokopediaModalOpen = false" class="text-gray-500 hover:text-gray-800 text-3xl leading-none">&times;</button>
        </div>
        <div class="p-6">
            <table id="tokopedia-sales-table" class="min-w-full divide-y divide-gray-200" style="width:100%">
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
                    @foreach ($all_tokopedia_sales as $sale)
                        <tr>
                             <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600 hover:text-indigo-800"><a href="#">{{ $sale['invoice_id'] }}</a></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">{{ $sale['customer_name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($sale['date'])->isoFormat('D MMM YYYY, HH:mm') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 font-semibold text-right">Rp {{ number_format($sale['total_amount'], 0, ',', '.') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($sale['status'] == 'Selesai')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Dibatalkan</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>