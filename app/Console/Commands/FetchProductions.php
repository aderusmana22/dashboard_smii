<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\QAD\ProductionController;

class FetchProductions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qad:fetch-productions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch productions data from QAD';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $controller = new ProductionController();
        $controller->getProductions();
        $this->info('Productions data fetched successfully.');
    }
}
