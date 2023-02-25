<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DeleteOldReleases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'taggy:delete-old-releases';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete old releases';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        app(\App\Actions\DeleteOldReleases::class)
            ->execute();

        return 0;
    }
}
