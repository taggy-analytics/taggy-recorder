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

class SetupRouter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'taggy:setup-router';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup router';

    /**
     * ra
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        app(\App\Actions\SetupRouter::class)->execute();
    }
}
