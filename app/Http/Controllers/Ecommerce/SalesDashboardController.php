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
        // 1. Filter Tanggal (Diubah: Defaultnya NULL, hanya diisi jika ada input)
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $startDateTime = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
        $endDateTime = $endDate ? Carbon::parse($endDate)->endOfDay() : null;

        // ==================================================
        // PENGAMBILAN DATA SHOPEE
        // ==================================================
        // Mulai query dan terapkan filter status COMPLETED secara default
        $shopeeQuery = ShopeeOrder::where('order_status', 'COMPLETED');

        // Terapkan filter tanggal HANYA JIKA user memilih tanggal
        if ($startDateTime && $endDateTime) {
            $shopeeQuery->whereBetween('create_time_shopee', [$startDateTime, $endDateTime]);
        }

        $shopeeSummaryData = (clone $shopeeQuery)
            ->select(
                DB::raw('SUM(total_amount) as total_revenue'),
                DB::raw('COUNT(id) as total_orders')
            )
            ->first();

        // Query untuk produk terjual juga harus mengikuti filter yang sama
        $shopeeProductsQuery = DB::table('shopee_order_items')
            ->join('shopee_orders', 'shopee_order_items.shopee_order_id', '=', 'shopee_orders.id')
            ->where('shopee_orders.order_status', 'COMPLETED');
        
        if ($startDateTime && $endDateTime) {
            $shopeeProductsQuery->whereBetween('shopee_orders.create_time_shopee', [$startDateTime, $endDateTime]);
        }
        $shopeeProductsSoldCount = $shopeeProductsQuery->sum('shopee_order_items.model_quantity_purchased');

        $shopee_summary = [
            'total_revenue' => $shopeeSummaryData->total_revenue ?? 0,
            'total_orders' => $shopeeSummaryData->total_orders ?? 0,
            'total_products_sold' => $shopeeProductsSoldCount,
            'average_order_value' => ($shopeeSummaryData->total_orders > 0) ? $shopeeSummaryData->total_revenue / $shopeeSummaryData->total_orders : 0,
        ];

        $shopee_sales_list = (clone $shopeeQuery)->latest('create_time_shopee')->take(5)->get();

        // Query untuk top products juga harus mengikuti filter yang sama
        $shopeeTopProductsQuery = ShopeeOrderItem::join('shopee_orders', 'shopee_order_items.shopee_order_id', '=', 'shopee_orders.id')
             ->where('shopee_orders.order_status', 'COMPLETED');

        if ($startDateTime && $endDateTime) {
            $shopeeTopProductsQuery->whereBetween('shopee_orders.create_time_shopee', [$startDateTime, $endDateTime]);
        }

        $shopee_top_products = $shopeeTopProductsQuery
            ->select(
                'shopee_order_items.item_name',
                'shopee_order_items.image_url',
                DB::raw('SUM(shopee_order_items.model_quantity_purchased) as sold_count')
            )
            ->groupBy('shopee_order_items.item_name', 'shopee_order_items.image_url')
            ->orderByDesc('sold_count')
            ->take(5)
            ->get();

        $shopee_orders_last_sync = EcommerceSetting::where('key', 'shopee_orders_last_sync')->value('value');

        // ==================================================
        // PENGAMBILAN DATA TOKOPEDIA (TIKTOK)
        // ==================================================
        // Mulai query dan terapkan filter status COMPLETED secara default
        $tokopediaQuery = TiktokpedOrder::where('status', 'COMPLETED');

        // Terapkan filter tanggal HANYA JIKA user memilih tanggal
        if ($startDateTime && $endDateTime) {
            $tokopediaQuery->whereBetween('created_at_tiktok', [$startDateTime, $endDateTime]);
        }

        $tokopediaSummaryData = (clone $tokopediaQuery)
            ->select(
                DB::raw('SUM(total_amount) as total_revenue'),
                DB::raw('COUNT(id) as total_orders')
            )
            ->first();

        // Query untuk produk terjual juga harus mengikuti filter yang sama
        $tokopediaProductsQuery = DB::table('tiktokped_order_items')
            ->join('tiktokped_orders', 'tiktokped_order_items.tiktokped_order_id', '=', 'tiktokped_orders.id')
            ->where('tiktokped_orders.status', 'COMPLETED');

        if ($startDateTime && $endDateTime) {
            $tokopediaProductsQuery->whereBetween('tiktokped_orders.created_at_tiktok', [$startDateTime, $endDateTime]);
        }
        $tokopediaProductsSoldCount = $tokopediaProductsQuery->sum('tiktokped_order_items.quantity');

        $tokopedia_summary = [
            'total_revenue' => $tokopediaSummaryData->total_revenue ?? 0,
            'total_orders' => $tokopediaSummaryData->total_orders ?? 0,
            'total_products_sold' => $tokopediaProductsSoldCount,
            'average_order_value' => ($tokopediaSummaryData->total_orders > 0) ? $tokopediaSummaryData->total_revenue / $tokopediaSummaryData->total_orders : 0,
        ];

        $tokopedia_sales_list = (clone $tokopediaQuery)->latest('created_at_tiktok')->take(5)->get();

        // Query untuk top products juga harus mengikuti filter yang sama
        $tokopediaTopProductsQuery = TiktokpedOrderItem::join('tiktokped_orders', 'tiktokped_order_items.tiktokped_order_id', '=', 'tiktokped_orders.id')
            ->where('tiktokped_orders.status', 'COMPLETED');

        if ($startDateTime && $endDateTime) {
            $tokopediaTopProductsQuery->whereBetween('tiktokped_orders.created_at_tiktok', [$startDateTime, $endDateTime]);
        }

        $tokopedia_top_products = $tokopediaTopProductsQuery
            ->select(
                'tiktokped_order_items.product_name',
                'tiktokped_order_items.sku_image as image_url',
                DB::raw('SUM(tiktokped_order_items.quantity) as sold_count')
            )
            ->groupBy('tiktokped_order_items.product_name', 'tiktokped_order_items.sku_image')
            ->orderByDesc('sold_count')
            ->take(5)
            ->get();

        $tokopedia_last_sync = EcommerceSetting::where('key', 'tiktok_last_sync')->value('value');

        return view('ecommerce.sales.index', [
            'shopee_summary' => $shopee_summary,
            'shopee_sales_list' => $shopee_sales_list,
            'shopee_top_products' => $shopee_top_products,
            'shopee_orders_last_sync' => $shopee_orders_last_sync,
            'shopee_ajax_url' => route('ecommerce.shopee.orders.data', $request->query()),
            'tokopedia_summary' => $tokopedia_summary,
            'tokopedia_sales_list' => $tokopedia_sales_list,
            'tokopedia_top_products' => $tokopedia_top_products,
            'tokopedia_last_sync' => $tokopedia_last_sync,
            'tokopedia_ajax_url' => route('ecommerce.tokopedia.orders.data', $request->query()),
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    public function getPaginatedShopeeOrders(Request $request)
    {
        // Mulai query dan terapkan filter status COMPLETED secara default
        $query = ShopeeOrder::query()->where('order_status', 'COMPLETED');

        // Filter berdasarkan tanggal HANYA JIKA ada di request
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDateTime = Carbon::parse($request->start_date)->startOfDay();
            $endDateTime = Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('create_time_shopee', [$startDateTime, $endDateTime]);
        }

        // Filter berdasarkan pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_sn', 'like', '%' . $search . '%')
                  ->orWhere('recipient_name', 'like', '%' . $search . '%');
                  // Tidak perlu mencari order_status karena sudah difilter COMPLETED
            });
        }

        $all_shopee_sales = $query->latest('create_time_shopee')->paginate(10)->withQueryString();
        $html = view('ecommerce.sales.partials.shopee-orders-table', compact('all_shopee_sales'))->render();
        return response()->json(['html' => $html]);
    }

    public function getPaginatedOrders(Request $request)
    {
        // Mulai query dan terapkan filter status COMPLETED secara default
        $query = TiktokpedOrder::query()->where('status', 'COMPLETED');

        // Filter berdasarkan tanggal HANYA JIKA ada di request
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDateTime = Carbon::parse($request->start_date)->startOfDay();
            $endDateTime = Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('created_at_tiktok', [$startDateTime, $endDateTime]);
        }

        // Filter berdasarkan pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('tiktok_order_id', 'like', '%' . $search . '%')
                  ->orWhere('recipient_name', 'like', '%' . $search . '%');
                  // Tidak perlu mencari status karena sudah difilter COMPLETED
            });
        }
        
        $all_tokopedia_sales = $query->latest('created_at_tiktok')->paginate(10)->withQueryString();
        $html = view('ecommerce.sales.partials.tokopedia-orders-table', compact('all_tokopedia_sales'))->render();
        return response()->json(['html' => $html]);
    }
}