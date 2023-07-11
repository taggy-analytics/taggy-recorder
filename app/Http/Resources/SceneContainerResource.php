<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SceneContainerResource extends JsonResource
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
            'entity_id' => $this->entity_id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'start_time' => $this->start_time,
            'type' => $this->type,
            'sub_type' => $this->sub_type,
        ];
    }
}
