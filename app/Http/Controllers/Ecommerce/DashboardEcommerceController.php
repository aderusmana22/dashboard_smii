<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Controllers\TiktokShop\TiktokShopGetAuthorizedShopController;
use App\Models\EcommerceSetting;
use App\Models\MasterProduct;
use App\Models\ShopeeOrder;
use App\Models\TiktokpedOrder; // Data tetap dari sini
use App\Models\ShopeeShop;
use App\Models\ShopeeProduct;
use App\Models\TiktokProduct; // Data tetap dari sini
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

        // Mengambil data dari controller lain
        $tiktokShopApiController = new TiktokShopGetAuthorizedShopController();
        $tiktokShopData = $tiktokShopApiController->fetchShops();

        // Mengambil data dari model
        $shopeeShop = ShopeeShop::first();

        // Mengembalikan nama variabel dan fungsi ke 'tokopedia'
        $tokopediaCardData = $this->getTokopediaCardData($startDate, $endDate);
        $shopeeCardData = $this->getShopeeCardData($startDate, $endDate);
        $lowStockProducts = $this->getLowStockProducts();
        $topProducts = $this->getTopSellingProducts($startDateTime, $endDateTime);
        $productsForFilter = $this->getUniqueProductNames($startDateTime, $endDateTime);
        $recentTransactions = $this->getRecentTransactions($startDateTime, $endDateTime);
        $quickActionData = $this->getQuickActionData();

        return view('ecommerce.index', compact(
            'tiktokShopData',
            'shopeeShop',
            'tokopediaCardData', // Variabel ini yang akan dikirim ke view
            'shopeeCardData',
            'lowStockProducts',
            'topProducts',
            'productsForFilter',
            'recentTransactions',
            'startDate',
            'endDate',
            'quickActionData'
        ));
    }

    /**
     * Mengambil data untuk modal Aksi Cepat melalui AJAX.
     */
    public function fetchModalData(Request $request)
    {
        $category = $request->input('category');
        $data = [];

        switch ($category) {
            case 'perluDiproses':
                $data = $this->getPerluDiprosesData();
                break;
            case 'dalamPengiriman':
                $data = $this->getDalamPengirimanData();
                break;
            case 'menungguPenyelesaian':
                $data = $this->getMenungguPenyelesaianData();
                break;
            case 'transaksiSelesai':
                $data = $this->getTransaksiSelesaiData();
                break;
            case 'transaksiDibatalkan':
                $data = $this->getTransaksiDibatalkanData();
                break;
            case 'produkTidakAktif':
                $data = $this->getProdukTidakAktifData();
                break;
            case 'produkHabis':
                $data = $this->getProdukHabisData();
                break;
        }

        return response()->json($data);
    }

    /**
     * Mengumpulkan semua data hitungan untuk tampilan awal dashboard.
     */
    private function getQuickActionData(): array
    {
        return [
            'perluDiproses' => $this->getPerluDiprosesData(true),
            'dalamPengiriman' => $this->getDalamPengirimanData(true),
            'menungguPenyelesaian' => $this->getMenungguPenyelesaianData(true),
            'transaksiSelesai' => $this->getTransaksiSelesaiData(true),
            'transaksiDibatalkan' => $this->getTransaksiDibatalkanData(true),
            'produkTidakAktif' => $this->getProdukTidakAktifData(true),
            'produkHabis' => $this->getProdukHabisData(true),
        ];
    }

    // ===================================================================
    // == FUNGSI-FUNGSI HELPER UNTUK SETIAP KATEGORI AKSI CEPAT ==
    // ===================================================================

    private function getPerluDiprosesData($countOnly = false)
    {
        $shopeeStatus = ['AWAITING_SHIPMENT'];
        $tiktokStatus = ['PROCESSED'];
        return $this->fetchOrdersByStatus($shopeeStatus, $tiktokStatus, $countOnly);
    }

    private function getDalamPengirimanData($countOnly = false)
    {
        $shopeeStatus = ['IN_TRANSIT'];
        $tiktokStatus = ['SHIPPED'];
        return $this->fetchOrdersByStatus($shopeeStatus, $tiktokStatus, $countOnly);
    }

    private function getMenungguPenyelesaianData($countOnly = false)
    {
        $shopeeStatus = ['DELIVERED'];
        $tiktokStatus = ['TO_CONFIRM_RECEIVE'];
        return $this->fetchOrdersByStatus($shopeeStatus, $tiktokStatus, $countOnly);
    }

    private function getTransaksiSelesaiData($countOnly = false)
    {
        $shopeeStatus = ['COMPLETED'];
        $tiktokStatus = ['COMPLETED'];
return $this->fetchOrdersByStatus($shopeeStatus, $tiktokStatus, $countOnly, 10);

    }

    private function getTransaksiDibatalkanData($countOnly = false)
    {
        $shopeeStatus = ['CANCELLED'];
        $tiktokStatus = ['CANCELLED'];
         return $this->fetchOrdersByStatus($shopeeStatus, $tiktokStatus, $countOnly, 10);
        
    }

    private function getProdukTidakAktifData($countOnly = false)
    {
        $shopeeQuery = ShopeeProduct::where('item_status', 'UNLIST');
        $tiktokQuery = TiktokProduct::whereIn('status', ['SELLER_DEACTIVATED', 'UNKNOWN', 'DELETED']);

        if ($countOnly) {
            return $shopeeQuery->count() + $tiktokQuery->count();
        }

        return [
            'shopee' => $shopeeQuery->select('id', 'item_name as product_name', 'item_sku as sku', 'main_image_url as image_url', 'item_status as status')->get(),
            'tokopedia' => $tiktokQuery->select('id', 'title as product_name', 'sku', 'main_image_url as image_url', 'status')->get(),
        ];
    }

    private function getProdukHabisData($countOnly = false)
    {
        $shopeeQuery = MasterProduct::whereHas('shopee_product', function ($q) { $q->where('item_status', 'NORMAL'); })->where('total_stock', '<=', 0);
        $tiktokQuery = MasterProduct::whereHas('tiktok_product', function ($q) { $q->where('status', 'ACTIVATE'); })->where('total_stock', '<=', 0);

        if ($countOnly) {
            $shopeeIds = $shopeeQuery->pluck('id');
            $tiktokIds = $tiktokQuery->pluck('id');
            return $shopeeIds->merge($tiktokIds)->unique()->count();
        }

        return [
            'shopee' => $shopeeQuery->with('shopee_product')->get()->map(function($p) {
                return ['id' => $p->id, 'product_name' => $p->title, 'sku' => $p->shopee_product->item_sku ?? 'N/A', 'image_url' => $p->shopee_product->main_image_url ?? null, 'stock' => $p->total_stock];
            }),
            'tokopedia' => $tiktokQuery->with('tiktok_product')->get()->map(function($p) {
                return ['id' => $p->id, 'product_name' => $p->title, 'sku' => $p->tiktok_product->sku ?? 'N/A', 'image_url' => $p->tiktok_product->main_image_url ?? null, 'stock' => $p->total_stock];
            }),
        ];
    }

    private function fetchOrdersByStatus(array $shopeeStatuses, array $tiktokStatuses, bool $countOnly, ?int $limit = null)
    {
        $shopeeQuery = ShopeeOrder::whereIn('order_status', $shopeeStatuses);
        $tiktokQuery = TiktokpedOrder::whereIn('status', $tiktokStatuses);

        if ($countOnly) {
            return $shopeeQuery->count() + $tiktokQuery->count();
        }

        // [MODIFIKASI] Terapkan limit jika parameter diisi
        if ($limit) {
            $shopeeQuery->take($limit);
            $tiktokQuery->take($limit);
        }

        return [
            'shopee' => $shopeeQuery->select('id', 'recipient_name', 'order_sn as order_id', 'total_amount')
                                  ->latest('create_time_shopee') // Mengambil yang terbaru
                                  ->get(),
            'tokopedia' => $tiktokQuery->select('id', 'recipient_name', 'tiktok_order_id as order_id', 'total_amount')
                                     ->latest('created_at_tiktok') // Mengambil yang terbaru
                                     ->get(),
        ];
    }

    // ===================================================================
    // == FUNGSI-FUNGSI ASLI YANG SUDAH ADA ==
    // ===================================================================

    private function getUniqueProductNames($startDateTime, $endDateTime)
    {
        $shopeeProductsQuery = DB::table('shopee_order_items')
            ->join('shopee_orders', 'shopee_order_items.shopee_order_id', '=', 'shopee_orders.id')
            ->where('shopee_orders.order_status', 'COMPLETED')
            ->select('shopee_order_items.item_name as product_name');

        $tiktokProductsQuery = DB::table('tiktokped_order_items')
            ->join('tiktokped_orders', 'tiktokped_order_items.tiktokped_order_id', '=', 'tiktokped_orders.id')
            ->where('tiktokped_orders.status', 'COMPLETED')
            ->select('tiktokped_order_items.product_name');

        if ($startDateTime && $endDateTime) {
            $shopeeProductsQuery->whereBetween('shopee_orders.create_time_shopee', [$startDateTime, $endDateTime]);
            $tiktokProductsQuery->whereBetween('tiktokped_orders.created_at_tiktok', [$startDateTime, $endDateTime]);
        }

        $shopeeProducts = $shopeeProductsQuery->distinct()->pluck('product_name');
        $tiktokProducts = $tiktokProductsQuery->distinct()->pluck('product_name');

        return $shopeeProducts->merge($tiktokProducts)->unique()->sort()->values();
    }

    public function fetchChartData(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'product_name' => 'nullable|string',
        ]);

        $startDateTime = Carbon::parse($validated['start_date'])->startOfDay();
        $endDateTime = Carbon::parse($validated['end_date'])->endOfDay();
        $productName = $validated['product_name'] ?? 'all';

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

        $period = CarbonPeriod::create($startDateTime, $endDateTime);
        $labels = [];
        $revenueData = [];
        $quantityData = [];

        foreach ($period as $date) {
            $dateString = $date->toDateString();
            $labels[] = $date->format('d M');

            $dailyRevenue = ($shopeeSales[$dateString]->daily_revenue ?? 0) + ($tiktokSales[$dateString]->daily_revenue ?? 0);
            $dailyQuantity = ($shopeeSales[$dateString]->daily_quantity ?? 0) + ($tiktokSales[$dateString]->daily_quantity ?? 0);

            $revenueData[] = round($dailyRevenue / 1000000, 2);
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
        $shopeeSalesQuery = DB::table('shopee_order_items')
            ->join('shopee_orders', 'shopee_order_items.shopee_order_id', '=', 'shopee_orders.id')
            ->where('shopee_orders.order_status', 'COMPLETED')
            ->select(
                'shopee_order_items.item_name as product_name',
                'shopee_order_items.image_url',
                DB::raw('SUM(shopee_order_items.model_quantity_purchased) as total_sold')
            )
            ->groupBy('product_name', 'image_url');

        $tiktokSalesQuery = DB::table('tiktokped_order_items')
            ->join('tiktokped_orders', 'tiktokped_order_items.tiktokped_order_id', '=', 'tiktokped_orders.id')
            ->where('tiktokped_orders.status', 'COMPLETED')
            ->select(
                'tiktokped_order_items.product_name',
                'tiktokped_order_items.sku_image as image_url',
                DB::raw('SUM(tiktokped_order_items.quantity) as total_sold')
            )
            ->groupBy('product_name', 'image_url');

        if ($startDateTime && $endDateTime) {
            $shopeeSalesQuery->whereBetween('shopee_orders.create_time_shopee', [$startDateTime, $endDateTime]);
            $tiktokSalesQuery->whereBetween('tiktokped_orders.created_at_tiktok', [$startDateTime, $endDateTime]);
        }

        $shopeeSales = $shopeeSalesQuery->get();
        $tiktokSales = $tiktokSalesQuery->get();
        $combinedSales = $shopeeSales->merge($tiktokSales);

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
            ->values();
    }

    private function getLowStockProducts()
    {
        $stockAlertThreshold = (int) (EcommerceSetting::where('key', 'stock_alert_threshold')->value('value') ?? 10);
        return MasterProduct::where('total_stock', '<=', $stockAlertThreshold)
            ->orderBy('total_stock', 'asc')
            ->take(10)
            ->get();
    }

    public function fetchTokopediaStats(Request $request)
    {
        $request->validate(['start_date' => 'nullable|date', 'end_date' => 'nullable|date|after_or_equal:start_date']);
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $data = $this->getTokopediaCardData($startDate, $endDate);
        return response()->json($data);
    }

    public function fetchShopeeStats(Request $request)
    {
        $request->validate(['start_date' => 'nullable|date', 'end_date' => 'nullable|date|after_or_equal:start_date']);
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $data = $this->getShopeeCardData($startDate, $endDate);
        return response()->json($data);
    }

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

    $topBuyers = (clone $query)->select('recipient_name', DB::raw('count(*) as purchase_count'))
        ->groupBy('recipient_name')
        ->orderByDesc('purchase_count')
        ->take(3)
        ->get();

    // === KALKULASI TONASE BARU UNTUK TOKOPEDIA ===
    $tonnageQuery = DB::table('tiktokped_order_items')
        ->join('tiktokped_orders', 'tiktokped_order_items.tiktokped_order_id', '=', 'tiktokped_orders.id')
        ->join('master_products', 'tiktokped_order_items.product_name', '=', 'master_products.title')
        ->join('product_tonnages', 'master_products.id', '=', 'product_tonnages.master_product_id')
        ->where('tiktokped_orders.status', 'COMPLETED');

    if ($startDate && $endDate) {
        $tonnageQuery->whereBetween('tiktokped_orders.created_at_tiktok', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
    }

    $totalTonnage = $tonnageQuery->sum(DB::raw('tiktokped_order_items.quantity * product_tonnages.tonnage'));
    // =============================================

    return [
        'total_nilai' => $stats->total_nilai ?? 0,
        'total_pembeli' => $stats->total_pembeli ?? 0,
        'top_buyers' => $topBuyers,
        'total_tonnage' => $totalTonnage ?? 0, // Tambahkan total_tonnage ke array
    ];
}

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
    
    $topBuyers = (clone $query)->select('recipient_name', DB::raw('count(*) as purchase_count'))
        ->groupBy('recipient_name')
        ->orderByDesc('purchase_count')
        ->take(3)
        ->get();

    // === KALKULASI TONASE BARU UNTUK SHOPEE ===
    $tonnageQuery = DB::table('shopee_order_items')
        ->join('shopee_orders', 'shopee_order_items.shopee_order_id', '=', 'shopee_orders.id')
        ->join('master_products', 'shopee_order_items.item_name', '=', 'master_products.title')
        ->join('product_tonnages', 'master_products.id', '=', 'product_tonnages.master_product_id')
        ->where('shopee_orders.order_status', 'COMPLETED');

    if ($startDate && $endDate) {
        $tonnageQuery->whereBetween('shopee_orders.create_time_shopee', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
    }

    $totalTonnage = $tonnageQuery->sum(DB::raw('shopee_order_items.model_quantity_purchased * product_tonnages.tonnage'));
    // ==========================================

    return [
        'total_nilai' => $stats->total_nilai ?? 0,
        'total_pembeli' => $stats->total_pembeli ?? 0,
        'top_buyers' => $topBuyers,
        'total_tonnage' => $totalTonnage ?? 0, // Tambahkan total_tonnage ke array
    ];
}

    private function getRecentTransactions($startDateTime, $endDateTime)
    {
        $shopeeQuery = DB::table('shopee_order_items')
            ->join('shopee_orders', 'shopee_order_items.shopee_order_id', '=', 'shopee_orders.id')
            ->where('shopee_orders.order_status', 'COMPLETED')
            ->select(
                'shopee_order_items.item_name as product_name',
                'shopee_order_items.image_url as product_image',
                'shopee_orders.recipient_name',
                'shopee_orders.create_time_shopee as transaction_time'
            );

        $tiktokQuery = DB::table('tiktokped_order_items')
            ->join('tiktokped_orders', 'tiktokped_order_items.tiktokped_order_id', '=', 'tiktokped_orders.id')
            ->where('tiktokped_orders.status', 'COMPLETED')
            ->select(
                'tiktokped_order_items.product_name',
                'tiktokped_order_items.sku_image as product_image',
                'tiktokped_orders.recipient_name',
                'tiktokped_orders.created_at_tiktok as transaction_time'
            );

        if ($startDateTime && $endDateTime) {
            $shopeeQuery->whereBetween('shopee_orders.create_time_shopee', [$startDateTime, $endDateTime]);
            $tiktokQuery->whereBetween('tiktokped_orders.created_at_tiktok', [$startDateTime, $endDateTime]);
        }

        $shopeeTransactions = $shopeeQuery->get();
        $tiktokTransactions = $tiktokQuery->get();

        return $shopeeTransactions->merge($tiktokTransactions)
            ->sortByDesc('transaction_time')
            ->take(3)
            ->values();
    }
}