<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionsStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            // Hashes are posted in the format: [position => crc32 hash of all transaction UUIDs up to this position]
            // @example {"2030": 363340319, "3634": 716678960}
            'hashs' => 'array',
            'hashs.*' => 'required|integer',
            // It is sufficiently safe to use only parts of the UUID, rather than the whole, for the calculation of the hash.
            // This parameter defines the number of characters from the UUID that are to be used for the calculation.
            'hash_substring_length' => 'required|int',
        ];
    }
}
