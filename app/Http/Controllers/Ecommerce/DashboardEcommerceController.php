<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Controllers\TiktokShop\TiktokShopGetAuthorizedShopController;
use App\Models\EcommerceSetting;
use App\Models\MasterProduct;
use App\Models\ShopeeOrder;
use App\Models\TiktokpedOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class DashboardEcommerceController extends Controller
{
    /**
     * Menampilkan halaman dashboard E-Commerce.
     */
      public function index(Request $request)
    {
        // Filter Tanggal
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $startDateTime = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
        $endDateTime = $endDate ? Carbon::parse($endDate)->endOfDay() : null;

        $tiktokShopApiController = new TiktokShopGetAuthorizedShopController();
        $tiktokShopData = $tiktokShopApiController->fetchShops();

        $tokopediaCardData = $this->getTokopediaCardData($startDate, $endDate);
        $shopeeCardData = $this->getShopeeCardData($startDate, $endDate);
        $lowStockProducts = $this->getLowStockProducts();
        $topProducts = $this->getTopSellingProducts($startDateTime, $endDateTime);

        // == LOGIKA BARU: Mengambil daftar produk untuk filter chart ==
        $productsForFilter = $this->getUniqueProductNames($startDateTime, $endDateTime);

        return view('ecommerce.index', compact(
            'tiktokShopData',
            'tokopediaCardData',
            'shopeeCardData',
            'lowStockProducts',
            'topProducts',
            'productsForFilter', // <-- Kirim daftar produk ke view
            'startDate',
            'endDate'
        ));
    }

    private function getUniqueProductNames($startDateTime, $endDateTime)
    {
        // 1. Ambil nama produk unik dari Shopee
        $shopeeProductsQuery = DB::table('shopee_order_items')
            ->join('shopee_orders', 'shopee_order_items.shopee_order_id', '=', 'shopee_orders.id')
            ->where('shopee_orders.order_status', 'COMPLETED')
            ->select('shopee_order_items.item_name as product_name');

        // 2. Ambil nama produk unik dari TikTok
        $tiktokProductsQuery = DB::table('tiktokped_order_items')
            ->join('tiktokped_orders', 'tiktokped_order_items.tiktokped_order_id', '=', 'tiktokped_orders.id')
            ->where('tiktokped_orders.status', 'COMPLETED')
            ->select('tiktokped_order_items.product_name');

        // Terapkan filter tanggal jika ada
        if ($startDateTime && $endDateTime) {
            $shopeeProductsQuery->whereBetween('shopee_orders.create_time_shopee', [$startDateTime, $endDateTime]);
            $tiktokProductsQuery->whereBetween('tiktokped_orders.created_at_tiktok', [$startDateTime, $endDateTime]);
        }

        $shopeeProducts = $shopeeProductsQuery->distinct()->pluck('product_name');
        $tiktokProducts = $tiktokProductsQuery->distinct()->pluck('product_name');

        // 3. Gabungkan, ambil yang unik, dan urutkan
        return $shopeeProducts->merge($tiktokProducts)->unique()->sort()->values();
    }

    /**
     * == METHOD BARU ==
     * Endpoint AJAX untuk mengambil data grafik penjualan.
     */
    public function fetchChartData(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'product_name' => 'nullable|string',
        ]);

        $startDateTime = Carbon::parse($validated['start_date'])->startOfDay();
        $endDateTime = Carbon::parse($validated['end_date'])->endOfDay();
        $productName = $validated['product_name'] ?? 'all';

        // Query Shopee
        $shopeeSalesQuery = DB::table('shopee_orders')
            ->join('shopee_order_items', 'shopee_orders.id', '=', 'shopee_order_items.shopee_order_id')
            ->where('shopee_orders.order_status', 'COMPLETED')
            ->whereBetween('shopee_orders.create_time_shopee', [$startDateTime, $endDateTime])
            ->select(
                DB::raw('DATE(shopee_orders.create_time_shopee) as sale_date'),
                DB::raw('SUM(shopee_order_items.model_discounted_price * shopee_order_items.model_quantity_purchased) as daily_revenue'),
                DB::raw('SUM(shopee_order_items.model_quantity_purchased) as daily_quantity')
            )
            ->groupBy('sale_date');

        // Query TikTok
        $tiktokSalesQuery = DB::table('tiktokped_orders')
            ->join('tiktokped_order_items', 'tiktokped_orders.id', '=', 'tiktokped_order_items.tiktokped_order_id')
            ->where('tiktokped_orders.status', 'COMPLETED')
            ->whereBetween('tiktokped_orders.created_at_tiktok', [$startDateTime, $endDateTime])
            ->select(
                DB::raw('DATE(tiktokped_orders.created_at_tiktok) as sale_date'),
                DB::raw('SUM(tiktokped_order_items.sale_price * tiktokped_order_items.quantity) as daily_revenue'),
                DB::raw('SUM(tiktokped_order_items.quantity) as daily_quantity')
            )
            ->groupBy('sale_date');

        if ($productName !== 'all') {
            $shopeeSalesQuery->where('shopee_order_items.item_name', $productName);
            $tiktokSalesQuery->where('tiktokped_order_items.product_name', $productName);
        }

        $shopeeSales = $shopeeSalesQuery->get()->keyBy('sale_date');
        $tiktokSales = $tiktokSalesQuery->get()->keyBy('sale_date');

        // Gabungkan data dan siapkan untuk chart
        $period = CarbonPeriod::create($startDateTime, $endDateTime);
        $labels = [];
        $revenueData = [];
        $quantityData = [];

        foreach ($period as $date) {
            $dateString = $date->toDateString();
            $labels[] = $date->format('d M');

            $dailyRevenue = ($shopeeSales[$dateString]->daily_revenue ?? 0) + ($tiktokSales[$dateString]->daily_revenue ?? 0);
            $dailyQuantity = ($shopeeSales[$dateString]->daily_quantity ?? 0) + ($tiktokSales[$dateString]->daily_quantity ?? 0);

            $revenueData[] = round($dailyRevenue / 1000000, 2); // Dalam jutaan
            $quantityData[] = $dailyQuantity;
        }

        return response()->json([
            'labels' => $labels,
            'revenue' => $revenueData,
            'quantity' => $quantityData,
        ]);
    }

    private function getTopSellingProducts($startDateTime, $endDateTime)
    {
        // 1. Ambil data penjualan dari Shopee
        $shopeeSalesQuery = DB::table('shopee_order_items')
            ->join('shopee_orders', 'shopee_order_items.shopee_order_id', '=', 'shopee_orders.id')
            ->where('shopee_orders.order_status', 'COMPLETED')
            ->select(
                'shopee_order_items.item_name as product_name',
                'shopee_order_items.image_url',
                DB::raw('SUM(shopee_order_items.model_quantity_purchased) as total_sold')
            )
            ->groupBy('product_name', 'image_url');

        // 2. Ambil data penjualan dari TikTok
        $tiktokSalesQuery = DB::table('tiktokped_order_items')
            ->join('tiktokped_orders', 'tiktokped_order_items.tiktokped_order_id', '=', 'tiktokped_orders.id')
            ->where('tiktokped_orders.status', 'COMPLETED')
            ->select(
                'tiktokped_order_items.product_name',
                'tiktokped_order_items.sku_image as image_url',
                DB::raw('SUM(tiktokped_order_items.quantity) as total_sold')
            )
            ->groupBy('product_name', 'image_url');

        // Terapkan filter tanggal jika ada
        if ($startDateTime && $endDateTime) {
            $shopeeSalesQuery->whereBetween('shopee_orders.create_time_shopee', [$startDateTime, $endDateTime]);
            $tiktokSalesQuery->whereBetween('tiktokped_orders.created_at_tiktok', [$startDateTime, $endDateTime]);
        }

        // 3. Gabungkan hasil dari kedua query
        $shopeeSales = $shopeeSalesQuery->get();
        $tiktokSales = $tiktokSalesQuery->get();
        $combinedSales = $shopeeSales->merge($tiktokSales);

        // 4. Proses data gabungan untuk menjumlahkan penjualan produk yang sama
        return $combinedSales->groupBy('product_name')
            ->map(function ($group) {
                return (object) [
                    'product_name' => $group->first()->product_name,
                    'image_url' => $group->first()->image_url ?? 'https://via.placeholder.com/150',
                    'total_sold' => $group->sum('total_sold'),
                ];
            })
            ->sortByDesc('total_sold')
            ->take(3)
            ->values(); // Mengatur ulang key collection menjadi 0, 1, 2
    }

    private function getLowStockProducts()
    {
        $stockAlertThreshold = (int) (EcommerceSetting::where('key', 'stock_alert_threshold')->value('value') ?? 10);
        return MasterProduct::where('total_stock', '<=', $stockAlertThreshold)
            ->orderBy('total_stock', 'asc')
            ->take(10)
            ->get();
    }

    /**
     * Mengambil data statistik untuk kartu Tokopedia via AJAX.
     */
    public function fetchTokopediaStats(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $data = $this->getTokopediaCardData($startDate, $endDate);

        return response()->json($data);
    }

    /**
     * == METHOD BARU ==
     * Mengambil data statistik untuk kartu Shopee via AJAX.
     */
    public function fetchShopeeStats(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $data = $this->getShopeeCardData($startDate, $endDate);

        return response()->json($data);
    }

    /**
     * Logika inti untuk mengambil data statistik kartu Tokopedia.
     */
    private function getTokopediaCardData($startDate, $endDate): array
    {
        $query = TiktokpedOrder::where('status', 'COMPLETED');

        if ($startDate && $endDate) {
            $query->whereBetween('created_at_tiktok', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }

        $stats = (clone $query)->select(
            DB::raw('SUM(total_amount) as total_nilai'),
            DB::raw('COUNT(DISTINCT recipient_name) as total_pembeli')
        )->first();

        $topBuyers = (clone $query)
            ->select('recipient_name', DB::raw('count(*) as purchase_count'))
            ->groupBy('recipient_name')
            ->orderByDesc('purchase_count')
            ->take(3)
            ->get();

        return [
            'total_nilai' => $stats->total_nilai ?? 0,
            'total_pembeli' => $stats->total_pembeli ?? 0,
            'top_buyers' => $topBuyers,
        ];
    }

    /**
     * == METHOD BARU ==
     * Logika inti untuk mengambil data statistik kartu Shopee.
     */
    private function getShopeeCardData($startDate, $endDate): array
    {
        $query = ShopeeOrder::where('order_status', 'COMPLETED');

        if ($startDate && $endDate) {
            $query->whereBetween('create_time_shopee', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }

        $stats = (clone $query)->select(
            DB::raw('SUM(total_amount) as total_nilai'),
            DB::raw('COUNT(DISTINCT recipient_name) as total_pembeli')
        )->first();

        $topBuyers = (clone $query)
            ->select('recipient_name', DB::raw('count(*) as purchase_count'))
            ->groupBy('recipient_name')
            ->orderByDesc('purchase_count')
            ->take(3)
            ->get();

        return [
            'total_nilai' => $stats->total_nilai ?? 0,
            'total_pembeli' => $stats->total_pembeli ?? 0,
            'top_buyers' => $topBuyers,
        ];
    }
}