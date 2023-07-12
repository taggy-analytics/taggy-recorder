<?php

namespace App\Support;

use App\Exceptions\MothershipException;
use App\Exceptions\RecorderNotAssociatedException;
use App\Http\Resources\CameraResource;
use App\Models\Camera;
use App\Models\Recording;
use App\Models\RecordingFile;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Crypto\Rsa\Exceptions\CouldNotDecryptData;
use Spatie\Crypto\Rsa\PrivateKey;

class Mothership
{
    private $client;
    private $headers;

    public const MOTHERSHIP_TOKEN_FILENAME = 'mothership-token.txt';
    public const CURRENT_SOFTWARE_VERSION_FILENAME = 'software-version.txt';
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

    public function currentRecorder()
    {
        try {
            return $this->get('recorders/' . Recorder::make()->getSystemId());
        }
        catch(MothershipException $exception) {
            return null;
        }
    }

    public function reportDiscoveredCamera(Camera $camera)
    {
        return $this->post('cameras', CameraResource::make($camera));
    }

    /*
    public function reportRecording(Camera $camera, $filename, $duration, $screenshot)
    {
        return $this->post('cameras/' . $camera->identifier . '/recordings', [
            'recorder' => Recorder::make()->getSystemId(),
            'filename' => $filename,
            'duration' => $duration,
            'screenshot' => $screenshot,
        ]);
    }
    */

    public function reportRecording(Recording $recording)
    {
        return $this->post('recorders/' . Recorder::make()->getSystemId() . '/recording-to-video', [
            'uuid' => $recording->uuid,
            'cameraId' => $recording->camera_id,
            'key' => $recording->key,
            'totalSegments' => $recording->files()->count(),
            'sceneContainer' => $recording->data['assigned_container'],
            'startTime' => $recording->started_at,
            'duration' => $recording->getDuration(),
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
            return $this->get('recorders/' . Recorder::make()->getSystemId() . '/upload-requests');
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
        return $this->get('recorders/' . Recorder::make()->getSystemId() . '/delete-requests');
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
                'recorder' => Recorder::make()->getSystemId(),
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
                'recorder' => Recorder::make()->getSystemId(),
                'recording_id' => $recording->id,
                'movie' => base64_encode(Storage::disk('public')->get($recording->thumbnailsMoviePath())),
                'thumbnail' => $recording->getThumbnail() ? base64_encode(Storage::disk('public')->get($recording->getThumbnail())) : null,
                'start_time' => $recording->started_at,
                'duration' => $recording->getDuration(),
            ]);

            return true;
        }
        catch(Exception $e) {
            throw $e;
        }
    }

    public function sendRecordingFile(RecordingFile $file)
    {
        $this->client->timeout(600);

        $this->post('videos/' . $file->video_id . '/segments', [
            'name' => $file->name,
            'segment' => base64_encode(Storage::disk('public')->get($file->videoPath())),
        ]);
    }

    public function sendPlaylist($videoId, $playlist)
    {
        $this->post('videos/' . $videoId . '/playlist', [
            'segment' => base64_encode($playlist),
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

    public function checkForUpdateFile()
    {
        $file = $this->get('recorders/' . Recorder::make()->getSystemId() . '/update/' . $this->currentSoftwareVersion(), 'body');

        if($file) {
            $filename = trim(explode('=', $this->headers[ "content-disposition"])[1]);
            Storage::put('releases/' . $filename, $file);

            return [
                'version' => Str::replaceLast('.zip', '', $filename),
                'filename' => $filename,
            ];
        }
    }

    public function log($data)
    {
        return $this->post('recorders/' . Recorder::make()->getSystemId() . '/log?key=' . config('taggy-recorder.mothership-logging-key'), $data);
    }

    public function currentSoftwareVersion()
    {
        return Storage::get(self::CURRENT_SOFTWARE_VERSION_FILENAME);
    }

    private function get($url, $type = 'json')
    {
        return $this->request('get', $url, type: $type);
    }

    private function post($url, $data = [])
    {
        return $this->request('post', $url, $data);
    }

    private function delete($url)
    {
        return $this->request('delete', $url);
    }

    private function request($method, $url, $data = null, $type = 'json')
    {
        $response = $this->client
            ->{$method}($url, $data);

        if($response->status() >= 400) {
            throw new MothershipException($response, $method, $url);
        }

        $this->headers = [
            'content-disposition' => $response->header('content-disposition'),
        ];

        return $type == 'json' ? $response->json() : $response->body();
    }

    public static function getToken()
    {
        $privateKey = PrivateKey::fromString(Recorder::make()->getPrivateKey());

        if(!Storage::has(self::MOTHERSHIP_TOKEN_FILENAME)) {
            // ToDo: not really nice. See https://trello.com/c/QkQq9zXD
            $getTokenResponse = Http::baseUrl(config('services.mothership.endpoint'))
                ->acceptJson()
                ->get('recorders/' . Recorder::make()->getSystemId() . '/token');

            if($getTokenResponse->status() == 422) {
                throw new RecorderNotAssociatedException();
            }

            $token = $getTokenResponse->json('token');

            // try if token can be decrypted (throws CouldNotDecryptData exception)
            $privateKey->decrypt(base64_decode($token));

            Storage::put(self::MOTHERSHIP_TOKEN_FILENAME, $token);
        }

        return $privateKey->decrypt(base64_decode(Storage::get(self::MOTHERSHIP_TOKEN_FILENAME)));
    }
}
