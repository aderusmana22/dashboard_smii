<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\EcommerceSetting;
use App\Models\ShopeeOrder;
use App\Models\ShopeeOrderItem;
use App\Models\TiktokpedOrder;
use App\Models\TiktokpedOrderItem;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalesDashboardController extends Controller
{
    public function index(Request $request): View
    {
        // 1. Filter Tanggal (Defaultnya bulan berjalan jika tidak ada input)
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        // ==================================================
        // PENGAMBILAN DATA SHOPEE
        // ==================================================
        
        // --- QUERY HANYA UNTUK PESANAN COMPLETED (Untuk Kalkulasi) ---
        $shopeeCompletedQuery = ShopeeOrder::where('order_status', 'COMPLETED')
                                           ->whereBetween('create_time_shopee', [$startDateTime, $endDateTime]);

        $shopeeSummaryData = (clone $shopeeCompletedQuery)
            ->select(
                DB::raw('SUM(total_amount) as total_revenue'),
                DB::raw('COUNT(id) as total_orders')
            )
            ->first();

        $shopeeProductsSoldCount = DB::table('shopee_order_items')
            ->join('shopee_orders', 'shopee_order_items.shopee_order_id', '=', 'shopee_orders.id')
            ->where('shopee_orders.order_status', 'COMPLETED') // Pastikan hanya COMPLETED
            ->whereBetween('shopee_orders.create_time_shopee', [$startDateTime, $endDateTime])
            ->sum('shopee_order_items.model_quantity_purchased');

        $shopee_summary = [
            'total_revenue' => $shopeeSummaryData->total_revenue ?? 0,
            'total_orders' => $shopeeSummaryData->total_orders ?? 0,
            'total_products_sold' => $shopeeProductsSoldCount,
            'average_order_value' => ($shopeeSummaryData->total_orders > 0) ? $shopeeSummaryData->total_revenue / $shopeeSummaryData->total_orders : 0,
        ];
        
        $shopee_top_products = ShopeeOrderItem::join('shopee_orders', 'shopee_order_items.shopee_order_id', '=', 'shopee_orders.id')
             ->where('shopee_orders.order_status', 'COMPLETED') // Pastikan hanya COMPLETED
             ->whereBetween('shopee_orders.create_time_shopee', [$startDateTime, $endDateTime])
             ->select('shopee_order_items.item_name', 'shopee_order_items.image_url', DB::raw('SUM(shopee_order_items.model_quantity_purchased) as sold_count'))
            ->groupBy('shopee_order_items.item_name', 'shopee_order_items.image_url')
            ->orderByDesc('sold_count')->take(5)->get();

        // --- QUERY UNTUK SEMUA STATUS (Untuk Daftar Transaksi Terakhir) ---
        $shopee_sales_list = ShopeeOrder::whereBetween('create_time_shopee', [$startDateTime, $endDateTime])
                                        ->latest('create_time_shopee')
                                        ->take(5)
                                        ->get();


        // ==================================================
        // PENGAMBILAN DATA TOKOPEDIA (TIKTOK)
        // ==================================================
        
        // --- QUERY HANYA UNTUK PESANAN COMPLETED (Untuk Kalkulasi) ---
        $tokopediaCompletedQuery = TiktokpedOrder::where('status', 'COMPLETED')
                                                 ->whereBetween('created_at_tiktok', [$startDateTime, $endDateTime]);

        $tokopediaSummaryData = (clone $tokopediaCompletedQuery)
            ->select(
                DB::raw('SUM(total_amount) as total_revenue'),
                DB::raw('COUNT(id) as total_orders')
            )
            ->first();

        $tokopediaProductsSoldCount = DB::table('tiktokped_order_items')
            ->join('tiktokped_orders', 'tiktokped_order_items.tiktokped_order_id', '=', 'tiktokped_orders.id')
            ->where('tiktokped_orders.status', 'COMPLETED') // Pastikan hanya COMPLETED
            ->whereBetween('tiktokped_orders.created_at_tiktok', [$startDateTime, $endDateTime])
            ->sum('tiktokped_order_items.quantity');
        
        $tokopedia_summary = [
            'total_revenue' => $tokopediaSummaryData->total_revenue ?? 0,
            'total_orders' => $tokopediaSummaryData->total_orders ?? 0,
            'total_products_sold' => $tokopediaProductsSoldCount,
            'average_order_value' => ($tokopediaSummaryData->total_orders > 0) ? $tokopediaSummaryData->total_revenue / $tokopediaSummaryData->total_orders : 0,
        ];

        $tokopedia_top_products = TiktokpedOrderItem::join('tiktokped_orders', 'tiktokped_order_items.tiktokped_order_id', '=', 'tiktokped_orders.id')
            ->where('tiktokped_orders.status', 'COMPLETED') // Pastikan hanya COMPLETED
            ->whereBetween('tiktokped_orders.created_at_tiktok', [$startDateTime, $endDateTime])
            ->select('tiktokped_order_items.product_name', 'tiktokped_order_items.sku_image as image_url', DB::raw('SUM(tiktokped_order_items.quantity) as sold_count'))
            ->groupBy('tiktokped_order_items.product_name', 'tiktokped_order_items.sku_image')
            ->orderByDesc('sold_count')->take(5)->get();

        // --- QUERY UNTUK SEMUA STATUS (Untuk Daftar Transaksi Terakhir) ---
        $tokopedia_sales_list = TiktokpedOrder::whereBetween('created_at_tiktok', [$startDateTime, $endDateTime])
                                              ->latest('created_at_tiktok')
                                              ->take(5)
                                              ->get();

        // --- Variabel lain untuk view ---
        $shopee_orders_last_sync = EcommerceSetting::where('key', 'shopee_orders_last_sync')->value('value');
        $tokopedia_last_sync = EcommerceSetting::where('key', 'tiktok_last_sync')->value('value');

        return view('ecommerce.sales.index', [
            'shopee_summary' => $shopee_summary,
            'shopee_sales_list' => $shopee_sales_list, // <- Ini sekarang berisi semua status
            'shopee_top_products' => $shopee_top_products,
            'shopee_orders_last_sync' => $shopee_orders_last_sync,
            'shopee_ajax_url' => route('ecommerce.shopee.orders.data', $request->query()),
            'tokopedia_summary' => $tokopedia_summary,
            'tokopedia_sales_list' => $tokopedia_sales_list, // <- Ini sekarang berisi semua status
            'tokopedia_top_products' => $tokopedia_top_products,
            'tokopedia_last_sync' => $tokopedia_last_sync,
            'tokopedia_ajax_url' => route('ecommerce.tokopedia.orders.data', $request->query()),
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    public function getPaginatedShopeeOrders(Request $request)
    {
        // HAPUS FILTER STATUS 'COMPLETED' dari sini
        $query = ShopeeOrder::query();

        // Filter tanggal tetap berlaku jika ada di request
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDateTime = Carbon::parse($request->start_date)->startOfDay();
            $endDateTime = Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('create_time_shopee', [$startDateTime, $endDateTime]);
        }

        // Filter pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_sn', 'like', '%' . $search . '%')
                  ->orWhere('recipient_name', 'like', '%' . $search . '%')
                  ->orWhere('order_status', 'like', '%' . $search . '%'); // Tambah pencarian berdasarkan status
            });
        }

        $all_shopee_sales = $query->latest('create_time_shopee')->paginate(10)->withQueryString();
        $html = view('ecommerce.sales.partials.shopee-orders-table', compact('all_shopee_sales'))->render();
        return response()->json(['html' => $html]);
    }

    public function getPaginatedOrders(Request $request)
    {
        // HAPUS FILTER STATUS 'COMPLETED' dari sini
        $query = TiktokpedOrder::query();

        // Filter tanggal tetap berlaku
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDateTime = Carbon::parse($request->start_date)->startOfDay();
            $endDateTime = Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('created_at_tiktok', [$startDateTime, $endDateTime]);
        }

        // Filter pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('tiktok_order_id', 'like', '%' . $search . '%')
                  ->orWhere('recipient_name', 'like', '%' . $search . '%')
                  ->orWhere('status', 'like', '%' . $search . '%'); // Tambah pencarian berdasarkan status
            });
        }
        
        $all_tokopedia_sales = $query->latest('created_at_tiktok')->paginate(10)->withQueryString();
        $html = view('ecommerce.sales.partials.tokopedia-orders-table', compact('all_tokopedia_sales'))->render();
        return response()->json(['html' => $html]);
    }
}