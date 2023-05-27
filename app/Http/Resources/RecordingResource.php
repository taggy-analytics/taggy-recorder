<?php

namespace App\Http\Resources;

use App\Enums\RecordingStatus;
use Illuminate\Http\Resources\Json\JsonResource;

class RecordingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type' => is_null($this->stopped_at) ? 'live' : 'finished',
            'start_time' => $this->started_at,
            'duration' => $this->getDuration(),
            'url' => $this->getUrl(),
            'camera' => CameraResource::make($this->camera),
        ];
    }
}
