<?php

namespace App\Actions\Mothership;

use App\Models\Camera;
use App\Support\Recorder;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class HandleRecordingsWithMothership extends MothershipAction
{
    protected function executeAction()
    {
        /*
        $this->reportFinishedRecordings();
        $this->checkRecordingsToUpload();
        $this->checkRecordingsToDelete();
        */
    }

    private function checkRecordingsToUpload()
    {
        foreach($this->mothership->getUploadRecordingRequests() as $uploadRequest) {
            try {
                $endpointBaseUri = Str::replaceLast('/mothership', '', config('services.mothership.endpoint'));

                $client = new \TusPhp\Tus\Client($endpointBaseUri);
                $client->setApiPath('sessions/upload');

                $camera = Camera::firstWhere('identifier', $uploadRequest['cameraIdentifier']);

                $filename = \Storage::path('cameras/' . $camera->id . '/uploadable/' . $uploadRequest['filename']);

                $key = md5(Recorder::make()->getMachineId() . '-' . $filename);

                $client
                    ->setKey($key)
                    ->addMetadata('session', $uploadRequest['sessionEncrypted'])
                    ->addMetadata('type', 'recorder')
                    ->file($filename)
                    ->upload();

                File::delete($filename);

                $this->mothership->deleteRecording($camera, $uploadRequest['id']);
            }
            catch(\Exception $e) {
                dump($e);
            }
        }
    }

    private function checkRecordingsToDelete()
    {
        foreach($this->mothership->getDeleteRecordingRequests() as $deleteRequest) {
            $camera = Camera::firstWhere('identifier', $deleteRequest['cameraIdentifier']);
            $filename = \Storage::path('cameras/' . $camera->id . '/uploadable/' . $deleteRequest['filename']);
            if(File::exists($filename)) {
                File::delete($filename);
            }
            $this->mothership->deleteRecording($camera, $deleteRequest['id']);
        }
    }

    private function reportFinishedRecordings()
    {
        collect(glob(storage_path('app/*/*/recordings/*')))
            ->filter(fn($file) => Carbon::parse(File::lastModified($file))->lt(now()->subSeconds(30)))
            ->each(function ($file) {
                $cameraId = collect(explode('/', $file))->reverse()->values()->get(2);
                $relativeFilename = Str::replaceFirst(storage_path('app'), '', $file);
                $uploadFilename = Str::replaceFirst('/recordings/', '/uploadable/', $file);
                $baseFilename = File::basename($file);

                $screenshot = FFMpeg::open($relativeFilename)
                    ->getFrameFromSeconds(2)
                    ->export()
                    ->getFrameContents();

                $duration = FFMpeg::open($relativeFilename)->getDurationInMiliseconds() / 1000;

                $directory = Str::replaceLast('/' . $baseFilename, '', $uploadFilename);
                if(!File::exists($directory)) {
                    File::makeDirectory($directory, recursive: true);
                }

                File::move($file, $uploadFilename);

                $this->mothership->reportRecording(Camera::find($cameraId), $baseFilename, $duration, base64_encode($screenshot));
            });
    }
}
