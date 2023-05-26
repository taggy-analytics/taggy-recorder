<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SceneResource;
use App\Models\Recording;
use App\Models\Scene;
use App\Support\FFMpegCommand;
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
        $filename = 'scene-videos/' . $scene->id . '-' . $recording->id . '.m4s';

        if(!Storage::exists($filename)) {
            $command = [
                '-ss', $scene->start_time->diffInSeconds($recording->start_time),
                '-i', $recording->getPath('video/video.m3u8'),
                '-t', $scene->duration,
                Storage::path($filename),
            ];

            FFMpegCommand::runRaw(implode(' ', $command));
        }

        return Storage::download($filename);
    }

    public function store(Request $request)
    {
        $this->validateRequest($request);

        $scene = Scene::create($request->only(['start_time', 'duration', 'data']));

        // ToDo: push scene to all clients
        return SceneResource::make($scene);
    }

    public function update(Scene $scene, Request $request)
    {
        $this->validateRequest($request);

        $scene->update($request->only(['start_time', 'duration', 'data']));

        // ToDo: push scene to all clients

        return SceneResource::make($scene);
    }

    private function validateRequest(Request $request)
    {
        $request->validate([
            'start_time' => 'required|date',
            'duration' => 'required|integer',
        ]);
    }
}
