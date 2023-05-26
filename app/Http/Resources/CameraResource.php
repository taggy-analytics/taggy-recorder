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
            'identifier' => $this->identifier,
            'status' => $this->status,
            'type' => (new \ReflectionClass($this->type))->getShortName(),
            'name' => $this->name,
            'credentials' => $this->credentials,
        ];
    }
}
