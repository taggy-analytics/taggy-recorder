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
            'type' => $this->status == RecordingStatus::CREATED ? 'live' : 'finished',
            'startTime' => $this->created_at,
            'url' => $this->getUrl(),
            'camera' => CameraResource::make($this->camera),
        ];
    }
}
