<?php

namespace App\Console\Commands;

use App\Actions\CheckIfAllNeededServicesAreUpAndRunning;
use App\Support\Recorder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

abstract class PseudoDaemon extends Command
{
    protected $description = 'Run corresponding action';
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if(!Recorder::make()->allNeededServicesAreUpAndRunning()) {
            return 1;
        }

        try {
            app($this->action)
                ->execute();
        }
        catch(\Exception $exception) {
            report($exception);
        }
    }
}
