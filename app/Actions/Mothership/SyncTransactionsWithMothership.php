<?php

namespace App\Actions\Mothership;

use App\Enums\LogMessageType;
use App\Exceptions\MothershipException;
use App\Models\Transaction;
use App\Models\UserToken;
use App\Support\Mothership;
use App\Support\Recorder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\File;


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

        // $entities = UserToken::perEntity();

        $userTokens = UserToken::byEndpointAndEntity();

        foreach($userTokens as $userToken) {
            try {
                // Let's make sure we have a valid user token
                // ToDo: we could iterate through all tokens until we don't get a 401/403
                // For now let's just use the most recent token and hope for the best.

                $mothership = Mothership::make($userToken);

                $uuids = $this->getQuery($userToken->entity_id, $userToken->endpoint)
                    ->orderBy('created_at')
                    ->orderBy('id')
                    ->pluck('id')
                    ->toArray();

                $hashes = $this->getSegmentsHash($uuids, self::SEGMENT_SIZE_FACTOR, self::SEGMENT_MIN_SIZE, $userToken->entity_id);

                $checkSync = $mothership
                    ->getTransactionsStatus($userToken->entity_id, $hashes, self::HASH_SUBSTRING_LENGTH);

                if(!$checkSync['transactions_in_sync']) {
                    //foreach($userTokenTransactions as $userTokenId => $transactions) {
                        $transactions = $this->getQuery($userToken->entity_id, $userToken->endpoint)
                            //->where('reported_to_mothership', false)
                            ->when(filled($checkSync['last_transaction_in_sync']),
                                fn(Builder $query) => $query->where('created_at', '>', Transaction::firstWhere('id', $checkSync['last_transaction_in_sync'])->created_at))
                            ->get(['id', 'entity_id', 'user_id', 'parent_1', 'parent_2', 'model_type', 'model_id', 'action', 'property', 'value', 'created_at']);

                        $reportResponse = $mothership
                            ->reportTransactions($userToken->entity_id, $transactions, $checkSync['last_transaction_in_sync']);

                        if(!$reportResponse) {
                            Recorder::make()->log(LogMessageType::REPORTING_TRANSACTIONS_FAILED, 'Reporting failed', [
                                'entity' => $userToken->entity_id,
                                'transactions' => $transactions,
                                'lastTransactionInSync' => $checkSync['last_transaction_in_sync'],
                            ]);
                            return;
                        }

                        if(count($reportResponse['transactions']) == 0) {
                            return;
                        }

                        $cleanedTransactions = collect($reportResponse['transactions']);

                        $databaseUuids = $this->getQuery($userToken->entity_id, $userToken->endpoint)->pluck('id');

                        $cleanedTransactionsUuids = $cleanedTransactions->pluck('id');
                        $uuidsInCleanedTransactionsButNotInDatabase = $cleanedTransactionsUuids->diff($databaseUuids);

                        $transactionsToInsert = $cleanedTransactions
                            ->whereIn('id', $uuidsInCleanedTransactionsButNotInDatabase)
                            ->hydrateTransactions($userToken->endpoint)
                            ->toArray();

                        Transaction::insertChunked($transactionsToInsert);

                        if($reportResponse['content'] == 'all-transactions') {
                            $uuidsInDatabaseButNotInCleanedTransactions = $databaseUuids->diff($cleanedTransactionsUuids);

                            Transaction::query()
                                ->whereIn('id', $uuidsInDatabaseButNotInCleanedTransactions)
                                ->delete();
                        }
                    //}
                }
            }
            catch(MothershipException $exception) {
                if($exception->response->status() < 500) {
                    // ToDo: what to do in this case!?
                    info('Transactions could not be synced for entity #' . $userToken->entity_id . ' (HTTP status ' . $exception->response->status() . ')');
                }
                blink()->forget('sync-transactions-running');
                throw $exception;
            }
        }

        blink()->forget('sync-transactions-running');
    }

    private function getSegmentsHash($uuids, $factor, $minSize, $entityId) {
        $segments = [];

        $currentIndex = -1;

        $debug = implode(PHP_EOL, $uuids) . PHP_EOL . PHP_EOL;

        while (count($uuids) > 0) {
            $hashData = '';

            $segmentSize = config('app.debug') ? 1 : max($minSize, ceil(count($uuids) * $factor));
            $segment = array_splice($uuids, 0, $segmentSize);

            foreach ($segment as $item) {
                $hashData .= substr($item, -self::HASH_SUBSTRING_LENGTH);
                $currentIndex++;
            }

            $debug .= crc32($hashData) . ' ' . $hashData . PHP_EOL;

            $segments[$currentIndex] = crc32($hashData);
        }

        if(config('app.debug')) {
            File::put(storage_path('logs/transactions-' . $entityId . '.log'), $debug);
        }

        return $segments;
    }

    private function getQuery($entityId, $endpoint)
    {
        return Transaction::query()
            ->where('endpoint', $endpoint)
            ->where('entity_id', $entityId);
    }
}
