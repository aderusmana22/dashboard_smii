<?php

namespace App\Console\Commands\PPIC;

use Illuminate\Console\Command;
use App\Http\Controllers\PPIC\MPSController; // Tetap digunakan
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

class FetchMPS extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qad:fetch-mps';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch MPS data from QAD, save it, then generate and email the daily PPIC report.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // ===================================================================
            // BAGIAN 1: Mengambil dan Menyimpan Data MPS (Logika dari FetchMPSDataJob)
            // ===================================================================
            $this->info('Starting MPS data fetch...');
            Log::info('Starting MPS data fetch from command...');

            $mpsController = new MPSController();
            $mpsDataFromQad = $mpsController->getMPS(true); // true untuk return data

            if (empty($mpsDataFromQad)) {
                Log::warning('No MPS data received from QAD. Command stopped.');
                $this->warn('No MPS data received from QAD. Command stopped.');
                return 1; // Keluar dengan status error
            }

            foreach ($mpsDataFromQad as $item) {
                MpsData::updateOrCreate(
                    [
                        'item_number' => $item['Item Number'],
                        'month'       => $item['Month'],
                        'year'        => $item['Year'],
                    ],
                    [
                        'description'    => $item['Description'],
                        'uom'            => $item['UOM'],
                        'net_weight'     => $item['Net Weight'],
                        'inventory_qty'  => $item['Inventory Qty'],
                        'dispatch_qty'   => $item['Dispatch Qty'],
                        'allocated_qty'  => $item['Allocated Qty'],
                        'so_outstanding' => $item['SO Outstanding'],
                        'mps_qty'        => $item['MPS Qty'],
                    ]
                );
            }
            $this->info('MPS data fetched and saved successfully.');
            Log::info('MPS data fetched and saved successfully.');

            // ===================================================================
            // BAGIAN 2: Membuat dan Mengirim Laporan (Logika dari ExportDailyReportJob)
            // ===================================================================
            $this->info('Starting daily report generation and export...');
            Log::info('Starting daily report generation and export...');

            $now = Carbon::now();
            $currentMonth = $now->month;
            $currentYear = $now->year;

            // Ambil data MPS yang baru saja disimpan
            $mpsData = MpsData::where('month', $currentMonth)
                              ->where('year', $currentYear)
                              ->get();

            if ($mpsData->isEmpty()) {
                $this->warn('No MPS data found in the database for this month. Report cannot be generated.');
                Log::info('No MPS data found in the database for this month. Report cannot be generated.');
                return 1;
            }

            // Ambil data Forecast
            $forecastDataMap = ForecastImport::where('month', $currentMonth)
                                          ->where('year', '>=', $currentYear)
                                          ->get()
                                          ->keyBy('item_number');

            if ($forecastDataMap->isEmpty()) {
                $this->warn('No Forecast data found for this month. Report cannot be generated.');
                Log::info('No Forecast data found for this month. Report cannot be generated.');
                return 1;
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
                        ['item_number' => $mpsItem->item_number, 'month' => $mpsItem->month, 'year' => $mpsItem->year],
                        $matchedData
                    );
                    
                    $exportData[] = $matchedData;
                }
            }

            if (empty($exportData)) {
                $this->warn('No matching data between MPS and Forecast. Report will not be sent.');
                Log::info('No matching data between MPS and Forecast. Report will not be sent.');
                return 0; // Selesai dengan sukses, tapi tidak ada yang dikirim
            }

            // Ekspor ke file Excel
            $date = $now->format('d F Y');
            $fileName = 'reports/Report_Stock_PPIC_' . $now->format('Ymd') . '.xlsx';
            Excel::store(new PPICStockExport($exportData, $date), $fileName, 'local');
            $filePath = Storage::disk('local')->path($fileName);
            $this->info('Temporary report file created at: ' . $filePath);

            // Ambil user dan kirim email
            $users = User::role('naim')->get();

            if ($users->isEmpty()) {
                Log::warning('No users with "naim" role found. Email will not be sent.');
                $this->warn('No users with "naim" role found. Email will not be sent.');
            } else {
                foreach ($users as $user) {
                    Mail::to($user->email)->send(new DailyReportMail($user->name, $now->format('d-M-Y'), $filePath));
                    $this->info('Report email sent to: ' . $user->email);
                    Log::info('Report email sent to: ' . $user->email);
                }
            }

            // Hapus file setelah dikirim
            if (Storage::disk('local')->exists($fileName)) {
                Storage::disk('local')->delete($fileName);
                $this->info('Temporary report file has been deleted.');
                Log::info('Temporary report file has been deleted.');
            }

            $this->info('Command completed successfully.');
            return 0; // Selesai dengan sukses

        } catch (\Exception $e) {
            Log::error('An error occurred in qad:fetch-mps command: ' . $e->getMessage());
            $this->error('An error occurred: ' . $e->getMessage());
            return 1; // Keluar dengan status error
        }
    }
}