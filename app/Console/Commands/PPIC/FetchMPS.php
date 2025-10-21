<?php

namespace App\Console\Commands\PPIC;

use App\Http\Controllers\PPIC\MPSController;
use Illuminate\Console\Command;

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
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $controller = new MPSController();
        $controller->getMPS();
        $this->info('MPS data fetched successfully.');
    }
}
