<?php

namespace App\Console\Commands;

use App\Support\Recorder;
use Illuminate\Console\Command;

class GoPro extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'taggy:go-pro';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Activate pro mode';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Recorder::make()->activateProMode();
    }
}
