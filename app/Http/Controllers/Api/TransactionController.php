<?php

namespace App\Http\Controllers\Api;

use App\Actions\CleanTransactions;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransactionsRequest;
use App\Http\Requests\TransactionsStatusRequest;
use App\Http\Resources\ModelTransactionResource;
use App\Models\ModelTransaction;
use App\Models\UserToken;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class TransactionController extends Controller
{
    public function status(TransactionsStatusRequest $request)
    {
        $uuids = $this->getUuids($request->entity_id);

        $uuidsConcatenated = $uuids
            ->map(fn($uuid) => substr($uuid, 0, $request->hash_substring_length))
            ->implode('');

        $lastUuidInSync = -1;

        foreach($request->hashs as $index => $hash) {
            $offset = $request->hash_substring_length * ($lastUuidInSync + 1);
            $length = $request->hash_substring_length * ($index - $lastUuidInSync);

            if($hash !== crc32(substr($uuidsConcatenated, $offset, $length))) {
                return [
                    'transactions_in_sync' => false,
                    'last_transaction_in_sync' => $lastUuidInSync > 0 ? $uuids[$lastUuidInSync] : null,
                ];
            }

            $lastUuidInSync = $index;
        }

        return [
            'transactions_in_sync' => true,
        ];
    }

    public function store(StoreTransactionsRequest $request)
    {
        $token = cache()->get('user-token');
        $userToken = UserToken::firstOrCreate([
            'user_id' => explode('|', $token)[0],
        ], [
            'token' => $token,
        ]);

        // ToDo: selbst entscheiden, ob Cleanup nötig ist

        if($this->cleanupNeeded($request->transactions)) {
            $newTransactions = collect($request->transactions)
                ->whereNotIn('uuid', $this->getUuids($request->entity_id))
                ->map(function ($transaction) use ($userToken) {
                    $transaction['user_token_id'] = $userToken->id;
                    $transaction['value'] = json_encode($transaction['value']);
                    $transaction['created_at'] = Carbon::parse($transaction['created_at'])
                        ->toDateTimeString('milliseconds');
                    return $transaction;
                })
                ->toArray();

            ModelTransaction::insert($newTransactions);

            $transactions = app(CleanTransactions::class)
                ->execute($request->entity_id);

            //dispatch()
        }
        else {
            $transactions = [];
            foreach($request->transactions as $transaction) {
                $transaction['user_token_id'] = $userToken->id;
                $transactions[] = ModelTransaction::create($transaction);
            }

            $transactions = null;
        }

        return [
            'transactions' => ModelTransactionResource::collection($transactions),
        ];
    }

    private function getUuids($entity)
    {
        return ModelTransaction::query()
            ->where('entity_id', $entity)
            ->pluck('uuid');
    }

    private function cleanupNeeded($transactions)
    {
        // Check if the youngest existing transaction is older than the oldest new transaction
        $existingTransactionsDate = ModelTransaction::where('entity_id', Arr::first($transactions)['entity_id'])
            ->max('created_at');

        $newTransactionsDate = collect($transactions)->min('created_at');

        return $existingTransactionsDate > $newTransactionsDate;
    }
}
