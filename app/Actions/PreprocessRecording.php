<?php

namespace App\Actions;

use App\Enums\RecordingStatus;
use App\Models\Recording;
use App\Models\RecordingFile;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Spatie\QueueableAction\QueueableAction;

class PreprocessRecording
{
    use QueueableAction;

    public function execute(Recording $recording)
    {
        $recording->setStatus(RecordingStatus::PREPROCESSING);

        $recording->files->nth(config('app.recording.preprocess.nth'))
            ->load('recording.camera')
            ->each(function (RecordingFile $file) use ($recording) {
                FFMpeg::open($file->getPath())
                    ->getFrameFromSeconds(0)
                    ->export()
                    ->toDisk('local')
                    ->save("recordings/{$recording->id}/{$file->id}-{$file->created_at}.jpg");
            });

        $recording->setStatus(RecordingStatus::PREPROCESSED);
    }

}
