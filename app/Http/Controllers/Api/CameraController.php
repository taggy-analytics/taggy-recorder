<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CameraResource;
use App\Models\Camera;
use App\Support\Recorder;

class CameraController extends Controller
{
    public function index()
    {
        return CameraResource::collection(Camera::all());
    }
}
