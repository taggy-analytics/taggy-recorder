<?php

namespace App\Http\Controllers\Api;

use App\Actions\CleanTransactions;
use App\Events\TransactionsAdded;
use App\Events\TransactionsRecalculated;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransactionsRequest;
use App\Http\Requests\TransactionsStatusRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
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

        if(empty($request->transactions)) {
            return [
                'transactions_in_sync' => false,
                'last_transaction_in_sync' => null,
            ];
        }

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
            'entity_id' => $request->entity_id,
            'user_id' => explode('|', $token)[0],
        ], [
            'token' => $token,
        ]);

        if($this->cleanupNeeded($request->entity_id, $request->transactions)) {
            $newTransactions = collect($request->transactions)
                ->whereNotIn('id', $this->getUuids($request->entity_id))
                ->map(function ($transaction) use ($userToken) {
                    ray($transaction);
                    $transaction['user_token_id'] = $userToken->id;
                    $transaction['value'] = json_encode(Arr::get($transaction, 'value'));
                    $transaction['created_at'] = Carbon::parse($transaction['created_at'])
                        ->toDateTimeString('milliseconds');
                    return $transaction;
                })
                ->toArray();

            Transaction::insert($newTransactions);
ray($newTransactions);
            $transactions = app(CleanTransactions::class)
                ->execute($request->entity_id);

            if(count($newTransactions) > 0) {
                broadcast(new TransactionsAdded($request->entity_id, $request->origin, $newTransactions));
                broadcast(new TransactionsRecalculated($request->entity_id, $request->origin));
            }
        }
        else {
            $transactions = [];
            foreach($request->transactions as $transaction) {
                $transaction['user_token_id'] = $userToken->id;
                $transactions[] = Transaction::create($transaction);
            }
            if(count($transactions) > 0) {
                broadcast(new TransactionsAdded($request->entity_id, $request->origin, $transactions));
            }

            $transactions = match($request->last_transaction_in_sync) {
                true => [],
                null => Transaction::where('entity_id', $request->entity_id)->get(),
                default => Transaction::query()
                    ->where('entity_id', $request->entity_id)
                    ->where('created_at', '>=', Transaction::find($request->last_transaction_in_sync)->created_at)
                    ->get(),
            };
        }

        return [
            'transactions' => TransactionResource::collection($transactions),
        ];
    }

    private function getUuids($entity)
    {
        return Transaction::query()
            ->where('entity_id', $entity)
            ->pluck('id');
    }

    private function cleanupNeeded($entityId, $transactions)
    {
        // Check if the youngest existing transaction is older than the oldest new transaction
        $existingTransactionsDate = Transaction::where('entity_id', $entityId)
            ->max('created_at');

        $newTransactionsDate = collect($transactions)->min('created_at');
        return $existingTransactionsDate > $newTransactionsDate;
    }
}
