<?php

namespace App\Http\Controllers\Api;

use App\Actions\CreateSceneVideo;
use App\Http\Controllers\Controller;
use App\Http\Resources\RecordingResource;
use App\Models\Recording;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RecordingController extends Controller
{
    public function index()
    {
        return RecordingResource::collection(Recording::all());
    }

    public function show(Recording $recording)
    {
        return RecordingResource::make($recording);
    }

    public function downloadScene(Recording $recording, $key, $startTime, $duration, $name)
    {
        $sceneFilename = $recording->sceneFilename($startTime, $duration);

        Cache::lock($sceneFilename, 10)->block(30, function () use ($sceneFilename, $recording, $startTime, $duration) {
            if(!Storage::exists('scenes/' . $sceneFilename)) {
                app(CreateSceneVideo::class)->execute($recording, $startTime, $duration);
            }
        });

        return response()->download(Storage::path('scenes/' . $sceneFilename), $name);
    }

    public function videoVod(Recording $recording, $key)
    {
        $m3u8 = Storage::disk('public')
            ->get($recording->getM3u8Path());

        $appendix = Str::contains($m3u8, '#EXT-X-ENDLIST') ? '' : '#EXT-X-ENDLIST';

        return $m3u8 . $appendix;
    }
}
