<?php

namespace App\Services\TiktokShop;

use App\Exceptions\TiktokApiException;
use App\Http\Controllers\TiktokShop\TiktokApiTrait;
use App\Models\EcommerceSetting;
use App\Models\TiktokProduct;
use App\Models\TiktokShop;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TiktokProductService
{
    use TiktokApiTrait;

    protected TiktokGetProductService $getProductService;

    public function __construct(TiktokGetProductService $getProductService)
    {
        $this->getProductService = $getProductService;
    }

    /**
     * Mengambil semua produk dari API, menyimpannya ke database,
     * dan memperbarui timestamp sinkronisasi.
     */
    public function syncProductsFromApi(int $pageSize = 100): int
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
        $productIds = $this->searchAndGetProductIds($shopConnection, $shopCipher, $pageSize);

        if (empty($productIds)) {
            $this->updateLastSyncTimestamp();
            return 0; // Tidak ada produk untuk disinkronkan
        }

        $syncedCount = 0;
        DB::beginTransaction();
        try {
            foreach ($productIds as $id) {
                $detail = $this->getProductService->getProductDetail($id);
                if ($detail) {
                    $this->saveOrUpdateProduct($detail);
                    $syncedCount++;
                }
            }

            $this->updateLastSyncTimestamp();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal saat sinkronisasi produk TikTok ke DB', ['error' => $e->getMessage()]);
            // Lempar kembali exception agar controller bisa menangkapnya
            throw new TiktokApiException('Proses sinkronisasi produk ke database gagal.');
        }

        return $syncedCount;
    }

    /**
     * Menyimpan atau memperbarui produk di database.
     */
    private function saveOrUpdateProduct(array $productData): void
    {
        // Kalkulasi data yang akan disimpan
        $prices = data_get($productData, 'skus.*.price.sale_price', []);
        $min_price = !empty($prices) ? min($prices) : 0;
        $max_price = !empty($prices) ? max($prices) : 0;
        $price_range = ($min_price == $max_price)
            ? 'Rp' . number_format($min_price)
            : 'Rp' . number_format($min_price) . ' - Rp' . number_format($max_price);

        $inventories = data_get($productData, 'skus.*.inventory', []);
        $total_stock = 0;
        if (is_array($inventories)) {
            foreach ($inventories as $inventory_group) {
                if (is_array($inventory_group)) {
                    $total_stock += array_sum(array_column($inventory_group, 'quantity'));
                }
            }
        }

        TiktokProduct::updateOrCreate(
            ['tiktok_product_id' => $productData['id']], // Kunci unik untuk mencari
            [
                'title'            => data_get($productData, 'title', 'Tanpa Judul'),
                
                // ==================================================
                // --- PERBAIKAN UTAMA ADA DI BARIS INI ---
                // ==================================================
                'status'           => data_get($productData, 'product_status', 'UNKNOWN'),
                
                'main_image_url'   => data_get($productData, 'main_images.0.urls.0'),
                'total_stock'      => $total_stock,
                'price_range'      => $price_range,
                'raw_data'         => json_encode($productData), // Simpan data mentah
            ]
        );
    }

    /**
     * Memperbarui timestamp di tabel settings.
     */
    private function updateLastSyncTimestamp(): void
    {
        EcommerceSetting::updateOrCreate(
            ['key' => 'tiktok_products_last_sync'],
            ['value' => now()]
        );
    }

    // Metode searchAndGetProductIds dan getShopCipher tetap sama, tidak perlu diubah
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