<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Services\TiktokShop\TiktokProductService;
use App\Services\TiktokShop\TiktokGetProductService;
use App\Exceptions\TiktokApiException;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ProductController extends Controller
{
    protected TiktokProductService $tiktokProductService;
    protected TiktokGetProductService $tiktokGetProductService;

    /**
     * Constructor untuk meng-inject kedua service.
     */
    public function __construct(
        TiktokProductService $tiktokProductService,
        TiktokGetProductService $tiktokGetProductService
    ) {
        $this->tiktokProductService = $tiktokProductService;
        $this->tiktokGetProductService = $tiktokGetProductService;
    }

    /**
     * Menampilkan halaman DAFTAR PRODUK.
     * Menggunakan TiktokProductService yang sudah berisi logika 2 langkah.
     */
    public function index(): View|RedirectResponse
    {
        try {
            // Controller hanya perlu memanggil satu metode ini.
            // Semua kerumitan (2 panggilan API) sudah ditangani di dalam service.
            $products = $this->tiktokProductService->getProductList();

            return view('ecommerce.product', compact('products'));

        } catch (TiktokApiException $e) {
            Log::error('Error di ProductController@index: ' . $e->getMessage());
            return redirect()->route('dashboard')
                ->with('error', 'Gagal memuat daftar produk dari TikTok: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan halaman DETAIL PRODUK.
     * Menggunakan TiktokGetProductService untuk mengambil data satu produk.
     * (Anda perlu membuat route dan view untuk ini, contoh: route('products.show', $id))
     */
    public function show(string $productId): View|RedirectResponse
    {
        try {
            $product = $this->tiktokGetProductService->getProductDetail($productId);

            if (!$product) {
                return redirect()->route('ecommerce.products.index')
                                 ->with('error', 'Produk tidak ditemukan.');
            }
            
            // Anda perlu membuat view baru bernama 'ecommerce.product-detail'
            return view('ecommerce.product-detail', compact('product'));

        } catch (TiktokApiException $e) {
            Log::error("Error di ProductController@show (ID: {$productId}): " . $e->getMessage());
            return redirect()->route('ecommerce.products.index')
                ->with('error', 'Gagal memuat detail produk: ' . $e->getMessage());
        }
    }
}