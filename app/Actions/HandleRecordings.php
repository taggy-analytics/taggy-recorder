<?php

namespace App\Actions;

use App\Actions\Preprocessing\CreateThumbnailForRecordingFile;
use App\Enums\RecordingFileStatus;
use App\Enums\RecordingFileType;
use App\Enums\RecordingStatus;
use App\Models\Camera;
use App\Models\Recording;
use App\Support\FFMpegCommand;
use App\Support\Mothership;
use Chrisyue\PhpM3u8\Facade\ParserFacade;
use Chrisyue\PhpM3u8\Stream\TextStream;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HandleRecordings
{
    public function execute()
    {
        $this->setPreprocessingStatusForFinishedRecordings();
        // $this->chooseRecordingFilesToBeUsedInThumbnails();
        // $this->checkIfThumbnailCreationWasFinishedForRecording();
        // $this->createZipFileWithThumbnails();
        $this->createRecordingFilesInDB();
        $this->reportUnreportedRecordingsToMothership();
        // $this->createThumbnailsForRecordingFiles();
        // $this->createMovieWithThumbnails();
        // $this->checkIfMovieCreationWasFinishedForRecording();

        $this->deleteRecordings();
    }

    private function setPreprocessingStatusForFinishedRecordings()
    {
        if(Camera::noCameraIsRecording()) {
            foreach(Recording::withStatus(RecordingStatus::CREATED) as $recording) {
                $recording->setStatus(RecordingStatus::PREPARING_PREPROCESSING);
            }
        }
    }

    private function createRecordingFilesInDB()
    {
        if(Camera::noCameraIsRecording()) {
            foreach(Recording::withStatus(RecordingStatus::PREPARING_PREPROCESSING) as $recording) {
                $parser = new ParserFacade();

                $files = collect(Arr::get($parser->parse(new TextStream(Storage::disk('public')->get($recording->getPath('video/video.m3u8')))), 'mediaSegments'))
                    ->pluck('uri');

                // ToDo: mass insert for performance boost
                foreach ($files as $file) {
                    $recording->files()->firstOrCreate([
                        'name' => $file,
                        'type' => RecordingFileType::VIDEO_M4S,
                    ], [
                        'status' => RecordingFileStatus::CREATED,
                    ]);
                }
                $recording->setStatus(RecordingStatus::CREATED_RECORDING_FILES_IN_DB);
            }
        }
    }

    private function reportUnreportedRecordingsToMothership()
    {
        if(Camera::noCameraIsRecording()) {
            foreach(Recording::withStatus(RecordingStatus::CREATED_RECORDING_FILES_IN_DB) as $recording) {
                $recording->setStatus(RecordingStatus::READY_FOR_REPORTING_TO_MOTHERSHIP);
                $recording->reportToMothership();
            }
        }
    }

    /*
    private function createMovieWithThumbnails()
    {
        if(Camera::noCameraIsRecording()) {
            foreach(Recording::withStatus(RecordingStatus::THUMBNAILS_CREATED) as $recording) {
                $processId = FFMpegCommand::run(
                    Storage::disk('public')->path($recording->thumbnailsPath()) . '/video-%05d.jpg',
                    Storage::disk('public')->path($recording->thumbnailsMoviePath()),
                    '-framerate 2 -c:v libx264 -pix_fmt yuv420p -vf "pad=ceil(iw/2)*2:ceil(ih/2)*2"'
                );

                $recording->update(['process_id' => $processId]);
                $recording->setStatus(RecordingStatus::CREATING_MOVIE);
            }
        }
    }

    private function checkIfMovieCreationWasFinishedForRecording()
    {
        if(Camera::noCameraIsRecording()) {
            foreach (Recording::withStatus(RecordingStatus::CREATING_MOVIE) as $recording) {
                if (!file_exists("/proc/{$recording->process_id}")) {
                    $recording->setStatus(RecordingStatus::MOVIE_CREATED);
                }
            }
        }
    }

    private function createThumbnailsForRecordingFiles()
    {
        if(Camera::noCameraIsRecording()) {
            foreach (Recording::withStatus(RecordingStatus::CREATED_RECORDING_FILES_IN_DB) as $recording) {
                foreach($recording->files->where('status', '<>', RecordingFileStatus::THUMBNAIL_CREATED) as $file) {
                    app(CreateThumbnailForRecordingFile::class)
                        ->execute($file);
                }
                $recording->setStatus(RecordingStatus::THUMBNAILS_CREATED);
            }
        }
    }
    */

    private function deleteRecordings()
    {
        if(Camera::noCameraIsRecording()) {
            foreach (Recording::withStatus(RecordingStatus::TO_BE_DELETED) as $recording) {
                $recording->delete();
            }
        }
    }

    /*
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
    */
}
