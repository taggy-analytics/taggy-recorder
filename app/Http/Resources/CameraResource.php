<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class CameraResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'identifier' => $this->identifier,
            'status' => $this->status,
            'type' => Arr::last(explode('\\', $this->type)),
            'name' => $this->name,
            'credentials' => $this->credentials,
            'recording_mode' => $this->recording_mode,
            'rotation' => $this->rotation,
            'is_recording' => $this->isRecording(),
            'streams' => $this->getStreams(),
            'video_width' => $this->video_width,
            'video_height' => $this->video_height,
        ];
    }
}
