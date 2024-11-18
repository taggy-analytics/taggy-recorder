<?php

namespace App\Actions;

use App\Enums\RecordingFileStatus;
use App\Enums\RecordingFileType;
use App\Enums\RecordingStatus;
use App\Models\Camera;
use App\Models\MothershipReport;
use App\Models\Recording;
use App\Models\RecordingFile;
use App\Models\UserToken;
use Chrisyue\PhpM3u8\Facade\ParserFacade;
use Chrisyue\PhpM3u8\Stream\TextStream;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HandleRecordings
{
    public function execute()
    {
        $this->setPreprocessingStatusForFinishedRecordings();
        $this->createRecordingFilesInDB();
        $this->reportUnreportedRecordingsToMothership();
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
                $recording->files()->delete();
                $parser = new ParserFacade();

                $currentTime = now()->toDateTimeString();

                $files = collect(Arr::get($parser->parse(new TextStream(Storage::disk('public')->get($recording->getM3u8Path()))), 'mediaSegments'))
                    ->pluck('uri')
                    ->map(fn($file) => [
                        'recording_id' => $recording->id,
                        'name' => $file,
                        'type' => RecordingFileType::VIDEO_M4S,
                        'status' => RecordingFileStatus::CREATED,
                        'updated_at' => $currentTime,
                        'created_at' => $currentTime,
                    ])->toArray();

                /*
                $files[] = [
                    'recording_id' => $recording->id,
                    'name' => 'init.mp4',
                    'type' => RecordingFileType::PLAYLIST,
                    'status' => RecordingFileStatus::CREATED,
                    'updated_at' => $currentTime,
                    'created_at' => $currentTime,
                ];
                */

                $files[] = [
                    'recording_id' => $recording->id,
                    'name' => 'video.m3u8',
                    'type' => RecordingFileType::PLAYLIST,
                    'status' => RecordingFileStatus::CREATED,
                    'updated_at' => $currentTime,
                    'created_at' => $currentTime,
                ];

                /*
                $files[] = [
                    'recording_id' => $recording->id,
                    'name' => 'playlist.m3u8',
                    'type' => RecordingFileType::PLAYLIST,
                    'status' => RecordingFileStatus::CREATED,
                    'updated_at' => $currentTime,
                    'created_at' => $currentTime,
                ];
                */

                RecordingFile::insertChunked($files);

                $mothershipReports = $recording->load('files')
                    ->files()
                    ->pluck('id')
                    ->map(fn($fileId) => [
                        'model_type' => RecordingFile::class,
                        'model_id' => $fileId,
                        'updated_at' => $currentTime,
                        'created_at' => $currentTime,
                    ])->toArray();

                MothershipReport::insertChunked($mothershipReports);

                DB::table('livestream_segments')
                    ->where('file', 'LIKE', '%' . $recording->key . '%')
                    ->delete();

                $recording->setStatus(RecordingStatus::CREATED_RECORDING_FILES_IN_DB);
            }
        }
    }

    private function reportUnreportedRecordingsToMothership()
    {
        if(Camera::noCameraIsRecording()) {
            foreach(Recording::withStatus(RecordingStatus::CREATED_RECORDING_FILES_IN_DB) as $recording) {
                $userToken = UserToken::forEndpointAndEntity($recording->data['endpoint'], $recording->data['entity_id']);
                $recording->reportToMothership($userToken);
                $recording->setStatus(RecordingStatus::READY_FOR_REPORTING_TO_MOTHERSHIP);
            }
        }
    }

    private function deleteRecordings()
    {
        if(Camera::noCameraIsRecording()) {
            foreach (Recording::withStatus(RecordingStatus::TO_BE_DELETED) as $recording) {
                $recording->delete();
            }
        }
    }
}
