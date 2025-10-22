<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Http\Controllers\PPIC\MPSController;
use App\Models\MpsData;
use Illuminate\Support\Facades\Log;

class FetchMPSDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        //
    }

    public function handle(): void
    {
        Log::info('Memulai job pengambilan data MPS...');
        try {
            $mpsController = new MPSController();
            $mpsData = $mpsController->getMPS(true); // true untuk return data

            if (empty($mpsData)) {
                Log::warning('Tidak ada data MPS yang diterima dari QAD.');
                return;
            }

            foreach ($mpsData as $item) {
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
            Log::info('Job pengambilan data MPS berhasil diselesaikan.');
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan pada job pengambilan data MPS: ' . $e->getMessage());
        }
    }
}