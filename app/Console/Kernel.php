<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

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

         // Menjalankan sinkronisasi produk setiap 30 menit
        $schedule->command('sync:shopee-products')->everyThirtyMinutes()->withoutOverlapping();
        $schedule->command('sync:tiktok-products')->everyThirtyMinutes()->withoutOverlapping();

        // Menjalankan sinkronisasi pesanan setiap 30 menit, pada menit ke-5 dan 35
        // Ini untuk memberi jeda dari sinkronisasi produk
        $schedule->command('sync:shopee-orders')->cron('5,35 * * * *')->withoutOverlapping();
        $schedule->command('sync:tiktok-orders')->cron('5,35 * * * *')->withoutOverlapping();

        // Menjalankan sinkronisasi tabel master setiap 30 menit, pada menit ke-10 dan 40
        // Dijalankan SETELAH sinkronisasi produk selesai
        $schedule->command('sync:master-products')->cron('10,40 * * * *')->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
