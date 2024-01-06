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

class UpdateSoftware extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'taggy:update-software';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update software';

    /**
     * ra
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if(!Camera::noCameraIsRecording()) {
            $this->error('Cannot update software while a camera is recording');
            return 1;
        }

        $this->info(json_encode(app(\App\Actions\UpdateSoftware::class)->execute(), JSON_PRETTY_PRINT));
    }
}
