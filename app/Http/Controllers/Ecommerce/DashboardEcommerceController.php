<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Controllers\TiktokShop\TiktokShopGetAuthorizedShopController;
use App\Models\TiktokpedOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardEcommerceController extends Controller
{
    /**
     * Menampilkan halaman dashboard E-Commerce.
     */
    public function index(Request $request)
    {
        $tiktokShopApiController = new TiktokShopGetAuthorizedShopController();
        $tiktokShopData = $tiktokShopApiController->fetchShops();

        $tokopediaCardData = $this->getTokopediaCardData(null, null);

        return view('ecommerce.index', compact('tiktokShopData', 'tokopediaCardData'));
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
     * Logika inti untuk mengambil data statistik kartu Tokopedia.
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    private function getTokopediaCardData($startDate, $endDate): array
    {
        $query = TiktokpedOrder::where('status', 'COMPLETED');

        if ($startDate && $endDate) {
            $query->whereBetween('created_at_tiktok', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }

        $statsQuery = clone $query;
        $topBuyersQuery = clone $query; // Ganti nama variabel agar lebih jelas

        $totalNilai = $statsQuery->sum('total_amount');
        $totalPembeli = $statsQuery->distinct('recipient_name')->count('recipient_name');

        // ==================================================
        // ============ PERUBAHAN UTAMA DI SINI =============
        // ==================================================
        // Mengambil 3 pembeli teratas, bukan hanya satu
        $topBuyers = $topBuyersQuery
            ->select('recipient_name', DB::raw('count(*) as purchase_count'))
            ->groupBy('recipient_name')
            ->orderByDesc('purchase_count')
            ->orderByDesc('created_at_tiktok')
            ->take(3) // Mengambil 3 hasil teratas
            ->get();  // Mengambil koleksi hasil

        return [
            'total_nilai' => $totalNilai,
            'total_pembeli' => $totalPembeli,
            'top_buyers' => $topBuyers, // Ganti nama key menjadi plural
        ];
    }
}