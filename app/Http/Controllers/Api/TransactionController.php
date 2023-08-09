<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransactionsStatusRequest;
use App\Models\ModelTransaction;

class TransactionController extends Controller
{
    public function status(TransactionsStatusRequest $request)
    {
        $uuids = ModelTransaction::query()
            ->where('entity_id', $request->entityId)
            ->pluck('uuid');

        $uuidsConcatenated = $uuids
            ->map(fn($uuid) => substr($uuid, 0, $request->hash_substring_length))
            ->implode('');

        $lastUuidInSync = null;

        foreach($request->hashs as $index => $hash) {
            if($hash !== crc32(substr($uuidsConcatenated, 0, $request->hash_substring_length * ($index + 1)))) {
                return [
                    'transactions_in_sync' => false,
                    'last_transaction_in_sync' => filled($lastUuidInSync) ? $uuids[$lastUuidInSync] : null,
                ];
            }

            $lastUuidInSync = $index;
        }

        return [
            'transactions_in_sync' => true,
        ];
    }
}
