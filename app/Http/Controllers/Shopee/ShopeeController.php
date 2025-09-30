<?php

namespace App\Http\Controllers\Shopee;

use App\Http\Controllers\Controller;
use App\Models\ShopeeShop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ShopeeController extends Controller
{
    use ShopeeApiTrait;

    /**
     * Constructor untuk inisialisasi API.
     */
    public function __construct()
    {
        $this->initializeShopeeApi();
    }

    /**
     * Mengarahkan pengguna ke halaman otorisasi Shopee.
     */
    public function redirectToAuth()
    {
        $path = '/api/v2/shop/auth_partner';
        $timestamp = time();
        $sign = $this->generateSignature($path, $timestamp);
        $redirectUrl = route('shopee.callback');

        $params = [
            'partner_id' => $this->partnerId,
            'timestamp' => $timestamp,
            'sign' => $sign,
            'redirect' => $redirectUrl,
        ];

        $authUrl = $this->apiBaseUrl . $path . '?' . http_build_query($params);

        return redirect($authUrl);
    }

    /**
     * Menangani callback dari Shopee setelah otorisasi.
     */
    public function handleCallback(Request $request)
    {
        Log::info('--- Shopee Callback Received ---', $request->all());

        $authCode = $request->input('code');
        $shopId = $request->input('shop_id');

        if (!$authCode || !$shopId) {
            Log::error('Authorization code or shop_id not found in Shopee callback.');
            return redirect()->route('ecommerce.settings.index')->with('error', 'Kode otorisasi tidak ditemukan. Proses gagal.');
        }

        // Tukarkan auth_code dengan access_token
        $path = '/api/v2/auth/token/get';
        $timestamp = time();
        $sign = $this->generateSignature($path, $timestamp);

        $url = $this->apiBaseUrl . $path . '?' . http_build_query([
            'partner_id' => $this->partnerId,
            'timestamp' => $timestamp,
            'sign' => $sign,
        ]);

        $response = Http::post($url, [
            'code' => $authCode,
            'shop_id' => (int) $shopId,
            'partner_id' => (int) $this->partnerId,
        ]);

        Log::info('Shopee API Response for token exchange.', [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        if ($response->failed() || !empty($response->json('error'))) {
            Log::error('Shopee Token Exchange Failed', ['response' => $response->body()]);
            return redirect()->route('ecommerce.settings.index')->with('error', 'Gagal mendapatkan token dari Shopee: ' . $response->json('message'));
        }

        $data = $response->json();
        Log::info('Shopee token exchange successful. Preparing to save data.', ['data' => $data]);

        try {
            // Hapus koneksi lama jika ada, untuk memastikan hanya ada 1 toko yang terhubung
            ShopeeShop::truncate();

            // Simpan data baru ke database
            ShopeeShop::create([
                'shop_id' => $shopId,
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'],
                'access_token_expires_at' => Carbon::now()->addSeconds($data['expire_in']),
                'refresh_token_expires_at' => Carbon::now()->addDays(30), // Refresh token valid 30 hari
            ]);
            Log::info('--- Shopee data saved to database successfully! ---');
        } catch (\Exception $e) {
            Log::error('Failed to save Shopee data to database.', ['error_message' => $e->getMessage()]);
            return redirect()->route('ecommerce.settings.index')->with('error', 'Gagal menyimpan data token Shopee ke database.');
        }

        return redirect()->route('ecommerce.settings.index')->with('success', 'Toko Shopee dengan ID "' . $shopId . '" berhasil terhubung!');
    }

    /**
     * Memutuskan koneksi dengan toko Shopee.
     */
    public function disconnect()
    {
        ShopeeShop::truncate();
        return redirect()->route('ecommerce.settings.index')->with('success', 'Koneksi dengan Shopee berhasil diputuskan.');
    }
}