<?php

namespace App\Console\Commands;

use App\Models\Camera;
use Illuminate\Console\Command;

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
        if (! Camera::noCameraIsRecording()) {
            $this->error('Cannot update software while a camera is recording');

            return 1;
        }

        $this->info(json_encode(app(\App\Actions\UpdateSoftware::class)->execute(), JSON_PRETTY_PRINT));
    }
}
