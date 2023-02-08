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
use ZipArchive;

class PreprocessRecording
{
    use QueueableAction;

    public function execute(Recording $recording)
    {
        $recording->setStatus(RecordingStatus::PREPROCESSING);
        $basePath = "recordings/{$recording->id}/thumbnails";

        $this->createThumbnails($recording, $basePath);
        $this->optimizeThumbnails($basePath);
        $this->createZipArchive($basePath);

        $recording->setStatus(RecordingStatus::PREPROCESSED);
    }

    private function createThumbnails(Recording $recording, $basePath)
    {
        $recording->files->nth(config('taggy-recorder.video-conversion.thumbnails.nth'))
            ->load('recording.camera')
            ->each(function (RecordingFile $file) use ($recording, $basePath) {
                FFMpeg::open($file->getPath('video'))
                    ->getFrameFromSeconds(0)
                    ->export()
                    ->toDisk('local')
                    ->save("{$basePath}/{$file->id}-{$file->created_at->toDateTimeLocalString()}.jpg");
            });
    }

    private function optimizeThumbnails($basePath)
    {
        foreach(Storage::files($basePath) as $file) {
            Image::load(Storage::disk('local')->path($file))
                ->optimize()
                ->height(320)
                ->quality(50)
                ->save();
        }
    }

    private function createZipArchive($basePath)
    {
        $zip = new ZipArchive();
        $zip->open(storage_path("app/{$basePath}/thumbnails.zip"), ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach(Storage::files($basePath) as $file) {
            $zip->addFile(Storage::disk('local')->path($file), pathinfo($file, PATHINFO_BASENAME));
        }

        $zip->close();
    }
}
