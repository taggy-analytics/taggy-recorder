<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RecordingResource;
use App\Models\Recording;
use Illuminate\Http\Request;

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
}
