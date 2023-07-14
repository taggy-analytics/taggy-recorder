<?php

namespace App\Console;

use App\Console\Commands\HandleCameras;
use App\Console\Commands\HandleRecordings;
use App\Console\Commands\HandleUploads;
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
            ->withoutOverlapping()
            ->everyFiveSeconds();

        /*
        $schedule->command(HandleUploads::class)
            ->runInBackground()
            ->withoutOverlapping()
            ->everyTwentySeconds();
        */

        $schedule->command(HandleRecordings::class)
            ->runInBackground()
            ->withoutOverlapping()
            ->everyTwentySeconds();

        $schedule->command(RunMothershipActions::class)
            ->runInBackground()
            ->withoutOverlapping()
            ->everyTwentySeconds();
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
