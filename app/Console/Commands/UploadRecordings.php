<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UploadRecordings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'taggy:upload-recordings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload recordings';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        app(\App\Actions\WatchRecordedFiles::class)
            ->execute();

        return 0;
    }
}
