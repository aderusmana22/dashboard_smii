<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\MpsData;
use App\Models\ForecastImport;
use App\Models\DailyPpicReport;
use App\Exports\PPICStockExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ExportDailyReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        //
    }

    public function handle(): void
    {
        Log::info('Memulai job ekspor laporan harian (Aturan Ketat: Hanya Data Cocok)...');
        try {
            $now = Carbon::now();
            $currentMonth = $now->month;
            $currentYear = $now->year;

            // 1. Ambil data MPS untuk periode berjalan
            $mpsData = MpsData::where('month', $currentMonth)
                              ->where('year', $currentYear)
                              ->get();

            if ($mpsData->isEmpty()) {
                Log::info('Tidak ada data MPS untuk bulan ini. Tidak ada yang bisa diekspor.');
                return;
            }

            // 2. Ambil data Forecast untuk periode berjalan dan buat menjadi map untuk pencarian cepat
            $forecastDataMap = ForecastImport::where('month', $currentMonth)
                                          ->where('year', '>=', $currentYear)
                                          ->get()
                                          ->keyBy('item_number');

            if ($forecastDataMap->isEmpty()) {
                Log::info('Tidak ada data Forecast untuk bulan ini. Tidak ada yang bisa diekspor.');
                return;
            }

            $exportData = [];

            // 3. Loop melalui data MPS dan cari pasangannya di data Forecast
            foreach ($mpsData as $mpsItem) {
                // Cari forecast yang cocok berdasarkan item_number
                $forecastItem = $forecastDataMap->get($mpsItem->item_number);

                // HANYA jika data forecast DITEMUKAN, maka proses data ini
                if ($forecastItem) {
                    $matchedData = [
                        'item_number'    => $mpsItem->item_number,
                        'description'    => $mpsItem->description,
                        'month'          => $mpsItem->month,
                        'year'           => $mpsItem->year,
                        'inventory_qty'  => $mpsItem->inventory_qty,
                        'dispatch_qty'   => $mpsItem->dispatch_qty,
                        'allocated_qty'  => $mpsItem->allocated_qty,
                        'so_outstanding' => $mpsItem->so_outstanding,
                        'mps_qty'        => $mpsItem->mps_qty,
                        'forecast_unit'  => $forecastItem->unit,
                        'forecast_tonage'=> $forecastItem->tonage,
                    ];

                    // 4. Simpan atau update data yang cocok ke tabel laporan harian
                    DailyPpicReport::updateOrCreate(
                        [
                            'item_number' => $mpsItem->item_number,
                            'month'       => $mpsItem->month,
                            'year'        => $mpsItem->year,
                        ],
                        $matchedData
                    );
                    
                    $exportData[] = $matchedData;
                }
                // Jika tidak ada pasangan forecast, $mpsItem akan diabaikan.
            }

            if (empty($exportData)) {
                Log::info('Tidak ada data yang cocok antara MPS dan Forecast untuk diekspor.');
                return;
            }

            // 5. Ekspor ke file Excel
            $date = $now->format('d F Y');
            $fileName = 'reports/Report_Stock_PPIC_' . $now->format('Ymd') . '.xlsx';
            
            Excel::store(new PPICStockExport($exportData, $date), $fileName, 'local');

            Log::info('Job ekspor laporan harian berhasil. File disimpan di: ' . Storage::path($fileName));

        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan pada job ekspor laporan harian: ' . $e->getMessage());
        }
    }
}