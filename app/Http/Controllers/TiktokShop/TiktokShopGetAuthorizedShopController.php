<?php

namespace App\Http\Controllers\TiktokShop;

use App\Http\Controllers\Controller;
use App\Http\Controllers\TiktokShop\TiktokApiTrait; // <-- Gunakan Trait
use App\Models\TiktokShop;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TiktokShopGetAuthorizedShopController extends Controller
{
    // Gunakan Trait yang berisi logika API yang bisa digunakan kembali
    use TiktokApiTrait;

    /**
     * Method utama untuk mengambil data toko yang terotorisasi dari API TikTok.
     * Method ini tidak akan diakses melalui route, tapi dipanggil oleh controller lain.
     *
     * @return array|null
     */
    public function fetchShops(): ?array
    {
        // Ambil data koneksi toko dari database
        $shopConnection = TiktokShop::first();

        // Jika tidak ada koneksi, langsung kembalikan null
        if (!$shopConnection) {
            return null;
        }

        // Panggil method dari Trait untuk mengisi $this->appKey dan $this->appSecret
        $this->initializeTiktokApi();

        // Cek jika access token perlu di-refresh sebelum digunakan
        if ($shopConnection->access_token_expires_at->isPast()) {
            // Panggil method refreshToken dari Trait
            $shopConnection = $this->refreshToken($shopConnection);
        }

        // Siapkan parameter untuk request API /authorization/202309/shops
        $path = '/authorization/202309/shops';
        $timestamp = time();
        $params = [
            'app_key' => $this->appKey,
            'timestamp' => $timestamp,
        ];

        // Buat signature menggunakan method dari Trait
        $sign = $this->generateSignature($path, $params);
        $params['sign'] = $sign;

        // Panggil API menggunakan Laravel HTTP Client
        $response = Http::withHeaders([
            'x-tts-access-token' => $shopConnection->access_token,
            'Content-Type' => 'application/json',
        ])->get($this->apiBaseUrl . $path, $params);

        if ($response->successful() && $response->json('code') === 0) {
            // Jika berhasil, kembalikan data dari response
            return $response->json('data');
        }

        // Catat error jika API gagal untuk debugging
        Log::error('Failed to get TikTok authorized shops', [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        // Kembalikan null jika terjadi error
        return null;
    }
}