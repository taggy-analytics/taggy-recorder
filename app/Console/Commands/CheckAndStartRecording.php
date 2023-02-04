<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckAndStartRecording extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'taggy:check-cameras';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check cameras';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        app(\App\Actions\CheckAndStartRecording::class)->execute();

        return 0;
    }
}
