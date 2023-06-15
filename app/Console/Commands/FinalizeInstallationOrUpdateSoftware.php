<?php

namespace App\Console\Commands;

use App\Exceptions\RecorderNotAssociatedException;
use App\Support\Mothership;
use App\Support\Recorder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FinalizeInstallationOrUpdateSoftware extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'taggy:finalize-or-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Finalize installation / update software';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if(!Recorder::make()->installationIsFinished()) {
            return $this->call(FinalizeInstallation::class);
        }
        else {
            return $this->call(UpdateSoftware::class);
        }
    }
}
