<?php

namespace App\Http\Requests;

use App\Enums\SceneContainerType;
use App\Enums\SessionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreSceneContainerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'entity_id' => 'required|integer',
            'name' => 'required|string',
            'uuid' => 'required|uuid|unique:scene_containers,uuid',
            'start_time' => 'required|date',
            'type' => ['required', new Enum(SceneContainerType::class)],
            'sub_type' => ['required', new Enum(SessionType::class)],
        ];
    }
}
