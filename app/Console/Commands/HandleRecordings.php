<?php

namespace App\Console\Commands;

use Hammerstone\PseudoDaemon\IsPseudoDaemon;
use Illuminate\Console\Command;

class HandleRecordings extends Command
{
    use IsPseudoDaemon;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'taggy:handle-recordings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Handle recordings';

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
        app(\App\Actions\HandleRecordings::class)
            ->execute();
    }

}
