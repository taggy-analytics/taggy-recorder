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
        $filename = $scene->videoFilePath($recording);

        if(!Storage::exists($filename)) {
            Storage::makeDirectory(dirname($filename));

            // FFmpeg doesn't like it if live HLS streams' m3u8s are used. So let's copy it first.
            $m3u8Path = $recording->getPath('video/video-' . $scene->id . '.m3u8');
            Storage::disk('public')
                ->copy($recording->getPath('video/video.m3u8'), $m3u8Path);

            $command = [
                '-ss', self::convertSeconds($scene->start_time->diffInSeconds($recording->started_at)),
                '-i', Storage::disk('public')->path($m3u8Path),
                '-t', self::convertSeconds($scene->duration),
                '-c', 'copy',
                '-f', 'mp4',
                Storage::path($filename),
            ];

            FFMpegCommand::runRaw(implode(' ', $command));

            while(!Storage::exists($filename)) {
                usleep(100000);
            }

            Storage::disk('public')->delete($m3u8Path);
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
            'start_time' => 'required|date',
            'duration' => 'required|integer',
        ]);
    }

    public static function convertSeconds($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds / 60) % 60);
        $seconds = $seconds % 60;
        $milliseconds = floor(($seconds - floor($seconds)) * 1000);

        return sprintf('%02d:%02d:%02d.%03d', $hours, $minutes, floor($seconds), $milliseconds);
    }
}
