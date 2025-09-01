<?php

namespace App\Console\Commands;

use App\Support\Recorder;
use Illuminate\Console\Command;
use Laravel\Horizon\Console\HorizonCommand;

class Horizon extends Command
{
    protected $signature = 'taggy:horizon';

    protected $description = 'Start a master supervisor in the foreground';

    public function handle()
    {
        Recorder::make()->waitUntilAllNeededServicesAreUpAndRunning();

        sleep(10);
        retry(100, function () {
            $this->call(HorizonCommand::class);
        }, 5000);
    }
}
