<?php

namespace App\Actions;

use Alchemy\BinaryDriver\Exception\ExecutionFailureException;
use App\Actions\Preprocessing\CreateThumbnailForRecordingFile;
use App\Enums\RecordingFileStatus;
use App\Enums\RecordingStatus;
use App\Models\Camera;
use App\Models\Recording;
use App\Models\RecordingFile;
use App\Support\Recorder;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class HandleRecordings
{
    public function execute()
    {
        $this->setPreprocessingStatusForFinishedRecordings();
        $this->chooseRecordingFilesToBeUsedInThumbnails();
        $this->createThumbnailsForRecordingFiles();
        $this->checkIfThumbnailCreationWasFinishedForRecording();
        // $this->createZipFileWithThumbnails();
        $this->createMovieWithThumbnails();
        $this->checkIfMovieCreationWasFinishedForRecording();

    }

    private function setPreprocessingStatusForFinishedRecordings()
    {
        if(Camera::noCameraIsRecording()) {
            foreach(Recording::withStatus(RecordingStatus::CREATED) as $recording) {
                $recording->setStatus(RecordingStatus::PREPARING_PREPROCESSING);
            }
        }
    }

    private function chooseRecordingFilesToBeUsedInThumbnails()
    {
        if(Camera::noCameraIsRecording()) {
            foreach(Recording::withStatus(RecordingStatus::PREPARING_PREPROCESSING) as $recording) {
                $recording->files->nth(config('taggy-recorder.video-conversion.thumbnails.nth'))
                    ->each(fn(RecordingFile $file) => $file->setStatus(RecordingFileStatus::TO_BE_THUMBNAILED));

                $recording->setStatus(RecordingStatus::THUMBNAILS_SELECTED);
            }
        }
    }

    private function createThumbnailsForRecordingFiles()
    {
        if(Camera::noCameraIsRecording()) {
            foreach(RecordingFile::withStatus(RecordingFileStatus::TO_BE_THUMBNAILED)->load('recording') as $file) {
                try {
                    app(CreateThumbnailForRecordingFile::class)
                        ->execute($file);
                }
                catch(ExecutionFailureException $exception)
                {
                    
                }
            }
        }
    }

    private function checkIfThumbnailCreationWasFinishedForRecording()
    {
        if(Camera::noCameraIsRecording()) {
            foreach(Recording::withStatus(RecordingStatus::THUMBNAILS_SELECTED) as $recording) {
                if($recording->files->where('status', RecordingFileStatus::TO_BE_THUMBNAILED)->count() === 0) {
                    $recording->setStatus(RecordingStatus::THUMBNAILS_CREATED);
                }
            }
        }
    }

    private function createZipFileWithThumbnails()
    {
        if(Camera::noCameraIsRecording()) {
            foreach(Recording::withStatus(RecordingStatus::THUMBNAILS_CREATED) as $recording) {
                $zip = new ZipArchive();
                $zip->open(storage_path("app/{$recording->rootPath()}/thumbnails.zip"), ZipArchive::CREATE | ZipArchive::OVERWRITE);

                foreach(Storage::files($recording->thumbnailPath()) as $file) {
                    $zip->addFile(Storage::disk('local')->path($file), basename($file));
                }

                $zip->close();

                $recording->setStatus(RecordingStatus::ZIP_FILE_CREATED);
            }
        }
    }

    private function createMovieWithThumbnails()
    {
        if(Camera::noCameraIsRecording()) {
            foreach(Recording::withStatus(RecordingStatus::THUMBNAILS_CREATED) as $recording) {
                $command = 'nohup sudo -u taggy ffmpeg -f image2 -r 2 -pattern_type glob -i "' . Storage::path($recording->thumbnailPath()) . '/*.jpg" -c:v libx264 -pix_fmt yuv420p -vf "pad=ceil(iw/2)*2:ceil(ih/2)*2" ' . Storage::path("{$recording->rootPath()}/thumbnails.mp4") . ' 2> /dev/null > /dev/null &';
                info($command);
                exec($command);

                $recording->setStatus(RecordingStatus::CREATING_MOVIE);
            }
        }
    }

    private function checkIfMovieCreationWasFinishedForRecording()
    {
        if(Recorder::make()->getRunningFfmpegProcesses()->count() == 0) {
            foreach(Recording::withStatus(RecordingStatus::CREATING_MOVIE) as $recording) {
                $recording->setStatus(RecordingStatus::MOVIE_CREATED);
            }
        }
    }
}
