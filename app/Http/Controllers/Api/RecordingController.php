<?php

namespace App\Http\Controllers\Api;

use App\Actions\CreateSceneVideo;
use App\Http\Controllers\Controller;
use App\Http\Resources\RecordingResource;
use App\Models\Recording;
use App\Support\Mothership;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RecordingController extends Controller
{
    public function index()
    {
        return RecordingResource::collection($this->getAllowedRecordings());
    }

    public function show(Recording $recording)
    {
        $this->authorizeRecording($recording);

        return RecordingResource::make($recording);
    }

    public function downloadScene(Recording $recording, $key, $startTime, $duration, $name)
    {
        // ToDo: delete (also route and CreateSceneVideo - maybe keep the latter)
        $sceneFilename = $recording->sceneFilename($startTime, $duration);

        Cache::lock($sceneFilename, 10)->block(30, function () use ($sceneFilename, $recording, $startTime, $duration) {
            if (! Storage::exists('scenes/'.$sceneFilename)) {
                app(CreateSceneVideo::class)->execute($recording, $startTime, $duration);
            }
        });

        return response()->download(Storage::path('scenes/'.$sceneFilename), $name);
    }

    public function videoVod(Recording $recording, $key)
    {
        $m3u8 = Storage::disk('public')
            ->get($recording->getM3u8Path());

        $appendix = Str::contains($m3u8, '#EXT-X-ENDLIST') ? '' : '#EXT-X-ENDLIST';

        return $m3u8.$appendix;
    }

    private function authorizeRecording(Recording $recording)
    {
        if (! $this->getAllowedRecordings()->contains($recording)) {
            abort(403);
        }
    }

    private function getAllowedRecordings()
    {
        // ToDo: Fix permissions
        return Recording::all();

        $allowedEntityIds = collect(auth()->user()->canTagMemberships);

        return Recording::all()
            ->filter(function (Recording $recording) use ($allowedEntityIds) {
                return $allowedEntityIds->contains($recording->getData('entity_id'))
                    && Mothership::getEndpoint() == $recording->getData('endpoint');
            });
    }
}
