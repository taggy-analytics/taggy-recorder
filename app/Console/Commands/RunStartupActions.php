<?php

namespace App\Console\Commands;

use App\Actions\EnsureAppKeyIsSet;
use App\Actions\EnsureNetworkIsSetup;
use Illuminate\Console\Command;

class RunStartupActions extends Command
{
    protected $signature = 'taggy:run-startup-actions';
    protected $description = 'Run startup actions';

    public function handle()
    {
        $this->call('cache:clear');
        $this->call('schedule:clear-cache');
        app(EnsureNetworkIsSetup::class)->execute();
        app(EnsureAppKeyIsSet::class)->execute();
    }
}
