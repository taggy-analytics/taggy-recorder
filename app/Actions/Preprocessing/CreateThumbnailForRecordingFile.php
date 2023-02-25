<?php

namespace App\Actions\Preprocessing;

use App\Enums\RecordingFileStatus;
use App\Models\RecordingFile;
use FFMpeg\Exception\RuntimeException;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Spatie\Image\Image;

class CreateThumbnailForRecordingFile
{
    public function execute(RecordingFile $file)
    {
            $exportedFramePath = "{$file->recording->thumbnailPath()}/{$file->name}.jpg";

        try {
            FFMpeg::open($file->videoPath())
                ->getFrameFromSeconds(0)
                ->export()
                ->toDisk('local')
                ->save($exportedFramePath);

            Image::load(Storage::disk('local')->path($exportedFramePath))
                ->optimize()
                ->height(320)
                ->quality(50)
                ->save();
        }
        catch(RuntimeException $e) {

        }

        $file->setStatus(RecordingFileStatus::THUMBNAIL_CREATED);
    }
}
