<?php

namespace App\Console\Commands;

use App\Support\ReleaseManager;
use Hammerstone\PseudoDaemon\IsPseudoDaemon;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

abstract class PseudoDaemon extends Command
{
    use IsPseudoDaemon;

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
        app($this->action)
            ->execute();
    }

    public function pseudoDaemonSleepSeconds()
    {
        return $this->sleepSeconds;
    }

    public function restartWhenChanged()
    {
        return ReleaseManager::currentRelease();
    }
}
