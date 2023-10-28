<?php

namespace App\Actions\Mothership;

use App\Enums\LogMessageType;
use App\Exceptions\MothershipException;
use App\Models\Transaction;
use App\Models\UserToken;
use App\Support\Mothership;
use App\Support\Recorder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class SyncTransactionsWithMothership
{
    public const HASH_SUBSTRING_LENGTH = 3;
    public const SEGMENT_SIZE_FACTOR = 0.2;
    public const SEGMENT_MIN_SIZE = 20;

    public function execute()
    {
        if(blink()->get('sync-transactions-running')) {
            Recorder::make()->log(LogMessageType::SYNC_TRANSACTIONS_ALREADY_RUNNING);
        }

        blink()->put('sync-transactions-running', true);
        $entityTransactions = Transaction::query()
            ->get()
            ->groupBy(['entity_id', 'user_token_id']);

        $entities = UserToken::perEntity();

        foreach($entities->keys() as $entityId) {
            $userTokenTransactions = Arr::get($entityTransactions, $entityId, []);

            $uuids = $this->getQuery($entityId)
                ->orderBy('created_at')
                ->pluck('id')
                ->toArray();

            $hashes = $this->getSegmentsHash($uuids, self::SEGMENT_SIZE_FACTOR, self::SEGMENT_MIN_SIZE);

            try {
                // Let's make sure we have a valid user token
                // ToDo: we could iterate through all tokens until we don't get a 401/403
                // For now let's just use the most recent token and hope for the best.
                $userToken = $entities[$entityId]->first();

                $checkSync = Mothership::make($userToken)
                    ->getTransactionsStatus($entityId, $hashes, self::HASH_SUBSTRING_LENGTH);

                if(!$checkSync['transactions_in_sync']) {
                    foreach($userTokenTransactions as $userTokenId => $transactions) {
                        $transactions = $this->getQuery($entityId)
                            //->where('reported_to_mothership', false)
                            ->when(filled($checkSync['last_transaction_in_sync']),
                                fn(Builder $query) => $query->where('created_at', '>', Transaction::firstWhere('id', $checkSync['last_transaction_in_sync'])->created_at))
                            ->get(['id', 'entity_id', 'user_id', 'parent_1', 'parent_2', 'model_type', 'model_id', 'action', 'property', 'value', 'created_at']);

                        $reportResponse = Mothership::make(UserToken::find($userTokenId))
                            ->reportTransactions($entityId, $transactions, $checkSync['last_transaction_in_sync']);

                        if(count($reportResponse['transactions']) == 0) {
                            return;
                        }

                        $cleanedTransactions = collect($reportResponse['transactions']);

                        $databaseUuids = $this->getQuery($entityId)->pluck('id');

                        $cleanedTransactionsUuids = $cleanedTransactions->pluck('id');
                        $uuidsInCleanedTransactionsButNotInDatabase = $cleanedTransactionsUuids->diff($databaseUuids);

                        $transactionsToInsert = $cleanedTransactions
                            ->whereIn('id', $uuidsInCleanedTransactionsButNotInDatabase)
                            ->hydrateTransactions()
                            ->toArray();

                        Transaction::insert($transactionsToInsert);

                        if($reportResponse['content'] == 'all-transactions') {
                            $uuidsInDatabaseButNotInCleanedTransactions = $databaseUuids->diff($cleanedTransactionsUuids);

                            Transaction::query()
                                ->whereIn('id', $uuidsInDatabaseButNotInCleanedTransactions)
                                ->delete();
                        }
                    }
                }
            }
            catch(MothershipException $exception) {
                report($exception);
                if($exception->response->status() < 500) {
                    // ToDo: what to do in this case!?
                    info('Not authorized to sync transactions for entity ' . $entityId);
                }
                blink()->forget('sync-transactions-running');
                throw $exception;
            }
        }

        blink()->forget('sync-transactions-running');
    }

    private function getSegmentsHash($array, $factor, $minSize) {
        $segments = [];

        $currentIndex = -1;

        while (count($array) > 0) {
            $hashData = '';

            $segmentSize = max($minSize, ceil(count($array) * $factor));
            $segment = array_splice($array, 0, $segmentSize);

            foreach ($segment as $item) {
                $hashData .= substr($item, 0, self::HASH_SUBSTRING_LENGTH);
                $currentIndex++;
            }

            $segments[$currentIndex] = crc32($hashData);
        }

        return $segments;
    }

    private function getQuery($entityId)
    {
        return Transaction::query()
            ->where('entity_id', $entityId);
    }
}
