<?php

namespace App\Services\TiktokShop;

use App\Exceptions\TiktokApiException;
use App\Http\Controllers\TiktokShop\TiktokApiTrait;
use App\Models\TiktokShop;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * TANGGUNG JAWAB: Menyiapkan DAFTAR PRODUK LENGKAP untuk view.
 * Service ini menjadi ORCHESTRATOR:
 * 1. Panggil /products/search untuk dapat daftar ID.
 * 2. Gunakan TiktokGetProductService untuk mengambil detail setiap produk satu per satu.
 */
class TiktokProductService
{
    use TiktokApiTrait;

    protected TiktokGetProductService $getProductService;

    // Inject service detail ke dalam service daftar
    public function __construct(TiktokGetProductService $getProductService)
    {
        $this->getProductService = $getProductService;
    }

    /**
     * Mengambil daftar produk lengkap yang siap ditampilkan di view.
     */
    public function getProductList(int $pageSize = 20): array
    {
        $shopConnection = TiktokShop::first();
        if (!$shopConnection) {
            throw new TiktokApiException('Koneksi toko TikTok tidak ditemukan.');
        }

        $this->initializeTiktokApi();
        if ($shopConnection->access_token_expires_at->isPast()) {
            $shopConnection = $this->refreshToken($shopConnection);
        }

        $shopCipher = $this->getShopCipher($shopConnection);

        // LANGKAH 1: Cari produk untuk mendapatkan semua ID-nya.
        $productIds = $this->searchAndGetProductIds($shopConnection, $shopCipher, $pageSize);

        if (empty($productIds)) {
            return [];
        }

        // LANGKAH 2: Lakukan perulangan dan panggil service detail untuk setiap ID.
        $detailedProducts = [];
        foreach ($productIds as $id) {
            // Panggil service lain untuk mengambil detail satu per satu
            $detail = $this->getProductService->getProductDetail($id);
            if ($detail) { // Hanya tambahkan jika berhasil diambil
                $detailedProducts[] = $detail;
            }
        }

        return $detailedProducts;
    }

    private function searchAndGetProductIds(TiktokShop $shopConnection, string $shopCipher, int $pageSize): array
    {
        $path = '/product/202309/products/search';
        $timestamp = time();

        $params = [
            'app_key'     => $this->appKey,
            'timestamp'   => $timestamp,
            'shop_cipher' => $shopCipher,
            'page_size'   => $pageSize,
        ];

        $bodyJsonString = json_encode([]);
        $params['sign'] = $this->generateSignature($path, $params, $bodyJsonString);
        $fullUrl = $this->apiBaseUrl . $path . '?' . http_build_query($params);

        $response = Http::withHeaders([
            'x-tts-access-token' => $shopConnection->access_token,
            'Content-Type'       => 'application/json',
        ])->post($fullUrl, []);

        if ($response->successful() && $response->json('code') === 0) {
            return array_column($response->json('data.products', []), 'id');
        }

        Log::error('Gagal saat mencari ID produk TikTok (Langkah 1)', ['body' => $response->body()]);
        throw new TiktokApiException('Langkah 1 Gagal: Tidak dapat mengambil daftar ID produk.');
    }

    private function getShopCipher(TiktokShop $shopConnection): string
    {
        $path = '/authorization/202309/shops';
        $timestamp = time();
        $params = ['app_key' => $this->appKey, 'timestamp' => $timestamp];
        $params['sign'] = $this->generateSignature($path, $params);

        $response = Http::withHeaders(['x-tts-access-token' => $shopConnection->access_token])
                        ->get($this->apiBaseUrl . $path, $params);

        if ($response->successful() && $response->json('code') === 0 && !empty($response->json('data.shops'))) {
            return $response->json('data.shops')[0]['cipher'];
        }
        throw new TiktokApiException('Gagal mendapatkan informasi toko (shop_cipher).');
    }
}