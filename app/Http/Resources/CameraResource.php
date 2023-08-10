<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

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
            'type' => $this->getType(),
            'name' => $this->name,
            'credentials' => $this->credentials,
            'recording_mode' => $this->recording_mode,
            'is_recording' => $this->isRecording(),
            'streams' => $this->getStreams(),
        ];
    }
}
