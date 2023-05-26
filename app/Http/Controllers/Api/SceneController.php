<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SceneResource;
use App\Models\Scene;
use Illuminate\Http\Request;

class SceneController extends Controller
{
    public function index()
    {
        return SceneResource::collection(Scene::all());
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
