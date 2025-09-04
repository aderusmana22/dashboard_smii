<?php

namespace App\Http\Controllers;

use App\Models\LaporanKecelakaan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class DevTestingController extends Controller
{
    /**
     * Pastikan controller ini hanya bisa diakses di environment lokal.
     */
    public function __construct()
    {
        if (!App::isLocal()) {
            abort(404);
        }
    }

    /**
     * Menampilkan pratinjau email permintaan persetujuan.
     */
    public function previewEmailRequest()
    {
        // Ambil laporan terakhir sebagai data dummy.
        // Pastikan Anda memiliki setidaknya satu laporan di database.
        $laporan = LaporanKecelakaan::with('pembuatLaporan', 'managerHse')->latest()->first();

        if (!$laporan) {
            return "<h2>Data Laporan Kecelakaan tidak ditemukan.</h2> <p>Silakan buat setidaknya satu laporan terlebih dahulu untuk melihat pratinjau ini.</p>";
        }

        // Gunakan manager HSE dari laporan sebagai approver dummy
        $approver = $laporan->managerHse;

        if (!$approver) {
            // Jika manager HSE tidak terhubung, gunakan user pertama sebagai fallback
            $approver = User::first();
            if (!$approver) {
                return "<h2>User tidak ditemukan.</h2> <p>Pastikan Anda memiliki setidaknya satu user di database.</p>";
            }
        }
        
        // Buat URL dummy untuk tombol
        $approveUrl = route('dashboard') . '?action=approve&token=' . Str::random(60);
        $rejectUrl = route('dashboard') . '?action=reject&token=' . Str::random(60);

        // Panggil view email dengan data dummy
        return view('safetyboard.emails.request', [
            'laporan' => $laporan,
            'approver' => $approver,
            'approveUrl' => $approveUrl,
            'rejectUrl' => $rejectUrl,
        ]);
    }

    /**
     * Menampilkan pratinjau halaman sukses.
     */
    public function previewSuccess()
    {
        return view('safetyboard.emails.success', [
            'message' => 'Ini adalah pesan sukses untuk keperluan testing.'
        ]);
    }

    /**
     * Menampilkan pratinjau halaman invalid/gagal.
     */
    public function previewInvalid()
    {
        return view('safetyboard.emails.invalid', [
            'message' => 'Ini adalah pesan invalid atau gagal untuk keperluan testing.'
        ]);
    }

    /**
     * Menampilkan pratinjau form penolakan.
     */
    public function previewRejectForm()
    {
        return view('safetyboard.emails.reject_form', [
            'token' => 'dummy-token-untuk-testing-form-penolakan'
        ]);
    }
}