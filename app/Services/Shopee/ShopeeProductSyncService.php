<?php

namespace App\Services\Shopee;

use App\Exceptions\ShopeeApiException;
use App\Http\Controllers\Shopee\ShopeeApiTrait;
use App\Models\ShopeeProduct;
use App\Models\ShopeeShop;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

class ShopeeProductSyncService
{
    use ShopeeApiTrait;

    public function syncProductsFromApi(int $pageSize = 100): void
    {
        $shop = ShopeeShop::first();
        if (!$shop) {
            throw new ShopeeApiException('Koneksi toko Shopee tidak ditemukan di database.');
        }

        $this->initializeShopeeApi();
        if ($shop->access_token_expires_at->isPast()) {
            Log::info("Token Shopee untuk shop_id: {$shop->shop_id} telah kadaluarsa, mencoba refresh...");
            $shop = $this->refreshToken($shop);
        }

        $normalProductIds = $this->getProductIdsByStatus($shop, 'NORMAL', $pageSize);
        $unlistProductIds = $this->getProductIdsByStatus($shop, 'UNLIST', $pageSize);
        $productIds = array_unique(array_merge($normalProductIds, $unlistProductIds));

        if (empty($productIds)) {
            Log::info('Tidak ada produk Shopee yang ditemukan untuk disinkronkan.');
            return;
        }

        DB::transaction(function () use ($shop, $productIds) {
            foreach (array_chunk($productIds, 50) as $idChunk) {
                $details = $this->fetchProductDetails($shop, $idChunk);
                foreach ($details as $detail) {
                    $this->saveOrUpdateProduct($detail);
                }
            }
        });
    }

    private function getProductIdsByStatus(ShopeeShop $shop, string $status, int $pageSize): array
    {
        $allItemIds = [];
        $offset = 0;
        $hasNextPage = true;

        while ($hasNextPage) {
            $path = '/api/v2/product/get_item_list';
            $timestamp = time();

            // [PERBAIKAN] Sesuaikan pemanggilan dengan signature baru di Trait
            $sign = $this->generateApiSignature(
                $path,
                $timestamp,
                $shop->access_token,
                $shop->shop_id
            );

            $params = [
                'partner_id'   => $this->partnerId,
                'timestamp'    => $timestamp,
                'access_token' => $shop->access_token,
                'shop_id'      => $shop->shop_id,
                'sign'         => $sign,
                'offset'       => $offset,
                'page_size'    => $pageSize,
                'item_status'  => $status,
            ];

            $response = Http::get($this->apiBaseUrl . $path, $params);

            if ($response->failed() || !empty($response->json('error'))) {
                Log::error("Gagal mengambil daftar ID produk Shopee untuk status '{$status}'. Full Response:", [
                    'status_code' => $response->status(),
                    'body' => $response->body(),
                ]);
                $shopeeError = $response->json('error', 'unknown_error');
                throw new ShopeeApiException("Gagal mengambil daftar ID produk Shopee (status: {$status}). Error: '{$shopeeError}'.");
            }

            $data = $response->json('response');
            $itemIds = array_column($data['item'] ?? [], 'item_id');
            $allItemIds = array_merge($allItemIds, $itemIds);

            $hasNextPage = $data['has_next_page'] ?? false;
            $offset = $data['next_offset'] ?? 0;
        }
        
        Log::info("Ditemukan " . count($allItemIds) . " produk Shopee dengan status '{$status}'.");
        return $allItemIds;
    }

    private function fetchProductDetails(ShopeeShop $shop, array $itemIds): array
    {
        $path = '/api/v2/product/get_item_base_info';
        $timestamp = time();

        // [PERBAIKAN] Sesuaikan pemanggilan dengan signature baru di Trait
        $sign = $this->generateApiSignature(
            $path,
            $timestamp,
            $shop->access_token,
            $shop->shop_id
        );

        $baseParams = [
            'partner_id'   => $this->partnerId,
            'timestamp'    => $timestamp,
            'access_token' => $shop->access_token,
            'shop_id'      => $shop->shop_id,
            'sign'         => $sign,
        ];

        $url = $this->apiBaseUrl . $path . '?' . http_build_query($baseParams);

        foreach ($itemIds as $id) {
            $url .= '&item_id_list=' . $id;
        }

        $response = Http::get($url);

        if ($response->failed() || !empty($response->json('error'))) {
            Log::warning('Gagal mengambil detail produk Shopee', [
                'sent_url' => $url,
                'response' => $response->body()
            ]);
            return [];
        }

        return $response->json('response.item_list', []);
    }

    private function saveOrUpdateProduct(array $data): void
    {
        $stockInfo = data_get($data, 'stock_info_v2.summary_info.total_available_stock', 0);
        $priceInfo = data_get($data, 'price_info.0');
        // [Perbaikan Kecil] Menggunakan data_get untuk harga agar lebih aman
        $currentPrice = data_get($priceInfo, 'current_price', 0);
        $price = $priceInfo ? 'Rp' . number_format($currentPrice) : 'N/A';

        ShopeeProduct::updateOrCreate(
            ['shopee_item_id' => $data['item_id']],
            [
                'item_name'      => $data['item_name'],
                'item_sku'       => data_get($data, 'item_sku'),
                'item_status'    => $data['item_status'],
                'main_image_url' => data_get($data, 'image.image_url_list.0'),
                'total_stock'    => $stockInfo,
                'price_info'     => $price,
                'raw_data'       => json_encode($data),
            ]
        );
    }
}