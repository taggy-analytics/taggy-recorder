<?php

namespace App\Console\Commands;

use App\Support\Recorder;
use Illuminate\Console\Command;

abstract class PseudoDaemon extends Command
{
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if(Recorder::make()->installationIsFinished()) {
            info('Running ' . $this->action . '...');
            app($this->action)
                ->execute();
        }
    }
}
