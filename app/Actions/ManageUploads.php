<?php

namespace App\Actions;

use App\Actions\Mothership\GetCredentialsForUnauthenticatedCameras;
use App\Actions\Mothership\SendDiscoveredCamerasToMothership;
use App\CameraTypes\CameraType;
use App\Enums\RecordingFileType;
use App\Enums\RecordingStatus;
use App\Models\Camera;
use App\Models\Recording;
use App\Support\Mothership;
use App\Support\Uploader;
use Illuminate\Support\Str;
use Spatie\Watcher\Watch;

class ManageUploads
{
    private $interval = 20;
    public function execute()
    {
        $mothership = Mothership::make();

        while (true) {
            if($recording = Recording::where('status', RecordingStatus::PREPROCESSED)->first()) {
                if($mothership->isOnline()) {
                    $mothership->sendRecordingThumbnails($recording);
                }
                app(UploadRecording::class)
                    ->onQueue()
                    ->execute($recording);
            }
            elseif(true) {

            }
            else {
                sleep($this->interval);
            }
        }
    }
}
