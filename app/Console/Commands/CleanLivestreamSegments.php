<?php

namespace App\Console\Commands;

use App\Models\LivestreamSegment;
use Illuminate\Console\Command;

class CleanLivestreamSegments extends Command
{
    protected $signature = 'taggy:clean-livestream-segments';

    protected $description = 'Clean livestream segments';

    public function handle()
    {
        LivestreamSegment::where('uploaded_at', '<', now()->subMinutes(5))->delete();
        LivestreamSegment::where('created_at', '<', now()->subHours(5))->delete();
    }
}
