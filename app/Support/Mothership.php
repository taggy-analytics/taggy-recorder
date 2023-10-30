<?php

namespace App\Support;

use App\Enums\RecordingStatus;
use App\Exceptions\MothershipException;
use App\Models\Recording;
use App\Models\RecordingFile;
use App\Models\UserToken;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Mothership
{
    private $client;
    private $headers;

    public function __construct(private ?UserToken $userToken = null)
    {
        if($userToken?->isRevoked()) {
            throw new \Exception('User token is revoked.');
        }

        $this->client = Http::baseUrl($this->getEndpoint($userToken))
            ->withUserAgent('TaggyRecorder/' . Recorder::make()->currentSoftwareVersion())
            ->acceptJson()
            ->withHeaders([
                'Recorder-Id' => Recorder::make()->getSystemId(),
            ])
            ->withToken($userToken?->token);
    }
    public static function make(UserToken $userToken = null)
    {
        return new self($userToken ?? UserToken::lastSuccessfullyUsed()->first());
    }

    public function reportRecording(Recording $recording)
    {
        try {
            return $this->post('recordings/to-video', [
                'uuid' => $recording->uuid,
                'recorderSystemId' => Recorder::make()->getSystemId(),
                'cameraId' => $recording->camera_id,
                'key' => $recording->key,
                'totalSegments' => $recording->files()->count(),
                'sessionUuid' => Arr::get($recording->data, 'session_uuid'),
                'entityId' => Arr::get($recording->data, 'entity_id'),
                'startTime' => $recording->started_at,
                'duration' => $recording->getDuration(),
            ]);
        }
        catch(MothershipException $exception) {
            return match($exception->response->status()) {
                404 => RecordingStatus::RECORDER_NOT_FOUND_ON_MOTHERSHIP,
                410 => RecordingStatus::SESSION_NOT_FOUND_ON_MOTHERSHIP,
                default => RecordingStatus::UNKNOWN_MOTHERSHIP_ERROR,
            };
        }

    }

    public function getTransactionsStatus($entityId, $hashes, $hashSubstringLength)
    {
        return $this->post('entities/' . $entityId . '/transactions/status', [
            'hashes' => $hashes,
            'hash_substring_length' => $hashSubstringLength,
        ]);
    }

    public function reportTransactions($entityId, $transactions, $lastTransactionInSync = null)
    {
        try {
            return $this->post('entities/' . $entityId . '/transactions', [
                'origin' => Recorder::make()->getSystemId(),
                'transactions' => Arr::except($transactions, 'user_token_id'),
                'last_transaction_in_sync' => $lastTransactionInSync,
            ]);
        }
        catch(MothershipException $exception) {
            return false;
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
            'playlist' => base64_encode($playlist),
        ]);
    }

    public function isOnline()
    {
        // App simulator testing
        if(cache()->has('connectedToMothership')) {
            return cache()->get('connectedToMothership');
        }

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
        $file = $this->get('recorders/' . Recorder::make()->getSystemId() . '/update/' . Recorder::make()->currentSoftwareVersion(), 'body');

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
        return $this->post('recorders/' . Recorder::make()->getSystemId() . '/log?key=' . config('taggy-recorder.mothership-logging-key'), $data, 'raw');
    }

    private function get($url, $type = 'json')
    {
        return $this->request('get', $url, type: $type);
    }

    private function post($url, $data = [], $type = 'json')
    {
        return $this->request('post', $url, $data, $type);
    }

    private function delete($url)
    {
        return $this->request('delete', $url);
    }

    private function getEndpoint(UserToken $userToken = null)
    {
        if($userToken) {
            return $userToken?->endpoint . '/api/v1';
        }

        return config('services.mothership.endpoint');
    }

    private function request($method, $url, $data = null, $type = 'json')
    {
        $response = $this->client
            ->{$method}($url, $data);

        if($response->status() >= 400) {
            if($response->status() == 401) {
                $this->userToken?->revoke();
            }
            throw new MothershipException($method, $url, $data, $response);
        }

        $this->headers = [
            'content-disposition' => $response->header('content-disposition'),
        ];

        return $type == 'json' ? $response->json() : $response->body();
    }
}
