<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * CATATAN PENTING:
 * Dengan alur kerja baru di mana gambar diunggah saat formulir utama disubmit,
 * controller ini dan rute yang terkait dengannya tidak lagi digunakan oleh
 * formulir Laporan Kecelakaan.
 *
 * Kode ini disediakan hanya untuk kelengkapan. Anda dapat menghapusnya
 * jika tidak ada editor TinyMCE lain di aplikasi Anda yang masih menggunakan
 * metode unggah gambar langsung (live upload).
 */
class EditorImageController extends Controller
{
    /**
     * Menyimpan gambar yang diunggah dari editor TinyMCE dan mengembalikan URL-nya.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // 1. Validasi file yang diunggah.
            $request->validate([
                'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Maksimal 2MB
            ]);

            // 2. Ambil file dari request. TinyMCE mengirimkannya dengan nama 'file'.
            $file = $request->file('file');

            // 3. Simpan file ke direktori 'storage/app/public/editor-uploads'.
            $path = $file->store('editor-uploads', 'public');

            // 4. Dapatkan URL publik ke file tersebut.
            $url = Storage::url($path);

            // 5. Kembalikan response JSON dengan kunci 'location' yang berisi URL.
            //    Ini adalah format yang wajib diikuti agar TinyMCE mengerti.
            return response()->json(['location' => $url]);

        } catch (\Exception $e) {
            // Jika terjadi error, catat ke log dan kembalikan response error.
            Log::error('Editor image upload failed: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal mengunggah gambar.'], 500);
        }
    }
}