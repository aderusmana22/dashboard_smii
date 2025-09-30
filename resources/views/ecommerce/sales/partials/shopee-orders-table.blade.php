<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200" style="width:100%">
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
            @forelse ($all_shopee_sales as $sale)
                <tr>
                     <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600 hover:text-indigo-800"><a href="#">{{ $sale->order_sn }}</a></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">{{ $sale->recipient_name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $sale->create_time_shopee ? $sale->create_time_shopee->isoFormat('D MMM YYYY, HH:mm') : '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 font-semibold text-right">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        @if($sale->order_status == 'COMPLETED')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">COMPLETED</span>
                        @elseif($sale->order_status == 'CANCELLED')
                             <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">CANCELLED</span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">{{ str_replace('_', ' ', $sale->order_status) }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-10 text-gray-500">
                        Tidak ada data yang cocok dengan filter atau pencarian Anda.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="p-4 border-t" id="shopee-pagination-links">
    {{ $all_shopee_sales->links() }}
</div>