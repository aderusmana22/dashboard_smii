<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\EcommerceSetting;
use App\Models\TiktokpedOrder;      // Ganti model
use App\Models\TiktokpedOrderItem;   // Tambahkan model item
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;     // Tambahkan DB facade

class SalesDashboardController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Query dasar untuk pesanan yang sudah selesai
        $completedOrdersQuery = TiktokpedOrder::where('status', 'COMPLETED');

        if ($startDate && $endDate) {
            $completedOrdersQuery->whereBetween('created_at_tiktok', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }

        $summaryQuery = clone $completedOrdersQuery;
        $listQuery = clone $completedOrdersQuery;

        // Ambil ID pesanan yang sudah selesai untuk query item
        $completedOrderIds = $summaryQuery->pluck('id');

        // Data untuk ringkasan (summary)
        $tokopedia_summary = [
            'total_revenue' => $summaryQuery->sum('total_amount'),
            'total_orders' => $summaryQuery->count(),
            // Hitung total produk terjual dari item yang terkait
            'total_products_sold' => TiktokpedOrderItem::whereIn('tiktokped_order_id', $completedOrderIds)->sum('quantity'),
            'average_order_value' => $summaryQuery->count() > 0 ? $summaryQuery->average('total_amount') : 0,
        ];
        
        // Daftar transaksi di halaman utama
        $tokopedia_sales_list = $listQuery->latest('created_at_tiktok')->take(5)->get();

        // Hitung produk terlaris
        $tokopedia_top_products = TiktokpedOrderItem::query()
            ->select('product_name', DB::raw('SUM(quantity) as sold_count'), DB::raw('MIN(sku_image) as image_url'))
            ->whereIn('tiktokped_order_id', $completedOrderIds)
            ->groupBy('product_name')
            ->orderByDesc('sold_count')
            ->take(4) // Ambil 3 produk teratas
            ->get();

        $lastSyncTimestamp = EcommerceSetting::where('key', 'tiktok_last_sync')->value('value');
        $tokopediaAjaxUrl = route('ecommerce.tokopedia.orders.data', $request->only(['start_date', 'end_date']));

        // Data Dummy Shopee (tidak berubah)
        $shopee_summary = [ 'total_revenue' => 125850000, 'total_orders' => 480, 'total_products_sold' => 912, 'average_order_value' => 262187 ];
        $shopee_top_products = [ ['image_url' => 'https://placehold.co/100x100/f97316/white?text=SP1', 'name' => 'Tas Ransel Eiger Pro', 'sold_count' => 152], ['image_url' => 'https://placehold.co/100x100/f97316/white?text=SP2', 'name' => 'Sepatu Lari Adidas Run', 'sold_count' => 110], ['image_url' => 'https://placehold.co/100x100/f97316/white?text=SP3', 'name' => 'Kaos Polos Cotton Combed', 'sold_count' => 98] ];
        $shopee_sales_list = [ ['invoice_id' => 'INV/SP/2023/00123', 'customer_name' => 'Andi Wijaya', 'total_amount' => 350000], ['invoice_id' => 'INV/SP/2023/00122', 'customer_name' => 'Bunga Citra', 'total_amount' => 185000], ['invoice_id' => 'INV/SP/2023/00121', 'customer_name' => 'Rian Hidayat', 'total_amount' => 75000] ];
        $all_shopee_sales = array_merge($shopee_sales_list, [ ['invoice_id' => 'INV/SP/2023/00120', 'customer_name' => 'Dewi Lestari', 'total_amount' => 550000], ['invoice_id' => 'INV/SP/2023/00119', 'customer_name' => 'Siska Amelia', 'total_amount' => 210000] ]);

        return view('ecommerce.sales.index', [
            'tokopedia_last_sync' => $lastSyncTimestamp,
            'tokopedia_summary' => $tokopedia_summary,
            'tokopedia_sales_list' => $tokopedia_sales_list,
            'tokopedia_top_products' => $tokopedia_top_products,
            'tokopedia_ajax_url' => $tokopediaAjaxUrl,
            'all_shopee_sales' => $all_shopee_sales,
            'shopee_summary' => $shopee_summary,
            'shopee_sales_list' => $shopee_sales_list,
            'shopee_top_products' => $shopee_top_products,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    public function getPaginatedOrders(Request $request)
    {
        try {
            // Ganti model yang digunakan
            $query = TiktokpedOrder::where('status', 'COMPLETED');
            
            $searchTerm = $request->input('search', '');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            if ($startDate && $endDate) {
                $query->whereBetween('created_at_tiktok', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            }

            if ($searchTerm != '') {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('tiktok_order_id', 'like', '%' . $searchTerm . '%')
                      ->orWhere('recipient_name', 'like', '%' . $searchTerm . '%');
                });
            }

            $paginatedOrders = $query->latest('created_at_tiktok')->paginate(10)->withQueryString();
            
            // Ganti nama variabel di view partial
            $tableHtml = view('ecommerce.sales.partials.tokopedia-sales-table', ['all_tokopedia_sales' => $paginatedOrders])->render();
            return response()->json(['html' => $tableHtml]);

        } catch (\Exception $e) {
            // ... (error handling tidak berubah)
        }
    }
}