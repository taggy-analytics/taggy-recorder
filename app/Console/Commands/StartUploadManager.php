<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class StartUploadManager extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'taggy:start-upload-manager';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start the upload manager.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        app(\App\Actions\ManageUploads::class)
            ->execute();

        return 0;
    }
}
