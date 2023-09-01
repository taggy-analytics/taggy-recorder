<?php

namespace App\Console\Commands;

use App\Support\Recorder;
use Illuminate\Console\Command;

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
        if(Recorder::make()->installationIsFinished()) {
            try {
                app($this->action)
                    ->execute();
            }
            catch(\Exception $exception) {
                report($exception);
            }
        }
    }
}
