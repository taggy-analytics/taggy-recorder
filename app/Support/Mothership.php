<?php

namespace App\Support;

use App\Exceptions\MothershipException;
use App\Http\Resources\CameraResource;
use App\Models\Camera;
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

    public function getDeleteRecordingRequests()
    {
        try {
            return $this->get('recorders/' . Recorder::make()->getMachineId() . '/delete-requests');
        }
        catch(MothershipException $e) {
            // mothership returns 404
            return [];
        }
    }

    public function checkStatus()
    {
        return $this->client
            ->timeout(3)
            ->get('check-reachability');
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
