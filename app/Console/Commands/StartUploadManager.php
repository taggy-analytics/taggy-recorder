<?php

namespace App\Console\Commands;

use Hammerstone\PseudoDaemon\IsPseudoDaemon;
use Illuminate\Console\Command;

class StartUploadManager extends Command
{
    use IsPseudoDaemon;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'taggy:start-upload-manager';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start the upload manager.';

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
        app(\App\Actions\ManageUploads::class)
            ->execute();
    }

    public function pseudoDaemonSleepSeconds()
    {
        return 20;
    }
}
