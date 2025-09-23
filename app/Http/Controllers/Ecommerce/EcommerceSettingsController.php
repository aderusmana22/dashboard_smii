<?php

namespace App\Http\Controllers\ECommerce;

use App\Http\Controllers\Controller;
use App\Models\EcommerceSetting;
use App\Models\TiktokShop; // Pastikan model ini ada
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
class EcommerceSettingsController extends Controller
{
    /**
     * Menampilkan halaman konfigurasi.
     */
    public function index(): View
    {
        // Ambil data koneksi TikTok
        $tiktokShop = TiktokShop::first();

        // Ambil nilai batas stok dari database.
        // Jika tidak ada, gunakan nilai default (misalnya: 10).
        $stockAlertLimit = EcommerceSetting::where('key', 'stock_alert_threshold')->value('value') ?? 10;

        return view('ecommerce.settings.index', compact('tiktokShop', 'stockAlertLimit'));
    }

    /**
     * Memperbarui pengaturan yang disimpan.
     */
    public function update(Request $request): RedirectResponse
    {
        // 1. Validasi input
        $validated = $request->validate([
            'stock_alert_threshold' => 'required|integer|min:0',
        ]);

        // 2. Simpan ke database menggunakan updateOrCreate
        // Ini akan membuat baris baru jika belum ada, atau memperbarui jika sudah ada.
        EcommerceSetting::updateOrCreate(
            ['key' => 'stock_alert_threshold'], // Kunci untuk mencari
            ['value' => $validated['stock_alert_threshold']] // Nilai untuk disimpan
        );

        // 3. Redirect kembali dengan pesan sukses
        return redirect()->route('ecommerce.settings.index')
                         ->with('success', 'Pengaturan berhasil disimpan.');
    }
}