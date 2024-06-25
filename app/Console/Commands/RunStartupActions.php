<?php

namespace App\Console\Commands;

use App\Actions\EnsureAppKeyIsSet;
use App\Actions\EnsureNetworkIsSetup;
use App\Actions\UpdateSoftware;
use App\Support\Mothership;
use App\Support\Recorder;
use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;

class RunStartupActions extends Command
{
    protected $signature = 'taggy:run-startup-actions';
    protected $description = 'Run startup actions';

    public function handle(Schedule $schedule)
    {
        Recorder::make()->waitUntilAllNeededServicesAreUpAndRunning();

        info('Running startup actions');
        $this->call('cache:clear');
        //$this->call('schedule:clear-cache');

        $mutexCleared = false;

        foreach ($schedule->events($this->laravel) as $event) {
            info('Checking ' . $event->command);
            if ($event->mutex->exists($event)) {
                info(sprintf('Deleting mutex for [%s]', $event->command));

                $event->mutex->forget($event);

                $mutexCleared = true;
            }
        }

        if (! $mutexCleared) {
           info('No mutex files were found.');
        }

        app(EnsureNetworkIsSetup::class)->execute();
        app(EnsureAppKeyIsSet::class)->execute();

        $counter = 0;
        while(!Mothership::make()->isOnline(disableCache: true) && $counter < 5) {
            sleep(5);
            $counter++;
        }

        $this->call(CleanLivestreamSegments::class);
        app(UpdateSoftware::class)->execute();
    }
}
