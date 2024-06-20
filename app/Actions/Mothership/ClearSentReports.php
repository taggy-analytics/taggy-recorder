<?php

namespace App\Actions\Mothership;

use App\Actions\CalculateLed;
use App\Models\MothershipReport;
use App\Support\Recorder;
use Spatie\LaravelIgnition\Facades\Flare;

class ClearSentReports
{
    public function execute()
    {
        MothershipReport::query()
            ->whereNotNull('processed_at')
            ->where('processed_at', '<=', now()->subDay())
            ->delete();
    }
}
