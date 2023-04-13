<?php

namespace App\Console\Commands;

use App\Support\Mothership;
use App\Support\Recorder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Preprovision extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'taggy:preprovision';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Preprovision';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if(!Storage::has(Recorder::SYSTEM_ID_FILENAME)) {
            Storage::put(Recorder::SYSTEM_ID_FILENAME, Str::random(100));
        }

        $this->info(base64_encode(json_encode([
            'system-id' => Recorder::make()->getSystemId(),
            'public-key' => Recorder::make()->getPublicKey(),
        ])));

        return 0;
    }
}
