<?php

namespace App\Console;

use App\Console\Commands\CalculateLed;
use App\Console\Commands\FreeDiskSpace;
use App\Console\Commands\HandleCameras;
use App\Console\Commands\HandleRecordings;
use App\Console\Commands\MeasureTemperature;
use App\Console\Commands\MonitorRecordings;
use App\Console\Commands\RunHealthChecks;
use App\Console\Commands\RunMothershipActions;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command(HandleCameras::class)
            ->runInBackground()
            ->withoutOverlapping(1)
            ->everyFiveSeconds();

        $schedule->command(HandleRecordings::class)
            ->runInBackground()
            ->withoutOverlapping()
            ->everyTwentySeconds();

        $schedule->command(RunMothershipActions::class)
            ->runInBackground()
            ->withoutOverlapping()
            ->everyTwentySeconds();

        $schedule->command(MonitorRecordings::class)
            ->runInBackground()
            ->withoutOverlapping(1)
            ->everyFiveSeconds();

        $schedule->command(CalculateLed::class)
            ->runInBackground()
            ->everyFiveSeconds();

        $schedule->command(RunHealthChecks::class)
            ->everyMinute();

        $schedule->command(MeasureTemperature::class)
            ->everyFifteenSeconds();

        $schedule->command(FreeDiskSpace::class)
            ->withoutOverlapping(15)
            ->everyFiveMinutes();

        $schedule->command('clean:directories')
            ->everyTenMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
