<?php

namespace App\Console\Commands;

use App\Http\Controllers\QAD\SalesController;
use Illuminate\Console\Command;

class GetShipmentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qad:fetch-shipments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch shipments data from QAD';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $controller = new SalesController();
        $controller->getShipment();
        $this->info('Shipments data fetched successfully.');
    }
}
