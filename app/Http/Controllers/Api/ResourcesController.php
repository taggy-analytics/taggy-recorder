<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CameraResource;
use App\Http\Resources\RecordingResource;
use App\Models\Camera;
use App\Models\Recording;
use App\Support\Recorder;

class ResourcesController extends Controller
{
    public function index()
    {
        return [
            'recordings' => RecordingResource::collection(Recording::all()),
        ];
    }
}
