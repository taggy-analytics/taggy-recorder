<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTokensRequest extends FormRequest
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
            'user_id' => 'required|int',
            'token' => 'required|string',
            'entities' => 'required|array',
            'entities.*.id' => 'required|int',
            // The date/time the token was last used successfully with the Taggy API
            'entities.*.last_used_successfully_at' => 'date',
        ];
    }
}
