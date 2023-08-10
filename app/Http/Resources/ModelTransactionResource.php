<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ModelTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'entity_id' => $this->entity_id,
            'user_id' => $this->user_id,
            'model_type' => $this->model_type,
            'model_id' => $this->model_id,
            'action' => $this->action,
            'property' => $this->property,
            // @var json
            'value' => $this->value,
            'created_at' => $this->created_at,
        ];
    }
}
