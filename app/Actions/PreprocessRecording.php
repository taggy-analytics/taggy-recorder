<?php

namespace App\Actions;

use App\Enums\RecordingStatus;
use App\Models\Recording;
use App\Models\RecordingFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Spatie\Image\Image;
use Spatie\QueueableAction\QueueableAction;

class PreprocessRecording
{
    use QueueableAction;

    public function execute(Recording $recording)
    {
        $recording->setStatus(RecordingStatus::PREPROCESSING);
        $basePath = "recordings/{$recording->id}/thumbnails";

        $recording->files->nth(config('taggy-recorder.video-conversion.thumbnails.nth'))
            ->load('recording.camera')
            ->each(function (RecordingFile $file) use ($recording, $basePath) {
                FFMpeg::open($file->getPath('video'))
                    ->getFrameFromSeconds(0)
                    ->export()
                    ->toDisk('local')
                    ->save("{$basePath}/{$file->id}-{$file->created_at}.jpg");
            });

        foreach(Storage::files($basePath) as $file) {
            $this->optimizeThumbnail(Storage::disk('local')->path($file));
        }

        $recording->setStatus(RecordingStatus::PREPROCESSED);
    }

    private function optimizeThumbnail($thumbnail)
    {
        Image::load($thumbnail)
            ->optimize()
            ->height(320)
            ->quality(50)
            ->save();
    }
}
