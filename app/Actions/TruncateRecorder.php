<?php

namespace App\Actions;

use App\Models\LivestreamSegment;
use App\Models\MothershipReport;
use App\Models\Recording;
use App\Models\RecordingFile;
use App\Models\Transaction;
use App\Models\UserToken;
use Illuminate\Support\Facades\File;

class TruncateRecorder
{
    public function execute()
    {
        LivestreamSegment::truncate();
        MothershipReport::truncate();
        RecordingFile::truncate();
        Recording::truncate();
        Transaction::truncate();
        UserToken::truncate();

        File::deleteDirectory(storage_path('logs'), true);
        File::deleteDirectory(storage_path('app/public/recordings'), true);
    }
}
