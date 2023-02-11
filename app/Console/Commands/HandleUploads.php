<?php

namespace App\Console\Commands;

use Hammerstone\PseudoDaemon\IsPseudoDaemon;
use Illuminate\Console\Command;

class HandleUploads extends Command
{
    use IsPseudoDaemon;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'taggy:handle-uploads';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Handle uploads';

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
        app(\App\Actions\HandleUploads::class)
            ->execute();
    }

    public function pseudoDaemonSleepSeconds()
    {
        return 20;
    }
}
