<?php

namespace App\Services\TiktokShop;

use App\Exceptions\TiktokApiException;
use App\Http\Controllers\TiktokShop\TiktokApiTrait;
use App\Models\TiktokShop;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TiktokGetOrderListService
{
    use TiktokApiTrait;

    public function fetchAllOrders(): array
    {
        $allOrders = [];
        $pageToken = null;
        $pageNumber = 1;

        Log::info('SYNC-TIKTOK: Memulai proses pengambilan semua pesanan.');

        do {
            $queryParams = [
                'page_size' => 100, // Selalu coba ambil 100 data per halaman
            ];

            if ($pageToken) {
                $queryParams['page_token'] = $pageToken;
            }

            Log::info("SYNC-TIKTOK: Mengambil halaman ke-{$pageNumber}...");
            
            $response = $this->getOrderListPage($queryParams);

            // LOGGING PENTING: Tampilkan struktur response untuk debugging
            Log::info("SYNC-TIKTOK: Response dari API halaman {$pageNumber}", [
                'total_count' => $response['total_count'] ?? 'N/A',
                'has_next_page_token' => isset($response['next_page_token']),
                'orders_in_page' => count($response['orders'] ?? [])
            ]);

            if (!empty($response['orders'])) {
                $allOrders = array_merge($allOrders, $response['orders']);
            }

            $pageToken = $response['next_page_token'] ?? null;
            $pageNumber++;

        } while ($pageToken && !empty($response['orders']));

        Log::info("SYNC-TIKTOK: Proses pengambilan selesai. Total pesanan yang berhasil diambil dari API: " . count($allOrders));
        return $allOrders;
    }

    public function getOrderListPage(array $queryParams = []): ?array
    {
        $shopConnection = TiktokShop::first();
        if (!$shopConnection) throw new TiktokApiException('Koneksi toko TikTok tidak ditemukan di database.');

        $this->initializeTiktokApi();
        if ($shopConnection->access_token_expires_at->isPast()) {
            $shopConnection = $this->refreshToken($shopConnection);
        }

        $shopCipher = $this->getShopCipher($shopConnection);
        $path = '/order/202309/orders/search';
        $timestamp = time();
        $baseParams = [
            'app_key'     => $this->appKey,
            'timestamp'   => $timestamp,
            'shop_cipher' => $shopCipher,
        ];

        // PERBAIKAN LOGIKA: Gabungkan parameter dengan benar
        // Parameter dari fetchAllOrders ($queryParams) akan menimpa default jika ada.
        $params = array_merge($baseParams, $queryParams);

        $bodyJsonString = json_encode([]);
        $params['sign'] = $this->generateSignature($path, $params, $bodyJsonString);
        $fullUrl = $this->apiBaseUrl . $path . '?' . http_build_query($params);
        $response = Http::withHeaders([
            'x-tts-access-token' => $shopConnection->access_token,
            'content-type'       => 'application/json',
        ])->post($fullUrl, []);

        if ($response->successful() && $response->json('code') === 0) {
            return $response->json('data');
        }
        
        Log::warning("SYNC-TIKTOK: Gagal mengambil halaman daftar pesanan", [
            'request_url'    => $fullUrl,
            'response_body'  => $response->body()
        ]);
        
        throw new TiktokApiException('Gagal mengambil halaman daftar pesanan dari TikTok API: ' . $response->json('message'));
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