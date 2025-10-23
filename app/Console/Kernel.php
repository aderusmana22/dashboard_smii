<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\FetchMPSDataJob;
use App\Jobs\ExportDailyReportJob;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('qad:fetch-inventory')->everyThirtyMinutes();

        // Menjadwalkan perintah fetch-productions setiap 30 menit
        $schedule->command('qad:fetch-productions')->everyThirtyMinutes();

        // Menjadwalkan perintah fetch-shipments setiap 30 menit
        $schedule->command('qad:fetch-shipments')->everyThirtyMinutes();

        // BAGIAN 2: SIKLUS SINKRONISASI PRODUK (PRODUK -> MASTER)
        $schedule->command('sync:shopee-products')
            ->everyThirtyMinutes()
            ->withoutOverlapping()
            ->onSuccess(function () {
                $this->call('sync:master-products');
            });

        $schedule->command('sync:tiktok-products')
            ->everyThirtyMinutes()
            ->withoutOverlapping()
            ->onSuccess(function () {
                $this->call('sync:master-products');
            });

        // BAGIAN 3: SIKLUS SINKRONISASI PESANAN & STOK (PESANAN -> STOK)
        $schedule->command('sync:run-all')
            ->everyFifteenMinutes()
            ->withoutOverlapping();

        $schedule->command('qad:fetch-mps')->dailyAt('07:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
