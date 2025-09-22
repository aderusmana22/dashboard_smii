<?php

namespace App\Http\Controllers;

use App\Models\TiktokShop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon; // <-- Import Carbon untuk manipulasi waktu

class TiktokController extends Controller
{
    private $appKey;
    private $appSecret;
    private $authBaseUrl = 'https://auth.tiktok-shops.com';

    /**
     * Constructor untuk mengambil kredensial dari file config.
     */
    public function __construct()
    {
        $this->appKey = config('services.tiktok.key');
        $this->appSecret = config('services.tiktok.secret');
    }

    /**
     * Mengarahkan pengguna ke halaman otorisasi TikTok Shop.
     */
    public function redirectToAuth()
    {
        $state = Str::random(40);
        session(['tiktok_oauth_state' => $state]);

        $params = [
            'app_key' => $this->appKey,
            'state' => $state,
        ];

        $authUrl = $this->authBaseUrl . '/oauth/authorize?' . http_build_query($params);

        return redirect($authUrl);
    }

    /**
     * Menangani callback dari TikTok Shop setelah otorisasi.
     */
    public function handleCallback(Request $request)
    {
        Log::info('--- TikTok Callback Received ---');

        if (empty($request->input('state')) || ($request->input('state') !== session('tiktok_oauth_state'))) {
            Log::error('State mismatch.', ['session_state' => session('tiktok_oauth_state'), 'request_state' => $request->input('state')]);
            session()->forget('tiktok_oauth_state');
            return redirect()->route('ecommerce.settings.index')->with('error', 'Invalid state parameter. Proses otorisasi gagal.');
        }
        Log::info('State validation successful.');

        $authCode = $request->input('code');
        if (!$authCode) {
            Log::error('Authorization code not found in callback request.');
            return redirect()->route('ecommerce.settings.index')->with('error', 'Authorization code not found. Proses otorisasi gagal.');
        }
        Log::info('Authorization code received.', ['auth_code' => $authCode]);

        // Tukarkan auth_code dengan access_token
        $response = Http::get($this->authBaseUrl . '/api/v2/token/get', [
            'app_key' => $this->appKey,
            'app_secret' => $this->appSecret,
            'auth_code' => $authCode,
            'grant_type' => 'authorized_code',
        ]);

        Log::info('TikTok API Response for token exchange.', [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        if ($response->failed() || $response->json('code') !== 0) {
            Log::error('TikTok Token Exchange Failed', ['response' => $response->body()]);
            return redirect()->route('ecommerce.settings.index')->with('error', 'Gagal mendapatkan token dari TikTok Shop: ' . $response->json('message'));
        }

        $data = $response->json('data');
        Log::info('Token exchange successful. Preparing to save data.', ['data' => $data]);

        try {
            // Hapus koneksi lama jika ada, untuk memastikan hanya ada 1 toko yang terhubung
            TiktokShop::truncate();
            
            // Simpan data baru ke database
            TiktokShop::create([
                'open_id' => $data['open_id'],
                'seller_name' => $data['seller_name'],
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'],
                
                // === LOGIKA YANG DIPERBAIKI ===
                // Konversi Unix timestamp absolut dari API menjadi format DATETIME
                'access_token_expires_at' => Carbon::createFromTimestamp($data['access_token_expire_in']),
                'refresh_token_expires_at' => Carbon::createFromTimestamp($data['refresh_token_expire_in']),
            ]);
            Log::info('--- Data saved to database successfully! ---');
        } catch (\Exception $e) {
            Log::error('Failed to save data to database.', ['error_message' => $e->getMessage()]);
            return redirect()->route('ecommerce.settings.index')->with('error', 'Gagal menyimpan data token ke database.');
        }

        return redirect()->route('ecommerce.settings.index')->with('success', 'Toko TikTok "' . $data['seller_name'] . '" berhasil terhubung!');
    }

    /**
     * Memutuskan koneksi dengan toko TikTok Shop.
     */
    public function disconnect()
    {
        TiktokShop::truncate();
        return redirect()->route('ecommerce.settings.index')->with('success', 'Koneksi dengan TikTok Shop berhasil diputuskan.');
    }
}