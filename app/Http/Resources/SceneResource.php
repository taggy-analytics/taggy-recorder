<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SceneResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'startTime' => $this->start_time,
            'duration' => $this->duration,
            'data' => $this->data,
            'recordings' => RecordingResource::collection($this->getContainingRecordings()),
        ];
    }
}
