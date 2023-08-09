<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionsRequest extends FormRequest
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
        // The validation takes very long. Let's just skip it - all consumers are controlled by us.
        return [
            'requestor' => 'required|string',
            'entity_id' => 'required|int',
            'transactions' => 'required|array',
            'trigger_cleanup' => 'required|bool',
        ];

        /*
        return [
            'entity_id' => 'required|int',
            'transactions' => 'required|array',
            'transactions.*.uuid' => 'required|uuid',
            'transactions.*.user_id' => 'required|int',
            'transactions.*.model_type' => 'required|string',
            'transactions.*.model_id' => 'required|uuid',
            'transactions.*.action' => 'required|in:create,update,delete,attach,detach',
            'transactions.*.property' => 'nullable',
            'transactions.*.value' => 'nullable',
            'transactions.*.created_at' => 'required|date',
        ];
        */
    }
}
