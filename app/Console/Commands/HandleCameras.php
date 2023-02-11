<?php

namespace App\Console\Commands;

use Hammerstone\PseudoDaemon\IsPseudoDaemon;
use Illuminate\Console\Command;

class HandleCameras extends Command
{
    use IsPseudoDaemon;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'taggy:discover-cameras';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Discover cameras';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->runAsPseudoDaemon();
    }

    public function process()
    {
        app(\App\Actions\HandleCameras::class)
            ->execute();
    }
}
