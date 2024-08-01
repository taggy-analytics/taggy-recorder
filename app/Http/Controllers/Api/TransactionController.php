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
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    public function status($entityId, TransactionsStatusRequest $request)
    {
        $uuids = $this->getUuids(Mothership::getEndpoint(), $entityId);

        $uuidsConcatenated = $uuids
            ->map(fn($uuid) => substr($uuid, -$request->hash_substring_length))
            ->implode('');

        $lastUuidInSync = -1;

        // Log::channel('transactions')->info('UUIDs Concatenated', compact('uuids'));

        foreach($request->hashes as $index => $hash) {
            $offset = $request->hash_substring_length * ($lastUuidInSync + 1);
            $length = $request->hash_substring_length * ($index - $lastUuidInSync);
            // Log::channel('transactions')->info('Checking Hash', compact('index', 'hash', 'offset', 'length'));
            // Log::channel('transactions')->info('Calculated Hash', ['hash' => crc32(substr($uuidsConcatenated, $offset, $length))]);

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

        if(count($uuids) > $lastUuidInSync + 1) {
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
        $userToken = UserToken::firstOrCreate([
            'entity_id' => $entityId,
            'user_id' => $request->user()->id,
            'endpoint' => Mothership::getEndpoint(),
        ], [
            'token' => $request->header('User-Token'),
        ]);

        info('User token recently created?' . ($userToken->wasRecentlyCreated ? 'yes' : 'no'));
        info('User token: ' . $userToken);

        $mothership = Mothership::make($userToken);

        if($this->cleanupNeeded($entityId, $request->transactions)) {
            $newTransactions = collect($request->transactions)
                ->whereNotIn('id', $this->getUuids($userToken->endpoint, $entityId))
                ->hydrateTransactions($userToken->endpoint)
                ->map(function ($transaction) use ($userToken) {
                    $transaction['user_token_id'] = $userToken->id;
                    $transaction['endpoint'] = $userToken->endpoint;
                    return $transaction;
                })
                ->toArray();

            info('Entity ID: ' . $entityId);
            info('Header User-Token: ' . $request->header('User-Token'));
            info('Mothership::getEndpoint(): ' . Mothership::getEndpoint());
            info('$mothership->getEndpoint(): ' . $mothership->getEndpoint());
            info('$userToken->endpoint: ' . $userToken->endpoint);

            Transaction::insertChunked($newTransactions);

            $transactions = app(CleanTransactions::class)
                ->execute($userToken->endpoint, $entityId);

            if(count($newTransactions) > 0) {
                broadcast(new TransactionsAdded($entityId, $newTransactions, $request->origin));
                broadcast(new TransactionsRecalculated($entityId, $request->origin));

                if($mothership->isOnline()) {
                    $mothership->reportTransactions($entityId, $newTransactions);
                }
            }

            $content = 'all-transactions';
        }
        else {
            $transactions = [];
            foreach($request->transactions ?? [] as $transaction) {
                $transaction['user_token_id'] = $userToken->id;
                $transaction['endpoint'] = $userToken->endpoint;
                $transaction = Transaction::firstOrCreate(['id' => $transaction['id']], $transaction);
                if($transaction->wasRecentlyCreated) {
                    $transactions[] = $transaction;
                }
            }

            if(count($transactions) > 0) {
                broadcast(new TransactionsAdded($entityId, $transactions, $request->origin));

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
            'transactions' => TransactionResource::collection(collect($transactions)->sortBy(['created_at', 'id'])),
            'content' => $content,
        ];
    }

    private function getUuids($endpoint, $entity)
    {
        return Transaction::query()
            ->where('endpoint', $endpoint)
            ->where('entity_id', $entity)
            ->orderBy('created_at')
            ->orderBy('id')
            ->pluck('id');
    }

    private function cleanupNeeded($entityId, $transactions)
    {
        if(is_null($transactions) || count($transactions) == 0) {
            return false;
        }

        // Check if the youngest existing transaction is older than the oldest new transaction
        $existingTransactionsDate = Transaction::where('entity_id', $entityId)
            ->max('created_at');

        $newTransactionsDate = collect($transactions)->min('created_at');
        return $existingTransactionsDate > $newTransactionsDate;
    }
}
