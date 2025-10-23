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
use App\Models\User;
use App\Exports\PPICStockExport;
use App\Mail\DailyReportMail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ExportDailyReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
    }

    public function handle(): void
    {
        Log::info('Memulai job ekspor laporan harian (Aturan Ketat: Hanya Data Cocok)...');
        try {
            $now = Carbon::now();
            $currentMonth = $now->month;
            $currentYear = $now->year;

            $mpsData = MpsData::where('month', $currentMonth)
                              ->where('year', $currentYear)
                              ->get();

            if ($mpsData->isEmpty()) {
                Log::info('Tidak ada data MPS untuk bulan ini. Tidak ada yang bisa diekspor.');
                return;
            }

            $forecastDataMap = ForecastImport::where('month', $currentMonth)
                                          ->where('year', '>=', $currentYear)
                                          ->get()
                                          ->keyBy('item_number');

            if ($forecastDataMap->isEmpty()) {
                Log::info('Tidak ada data Forecast untuk bulan ini. Tidak ada yang bisa diekspor.');
                return;
            }

            $exportData = [];

            foreach ($mpsData as $mpsItem) {
                $forecastItem = $forecastDataMap->get($mpsItem->item_number);

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
            }

            if (empty($exportData)) {
                Log::info('Tidak ada data yang cocok antara MPS dan Forecast untuk diekspor.');
                return;
            }

            $date = $now->format('d F Y');
            $fileName = 'reports/Report_Stock_PPIC_' . $now->format('Ymd') . '.xlsx';
            
            Excel::store(new PPICStockExport($exportData, $date), $fileName, 'local');
            $filePath = Storage::disk('local')->path($fileName);
            Log::info('File laporan sementara berhasil dibuat di: ' . $filePath);


            $users = User::role('data-emails')->get();

            if ($users->isEmpty()) {
                Log::warning('Tidak ada user dengan role "data-emails" yang ditemukan. Email tidak dikirim.');
            } else {
                foreach ($users as $user) {
                    try {
                        Mail::to($user->email)->send(new DailyReportMail($user->name, $now->format('d-M-Y'), $filePath));
                        Log::info('Email laporan berhasil dikirim ke: ' . $user->email);
                    } catch (\Exception $e) {
                        Log::error('Gagal mengirim email ke ' . $user->email . ': ' . $e->getMessage());
                    }
                }
            }

            if (Storage::disk('local')->exists($fileName)) {
                Storage::disk('local')->delete($fileName);
                Log::info('File laporan sementara berhasil dihapus: ' . $fileName);
            }

        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan pada job ekspor laporan harian: ' . $e->getMessage());
        }
    }
}