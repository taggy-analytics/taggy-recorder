<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class HandleUploadRequests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'taggy:handle-upload-requests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Handle upload requests';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        app(\App\Actions\HandleUploadRequests::class)
            ->execute();
    }
}
