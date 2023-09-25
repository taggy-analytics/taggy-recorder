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
use App\Support\Mothership;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class TransactionController extends Controller
{
    public function status($entityId, TransactionsStatusRequest $request)
    {
        $uuids = $this->getUuids($entityId);

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

        if($lastUuidInSync == -1) {
            return [
                'transactions_in_sync' => false,
                'last_transaction_in_sync' => null,
            ];
        }

        if(count($uuids) > $lastUuidInSync) {
            return [
                'transactions_in_sync' => false,
                'last_transaction_in_sync' => $uuids[$lastUuidInSync],
            ];
        }

        return [
            'transactions_in_sync' => true,
        ];
    }

    public function store($entityId, StoreTransactionsRequest $request)
    {
        $token = cache()->get('user-token');

        $userToken = UserToken::firstOrCreate([
            'entity_id' => $entityId,
            'user_id' => $request->user()->id,
            'endpoint' => cache()->get('mothership-endpoint'),
        ], [
            'token' => $token,
        ]);

        $mothership = Mothership::make($userToken);

        if($this->cleanupNeeded($entityId, $request->transactions)) {
            $newTransactions = collect($request->transactions)
                ->whereNotIn('id', $this->getUuids($entityId))
                ->hydrateTransactions()
                ->map(function ($transaction) use ($userToken) {
                    $transaction['user_token_id'] = $userToken->id;
                    return $transaction;
                })
                ->toArray();

            Transaction::insert($newTransactions);

            $transactions = app(CleanTransactions::class)
                ->execute($entityId);

            if(count($newTransactions) > 0) {
                broadcast(new TransactionsAdded($entityId, $request->origin, $newTransactions));
                broadcast(new TransactionsRecalculated($entityId, $request->origin));

                if($mothership->isOnline()) {
                    $mothership->reportTransactions($entityId, $newTransactions);
                }
            }

            $content = 'all-transactions';
        }
        else {
            $transactions = [];
            foreach($request->transactions as $transaction) {
                $transaction['user_token_id'] = $userToken->id;
                $transaction = Transaction::firstOrCreate(['id' => $transaction['id']], $transaction);
                if($transaction->wasRecentlyCreated) {
                    $transactions[] = $transaction;
                }
            }

            if(count($transactions) > 0) {
                broadcast(new TransactionsAdded($entityId, $request->origin, $transactions));

                if($mothership->isOnline()) {
                    $mothership->reportTransactions($entityId, $transactions);
                }
            }

            $transactions = match($request->last_transaction_in_sync) {
                true => [],
                null => Transaction::where('entity_id', $entityId)->get(),
                default => Transaction::query()
                    ->where('entity_id', $entityId)
                    ->where('created_at', '>=', Transaction::find($request->last_transaction_in_sync)->created_at)
                    ->get(),
            };
            $content = 'new-transactions';
        }

        return [
            'transactions' => TransactionResource::collection($transactions),
            'content' => $content,
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
