<?php

namespace App\Http\Controllers\ECommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TiktokShop;

class EcommerceSettingsController extends Controller
{
    /**
     * Menampilkan halaman form konfigurasi.
     * Untuk saat ini, kita akan menggunakan nilai statis/sample.
     */
    public function index()
    {
        $tiktokShop = TiktokShop::first();

        // --- DATA SAMPLE ---
        // Anggap saja kita mengambil nilai '10' dari database.
        $stockAlertLimit = '10';

        // Tampilkan view dan kirim data sample ke sana
          return view('ecommerce.settings.index', compact('tiktokShop', 'stockAlertLimit'));
    }

    /**
     * Memproses pembaruan data konfigurasi dari form.
     * Logika penyimpanan ke database diabaikan untuk saat ini.
     */
    public function update(Request $request)
    {
        // Langkah 1: Validasi input dari form
        $request->validate([
            // Input 'stock_alert_threshold' harus ada, berupa angka, dan minimal 0.
            'stock_alert_threshold' => 'required|integer|min:0',
        ], [
            // Pesan error kustom dalam Bahasa Indonesia
            'stock_alert_threshold.required' => 'Batas stok peringatan wajib diisi.',
            'stock_alert_threshold.integer' => 'Batas stok peringatan harus berupa angka.',
            'stock_alert_threshold.min' => 'Batas stok peringatan tidak boleh negatif.',
        ]);

        // Langkah 2: Logika untuk menyimpan data (di-skip untuk saat ini)
        // $setting = Setting::updateOrCreate(
        //     ['key' => 'stock_alert_threshold'],
        //     ['value' => $request->stock_alert_threshold]
        // );
        // --- Logika di atas hanya contoh jika menggunakan database ---

        // Langkah 3: Redirect kembali ke halaman konfigurasi
        // Kirim juga 'flash message' untuk notifikasi sukses.
        return redirect()
                ->route('ecommerce.settings.index')
                ->with('success', 'Batas stok peringatan berhasil diperbarui!');
    }
}