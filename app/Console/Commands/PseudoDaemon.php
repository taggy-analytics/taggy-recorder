<?php

namespace App\Console\Commands;

use App\Support\ReleaseManager;
use Hammerstone\PseudoDaemon\IsPseudoDaemon;
use Illuminate\Console\Command;

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
        info('Starting pseudo daemon ' . $this->action . '...');
        $this->runAsPseudoDaemon();
    }

    public function process()
    {
        info('Running ' . $this->action . '...');
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
