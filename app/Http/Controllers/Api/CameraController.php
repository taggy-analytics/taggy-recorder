<?php

namespace App\Http\Controllers\Api;

use App\Enums\RecordingMode;
use App\Http\Controllers\Controller;
use App\Http\Requests\StartRecordingRequest;
use App\Http\Resources\CameraResource;
use App\Http\Resources\RecordingResource;
use App\Models\Camera;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class CameraController extends Controller
{
    public function index()
    {
        return CameraResource::collection(Camera::all());
    }

    public function show(Camera $camera)
    {
        return CameraResource::make($camera);
    }

    public function update(Camera $camera, Request $request)
    {
        $request->validate([
            'recording_mode' => [new Enum(RecordingMode::class)],
            'credentials' => 'array:user,password',
        ]);

        $camera->update($request->only(['recording_mode', 'name', 'credentials']));

        return CameraResource::make($camera);
    }

    public function startRecording(Camera $camera, StartRecordingRequest $request)
    {
        if($camera->isRecording()) {
            abort(409, 'Camera is already recording.');
        }

        return RecordingResource::make($camera->startRecording($request->data));
    }

    public function stopRecording(Camera $camera)
    {
        if(!$camera->isRecording()) {
            abort(409, 'Camera is not recording.');
        }
        $recording = $camera->stopRecording();

        if(!$recording) {
            abort(422, 'Recording could not be stopped.');
        }

        return RecordingResource::make($recording);
    }

    public function currentRecording(Camera $camera)
    {
        if(!$camera->isRecording()) {
            return null;
        }

        return RecordingResource::make($camera->recordings()->latest()->first());
    }
}
