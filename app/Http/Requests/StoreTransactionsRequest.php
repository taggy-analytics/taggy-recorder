<?php

namespace App\Http\Requests;

use Dedoc\Scramble\Support\OperationExtensions\RulesExtractor\FormRequestRulesExtractor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

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
        $rules = [
            // A requestor ID that the consumer may select.
            // It will be utilized in websocket events to allow the consumer to determine if they were the origin.
            'requestor' => 'required|string',
            'entity_id' => 'required|int',
            'transactions' => 'required|array',
            // If set to true, a cleanup process is initiated, which may take some time. This is suitable for initial synchronization requests.
            // If set to false, transactions are simply appended to the end of the transactions list, making it suitable for handling "real-time" transactions.
            'trigger_cleanup' => 'required|bool',
        ];

        if(debug_backtrace()[1]['class'] == FormRequestRulesExtractor::class) {
            // The array validation takes very long. Let's just skip it - all consumers are controlled by us.
            // We only show it in the docs.

            $rules = array_merge($rules, [
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
            ]);
        }

        return $rules;
    }
}
