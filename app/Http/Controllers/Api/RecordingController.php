<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RecordingResource;
use App\Models\Recording;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

    public function update(Recording $recording, Request $request)
    {
        $recording->update($request->only(['data']));

        return RecordingResource::make($recording);
    }

    public function videoVod(Recording $recording)
    {
        $m3u8 = Storage::disk('public')
            ->get($recording->getPath('video/video.m3u8'));

        return $m3u8 . PHP_EOL . '#EXT-X-ENDLIST';
    }
}
