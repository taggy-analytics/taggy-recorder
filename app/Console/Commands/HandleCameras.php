<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class HandleCameras extends Command
{
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
        app(\App\Actions\HandleCameras::class)
            ->execute();

        return 0;
    }
}
