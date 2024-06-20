<?php

namespace App\Support;

use App\Actions\UpdateSoftware;
use App\Enums\RecordingFileStatus;
use App\Enums\RecordingStatus;
use App\Exceptions\MothershipException;
use App\Models\Recording;
use App\Models\RecordingFile;
use App\Models\UserToken;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\LaravelIgnition\Facades\Flare;

class Mothership
{
    private $client;
    private $headers;

    private const LAST_RESPONSE_STATUS_CACHE_KEY = 'lastMothershipResponseStatus';

    public function __construct(private ?UserToken $userToken = null, $endpoint = null)
    {
        if($userToken?->isRevoked()) {
            throw new \Exception('User token is revoked.');
        }

        $this->client = Http::baseUrl(($endpoint ?? self::getEndpoint($userToken)) . '/api/v1')
            ->withUserAgent('TaggyRecorder/' . Recorder::make()->currentSoftwareVersion())
            ->acceptJson()
            ->withHeaders([
                'Recorder-Id' => Recorder::make()->getSystemId(),
            ])
            ->withToken($userToken?->token);
    }
    public static function make(UserToken $userToken = null, $endpoint = null)
    {
        return new self($userToken ?? UserToken::lastSuccessfullyUsed()->first(), $endpoint);
    }

    public function lastResponseStatus()
    {
        return blink()->get(self::LAST_RESPONSE_STATUS_CACHE_KEY);
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
                'rotation' => $recording->rotation,
                'width' => $recording->width,
                'height' => $recording->height,
                'streamingProtocol' => $recording->getStreamingProtocol(),
                'codec' => $recording->getCodec(),
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
            'debug' => config('app.debug'),
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
            Flare::context('reportTransactionsException', $exception);
            return false;
        }
    }

    public function sendLivestreamFile(Recording $recording, $file, $content = null, $m3u8Content = null)
    {
        $this->client->timeout(600);

        $this->post('recordings/' . $recording->key . '/livestream-segments', [
            'name' => basename($file),
            'content' => $content ?? base64_encode(File::get($file)),
            'm3u8Content' => $m3u8Content,
            'rotation' => $recording->rotation,
            'width' => $recording->width,
            'height' => $recording->height,
            'streamingProtocol' => $recording->getStreamingProtocol(),
            'codec' => $recording->getCodec(),
            'session_uuid' => Arr::get($recording->data, 'session_uuid'),
        ]);
    }

    public function sendRecordingFile(RecordingFile $file)
    {
        $this->client->timeout(600);

        $this->post('videos/' . $file->video_id . '/video-segments', [
            'name' => $file->name,
            'segment' => base64_encode(Storage::disk('public')->get($file->videoPath())),
        ]);
    }

    /*
    public function sendMetaFiles($videoId, $videoM3u8, $initMp4)
    {
        $this->post('videos/' . $videoId . '/files', [
            'files' => [
                'video.m3u8' => base64_encode($videoM3u8),
                'init.mp4' => base64_encode($initMp4),
            ],
        ]);
    }
    */

    public function isOnline($timeout = 3, $disableCache = false)
    {
        // App simulator testing
        if(cache()->has('connectedToMothership')) {
            return cache()->get('connectedToMothership');
        }

        return blink()->once('isOnline' . ($disableCache ? Str::random() : ''), function() use ($timeout) {
            try {
                return $this->checkStatus($timeout)->status() == 200;
            }
            catch(\Throwable $exception) {
                return false;
            }
        });
    }

    public function checkStatus($timeout = 3)
    {
        return $this->client
            ->timeout($timeout)
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

    public function sendTemperatureLog($data)
    {
        return $this->post('recorders/' . Recorder::make()->getSystemId() . '/temperature?key=' . config('taggy-recorder.mothership-logging-key'), ['measurements' => $data]);
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

    public static function getEndpoint(UserToken $userToken = null)
    {
        if($userToken) {
            return $userToken?->endpoint;
        }

        $defaultEndpoint = config('services.mothership.' . config('app.env') . '.endpoint');
        return Arr::get(request()->environmentData(), 'urls.mothership', $defaultEndpoint);
    }

    private function request($method, $url, $data = null, $type = 'json')
    {
        $response = $this->client
            ->{$method}($url, $data);

        blink()->put(self::LAST_RESPONSE_STATUS_CACHE_KEY, $response->status());

        if($response->status() >= 400) {
            switch($response->status()) {
                case 401:
                    $this->userToken?->revoke();
                    throw new MothershipException($method, $url, $data, $response);
                case 420:
                    app(UpdateSoftware::class)->execute();
                    $response = $this->client
                        ->{$method}($url, $data);

                    blink()->put(self::LAST_RESPONSE_STATUS_CACHE_KEY, $response->status());
                    break;
                default:
                    throw new MothershipException($method, $url, $data, $response);
            }
        }

        $this->headers = [
            'content-disposition' => $response->header('content-disposition'),
        ];

        return $type == 'json' ? $response->json() : $response->body();
    }
}
