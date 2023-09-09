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
            'uuid' => $this->uuid,
            'type' => is_null($this->stopped_at) ? 'live' : 'finished',
            'start_time' => $this->started_at,
            'duration' => $this->getDuration(),
            'data' => $this->data,
            'url' => $this->getUrl(),
            'url_vod' => route('recording.video-vod', ['recording' => $this->resource->id, 'key' => $this->resource->key]),
            'camera' => CameraResource::make($this->camera),
            'aborted_at' => $this->aborted_at,
        ];
    }
}
