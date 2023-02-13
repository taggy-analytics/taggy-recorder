<?php

namespace App\Actions\Preprocessing;

use App\Enums\RecordingFileStatus;
use App\Models\RecordingFile;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Spatie\Image\Image;

class CreateThumbnailForRecordingFile
{
    public function execute(RecordingFile $file)
    {
        $exportedFramePath = "{$file->recording->thumbnailPath()}/{$file->id}.jpg";

        FFMpeg::open($file->getPath('video'))
            ->getFrameFromSeconds(0)
            ->export()
            ->toDisk('local')
            ->save($exportedFramePath);

        Image::load(Storage::disk('local')->path($exportedFramePath))
            ->optimize()
            ->height(320)
            ->quality(50)
            ->save();

        $file->setStatus(RecordingFileStatus::THUMBNAIL_CREATED);
    }
}
