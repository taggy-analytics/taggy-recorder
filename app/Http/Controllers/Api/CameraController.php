<?php

namespace App\Http\Controllers\Api;

use App\Enums\CameraStatus;
use App\Http\Controllers\Controller;
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
        info($request->only(['status', 'name', 'credentials']));

        $request->validate([
            'status' => [new Enum(CameraStatus::class)],
            'credentials' => 'array:user,password',
        ]);

        $camera->update($request->only(['status', 'name', 'credentials']));

        return CameraResource::make($camera);
    }

    public function startRecording(Camera $camera)
    {
        if($camera->isRecording()) {
            abort(409, 'Camera is already recording.');
        }

        return RecordingResource::make($camera->startRecording());
    }

    public function stopRecording(Camera $camera)
    {
        if(!$camera->isRecording()) {
            abort(409, 'Camera is not recording.');
        }

        return RecordingResource::make($camera->stopRecording());
    }
}
