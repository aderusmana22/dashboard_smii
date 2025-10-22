<?php

namespace App\Http\Controllers\PPIC;

use App\Http\Controllers\Controller;
use App\Models\ForecastImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ForecastsImport;
use App\Exports\ForecastTemplateExport;
use Carbon\Carbon; // <-- 1. TAMBAHKAN INI

class ForecastController extends Controller
{
    public function index()
    {
        return view('ppic.forecast.index');
    }

    /**
     * Mengambil data untuk ditampilkan di tabel via AJAX.
     * HANYA MENAMPILKAN DATA UNTUK BULAN DAN TAHUN BERJALAN.
     */
    public function fetchData()
    {
        // ===================================================================
        // PERUBAHAN DI SINI
        // ===================================================================
        $now = Carbon::now();
        $currentYear = $now->year;
        $currentMonth = $now->month;

        // Query diubah untuk memfilter berdasarkan tahun dan bulan saat ini
        $forecasts = ForecastImport::where('year', $currentYear)
                                    ->where('month', $currentMonth)
                                    ->orderBy('item_number', 'asc') // Urutkan berdasarkan item number
                                    ->get();
        // ===================================================================
        // AKHIR PERUBAHAN
        // ===================================================================

        return response()->json(['data' => $forecasts]);
    }

    public function downloadTemplate()
    {
        return Excel::download(new ForecastTemplateExport, 'template_import_forecast.xlsx');
    }

    /**
     * Menangani proses import dari file Excel.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            $file = $request->file('file');
            Excel::import(new ForecastsImport, $file);

            return response()->json(['success' => 'Data forecast berhasil diimpor!']);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];

            foreach ($failures as $failure) {
                $errors[] = "<b>Baris " . $failure->row() . "</b> (Kolom: " . $failure->attribute() . "): " . implode(', ', $failure->errors());
            }

            return response()->json(['error' => implode('<br>', $errors)], 422);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Import Forecast Error: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan server yang tidak terduga. Silakan hubungi administrator.'], 500);
        }
    }
}