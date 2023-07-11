<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSceneContainerRequest;
use App\Http\Resources\SceneContainerResource;
use App\Models\SceneContainer;

class SceneContainerController extends Controller
{
    public function index()
    {
        return SceneContainerResource::collection(SceneContainer::all());
    }

    public function store(StoreSceneContainerRequest $request)
    {
        $sceneContainer = SceneContainer::create($request->only('entity_id', 'name', 'uuid', 'start_time', 'type', 'sub_type'));
        return SceneContainerResource::make($sceneContainer);
    }

    public function show(SceneContainer $sceneContainer)
    {
        return SceneContainerResource::make($sceneContainer);
    }
}
