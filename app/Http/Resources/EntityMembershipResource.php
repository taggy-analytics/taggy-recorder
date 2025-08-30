<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EntityMembershipResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'entity' => new EntityResource($this->entity),
            'name' => $this->name,
            'shortname' => $this->shortname,
            'can_tag' => $this->can_tag,
            'can_view_session_events' => $this->can_view_session_events,
            'is_player' => $this->is_player,
            'is_admin' => $this->is_admin,
            'is_banned' => $this->is_banned,
        ];
    }
}
