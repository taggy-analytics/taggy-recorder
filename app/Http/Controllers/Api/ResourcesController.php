<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RecordingResource;
use App\Models\Recording;

class ResourcesController extends Controller
{
    public function index()
    {
        return [
            'recordings' => RecordingResource::collection(Recording::all()),
        ];
    }
}
