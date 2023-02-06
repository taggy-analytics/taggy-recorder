<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class WatchRecordedFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'taggy:watch-recorded-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Watch for new recorded files';

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
