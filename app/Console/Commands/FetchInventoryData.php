<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\QAD\InventoryController;

class FetchInventoryData extends Command
{
    protected $signature = 'qad:fetch-inventory';
    protected $description = 'Fetch inventory data from QAD';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $controller = new InventoryController();
        $controller->getDashboardInventory();
    }
}
