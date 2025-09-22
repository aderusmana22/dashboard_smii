<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
// Import controller yang baru dibuat
use App\Http\Controllers\TiktokShop\TiktokShopGetAuthorizedShopController;
use Illuminate\Http\Request;

class DashboardEcommerceController extends Controller
{
    /**
     * Menampilkan halaman dashboard E-Commerce dengan data dari TikTok Shop.
     */
    public function index()
    {
        // Buat instance dari controller yang bertanggung jawab mengambil data
        $tiktokShopApiController = new TiktokShopGetAuthorizedShopController();

        // Panggil method untuk mengambil data toko
        $tiktokShopData = $tiktokShopApiController->fetchShops();

        // Kirim data (baik berisi data toko atau null) ke view
        return view('ecommerce.index', compact('tiktokShopData'));
    }
}