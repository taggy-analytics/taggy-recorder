<?php

namespace App\Support;

use App\Exceptions\MothershipException;
use App\Http\Resources\CameraResource;
use App\Models\Camera;
use App\Models\Recording;
use App\Models\RecordingFile;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Spatie\Crypto\Rsa\PrivateKey;

class Mothership
{
    private $client;
    public const MOTHERSHIP_TOKEN_FILENAME = 'mothership-token.txt';
    public function __construct()
    {
        $this->client = Http::baseUrl(config('services.mothership.endpoint'))
            ->acceptJson()
            ->withToken(self::getToken());
    }
    public static function make()
    {
        return new self;
    }

    public function getCameras()
    {
        return $this->get('cameras');
    }

    public function reportDiscoveredCamera(Camera $camera)
    {
        return $this->post('cameras', CameraResource::make($camera));
    }

    public function reportRecording(Camera $camera, $filename, $duration, $screenshot)
    {
        return $this->post('cameras/' . $camera->identifier . '/recordings', [
            'recorder' => Recorder::make()->getMachineId(),
            'filename' => $filename,
            'duration' => $duration,
            'screenshot' => $screenshot,
        ]);
    }

    public function deleteRecording(Camera $camera, $recordingId)
    {
        return $this->delete('cameras/' . $camera->identifier . '/recordings/' . $recordingId);
    }

    public function getCameraCredentials(Camera $camera)
    {
        return $this->get('cameras/' . $camera->identifier . '/credentials');
    }

    public function reportInvalidCameraCredentials(Camera $camera)
    {
        return $this->post('cameras/' . $camera->identifier . '/invalid-credentials');
    }

    public function getUploadRecordingRequests()
    {
        try {
            return $this->get('recorders/' . Recorder::make()->getMachineId() . '/upload-requests');
        }
        catch(MothershipException $e) {
            // mothership returns 404
            return [];
        }
    }

    public function confirmRecordingUploadRequest($videoId, $totalSegments, $thumbnail, $totalVideoDuration)
    {
        return $this->post('videos/' . $videoId . '/confirm-recording-upload-request', [
            'totalSegments' => $totalSegments,
            'thumbnail' => base64_encode(Storage::get($thumbnail)),
            'duration' => $totalVideoDuration,
        ]);
    }

    public function getDeleteRecordingRequests()
    {
        return $this->get('recorders/' . Recorder::make()->getMachineId() . '/delete-requests');
    }

    public function confirmDeleteRequest(Recording $recording)
    {
        return $this->delete('cameras/' . $recording->camera->id . '/recordings/' . $recording->id);
    }

    public function sendRecordingThumbnails(Recording $recording)
    {
        $this->client->timeout(600);

        try {
            $this->post('cameras/' . $recording->camera->identifier . '/recordings', [
                'recorder' => Recorder::make()->getMachineId(),
                'thumbnails' => base64_encode(Storage::get("recordings/{$recording->id}/thumbnails.zip")),
            ]);

            return true;
        }
        catch(Exception $e) {
            throw $e;
            return false;
        }
    }

    public function sendRecordingThumbnailsMovie(Recording $recording)
    {
        $this->client->timeout(600);

        try {
            $this->post('cameras/' . $recording->camera->identifier . '/recordings/thumbnails-movie', [
                'recorder' => Recorder::make()->getMachineId(),
                'recording_id' => $recording->id,
                'movie' => base64_encode(Storage::get($recording->thumbnailsMoviePath())),
                'thumbnail' => base64_encode(Storage::get($recording->getThumbnail())),
                'start_time' => $recording->created_at,
                'duration' => $recording->getDuration(),
            ]);

            return true;
        }
        catch(Exception $e) {
            throw $e;
            return false;
        }
    }

    public function sendRecordingFile(RecordingFile $file)
    {
        $this->client->timeout(600);

        $this->post('videos/' . $file->video_id . '/segments', [
            'name' => $file->name,
            'segment' => base64_encode(Storage::get($file->videoPath())),
        ]);
    }

    public function isOnline()
    {
        try {
            return $this->checkStatus()->status() == 200;
        }
        catch(\Throwable $exception) {
            return false;
        }
    }

    public function checkStatus()
    {
        return $this->client
            ->timeout(3)
            ->get('check-reachability');
    }

    public function uploadFile($file, Camera $camera)
    {

    }

    private function get($url)
    {
        return $this->request('get', $url);
    }

    private function post($url, $data = [])
    {
        return $this->request('post', $url, $data);
    }

    private function delete($url)
    {
        return $this->request('delete', $url);
    }

    private function request($method, $url, $data = null)
    {
        $response = $this->client
            ->{$method}($url, $data);

        if($response->status() >= 400) {
            throw new MothershipException($response, $method, $url);
        }

        return $response->json();
    }

    public static function getToken()
    {
        $privateKey = PrivateKey::fromString(Recorder::make()->getPrivateKey());
        return $privateKey->decrypt(base64_decode(Storage::get(self::MOTHERSHIP_TOKEN_FILENAME)));
    }
}
