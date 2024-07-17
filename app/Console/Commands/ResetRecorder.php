<?php

namespace App\Console\Commands;

use App\Exceptions\RecorderNotAssociatedException;
use App\Models\Camera;
use App\Support\Mothership;
use App\Support\Recorder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ResetRecorder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'taggy:reset-recorder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset recorder';

    /**
     * ra
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if($this->confirm('This resets everything and deletes all recordings! Are you sure')) {
            app(\App\Actions\ResetRecorder::class)->execute();
        }
    }
}
