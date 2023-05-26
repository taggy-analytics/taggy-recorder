<?php

namespace App\Http\Resources;

use App\Models\Recording;
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
            'videos' => $this->getContainingRecordings()->map(fn (Recording $recording) => [
                'url' => route('scenes.download', [$this->resource, $recording]),
                'recording' => RecordingResource::make($recording),
            ])->values(),
        ];
    }
}
