<?php

namespace App\Http\Controllers\Api;

use App\Actions\CreateSceneVideo;
use App\Http\Controllers\Controller;
use App\Http\Resources\SceneResource;
use App\Models\Recording;
use App\Models\Scene;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SceneController extends Controller
{
    public function index()
    {
        return SceneResource::collection(Scene::all());
    }

    public function show(Scene $scene)
    {
        return SceneResource::make($scene);
    }

    public function download(Scene $scene, Recording $recording)
    {
        $filename = $scene->videoFilePath($recording);

        abort_unless(Storage::exists($scene->videoFilePath($recording, 'ready')), 404);

        return Storage::download($filename);
    }

    public function store(Request $request)
    {
        $this->validateRequest($request);

        $data = $request->only(['start_time', 'duration', 'data']);

        // As long as data is sent within "data"
        $data['uuid'] = $request->data['uuid'];
        $data['container_uuid'] = $request->data['container_id'];

        $delta = $request->is_live_tagging ? now()->diffInMilliseconds($request->system_time) : 0;
        $data['start_time'] = Carbon::parse($data['start_time'])->subMilliseconds($delta);

        $scene = Scene::create($data);

        foreach($scene->getContainingRecordings() as $recording) {
            app(CreateSceneVideo::class)
                ->onQueue()
                ->execute($scene, $recording);
        }


        // ToDo: push scene to all clients
        return SceneResource::make($scene);
    }

    public function update(Scene $scene, Request $request)
    {
        $this->validateRequest($request);

        $scene->update($request->only(['start_time', 'duration', 'data']));

        Recording::all()->each(fn(Recording $recording) => Storage::delete($scene->videoFilePath($recording)));

        // ToDo: push scene to all clients

        return SceneResource::make($scene);
    }

    public function delete(Scene $scene)
    {
        $scene->delete();
    }

    private function validateRequest(Request $request)
    {
        $request->validate([
            'is_live_tagging' => 'required|boolean',
            'system_time' => 'required|date',
            'start_time' => 'required|date',
            'duration' => 'required|integer',
        ]);
    }
}
