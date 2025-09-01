<?php

namespace App\Actions\Mothership;

use App\Models\MothershipReport;

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
