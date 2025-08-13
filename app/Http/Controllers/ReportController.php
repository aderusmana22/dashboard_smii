<?php

namespace App\Http\Controllers;

use App\Models\JobMarsho; // <-- PENTING: Tambahkan ini untuk mengambil data
use Illuminate\Http\Request;
use App\Exports\MarshoJobsExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    /**
     * ==================================================================
     * == METODE YANG HILANG: Untuk menampilkan halaman laporan ==
     * ==================================================================
     * Metode ini mengambil semua data 'JobMarsho' dari database dan
     * mengirimkannya ke view 'reports.jobs_export'.
     *
     * @return \Illuminate\View\View
     */
    public function showJobsExportPage()
    {
        // Ambil semua data job dengan relasi yang dibutuhkan untuk ditampilkan di tabel
        $jobs = JobMarsho::with([
            'pengaju', 
            'penutup', 
            'area', 
            'latestRoute.toDepartment'
        ])->latest()->get(); // 'latest()' untuk mengurutkan dari yang terbaru

        // Kirim data ke view yang sudah Anda buat
        return view('reports.jobs_export', compact('jobs'));
    }

    /**
     * ==================================================================
     * == METODE YANG SUDAH ADA: Untuk memproses unduhan Excel ==
     * ==================================================================
     * Metode ini dipanggil oleh tombol "Export ke Excel" untuk membuat
     * dan mengunduh file spreadsheet.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportMarshoJobs()
    {
        // Membuat nama file yang unik dengan timestamp
        $fileName = 'marsho_jobs_export_' . date('Y-m-d_H-i-s') . '.xlsx';

        // Memanggil class export dan memulai proses download
        return Excel::download(new MarshoJobsExport, $fileName);
    }
}