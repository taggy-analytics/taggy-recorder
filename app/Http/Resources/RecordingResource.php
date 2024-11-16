<?php

namespace App\Http\Resources;

use App\Enums\RecordingStatus;
use App\Enums\RecordingType;
use Illuminate\Http\Resources\Json\JsonResource;

class RecordingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'type' => is_null($this->stopped_at) ? RecordingType::LIVE->value : RecordingType::FINISHED->value,
            'start_time' => $this->started_at,
            'duration' => $this->getDuration(),
            'width' => $this->width,
            'height' => $this->height,
            'rotation' => $this->rotation,
            'data' => $this->data,
            'url' => $this->getUrl(),
            'url_vod' => route('recording.video-vod', ['recording' => $this->resource->id, 'key' => $this->resource->key]),
            'camera' => CameraResource::make($this->camera),
            'aborted_at' => $this->aborted_at,
            'thumbnail' => route('image', 'recording.png'),
            'latency' => $this->resource->getLatency(),
            'video_format' => $this->video_format,
        ];
    }
}
