<?php

namespace App\Actions\Mothership;

use App\Enums\RecordingFileStatus;
use App\Enums\RecordingStatus;
use App\Exceptions\MothershipException;
use App\Models\ModelTransaction;
use App\Models\RecorderLog;
use App\Models\Recording;
use App\Models\RecordingFile;
use App\Models\UserToken;
use App\Support\Mothership;
use App\Support\Recorder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class SyncTransactionsWithMothership
{
    public const HASH_SUBSTRING_LENGTH = 3;
    public const SEGMENT_SIZE_FACTOR = 0.2;
    public const SEGMENT_MIN_SIZE = 20;

    public function execute()
    {
        $entityTransactions = ModelTransaction::query()
            ->get()
            ->groupBy(['entity_id', 'user_token_id']);

        $entities = UserToken::query()
            ->where('last_used_successfully_at', '>', now()->subDays(30))
            ->orWhereNull('last_used_successfully_at')
            ->get()
            ->groupBy('entity_id');

        foreach($entities->keys() as $entityId) {
            $userTokenTransactions = $entityTransactions[$entityId];

            $uuids = $this->getQuery($entityId)
                ->orderBy('created_at')
                ->pluck('uuid')
                ->toArray();

            $hashs = $this->getSegmentsHash($uuids, self::SEGMENT_SIZE_FACTOR, self::SEGMENT_MIN_SIZE);

            try {
                // Let's make sure we have a valid user token
                // ToDo: we could iterate through all tokens until we don't get a 401/403
                // For now let's just use the most recent token and hope for the best.
                $userToken = $entities[$entityId]->sortByDesc('last_successfully_used_at')->first();

                $checkSync = Mothership::make($userToken->token)
                    ->getTransactionsStatus($entityId, $hashs, self::HASH_SUBSTRING_LENGTH);

                ray($checkSync);

                if(!$checkSync['transactions_in_sync']) {
                    foreach($userTokenTransactions as $userTokenId => $transactions) {
                        $transactions = $this->getQuery($entityId)
                            ->where('reported_to_mothership', false)
                            ->when(filled($checkSync['last_transaction_in_sync']),
                                fn(Builder $query) => $query->where('created_at', '>', ModelTransaction::firstWhere('uuid', $checkSync['last_transaction_in_sync'])->created_at))
                            ->get(['uuid', 'entity_id', 'user_id', 'model_type', 'model_id', 'action', 'property', 'value', 'created_at']);

                        $cleanedTransactions = collect(Mothership::make(UserToken::find($userTokenId)->token)
                            ->sendTransactions($entityId, $transactions)['transactions']);

                        if($cleanedTransactions) {
                            $databaseUuids = $this->getQuery($entityId)->pluck('uuid');
                            $cleanedTransactionsUuids = $cleanedTransactions->pluck('uuid');

                            // $uuidsInCleanedTransactionsButNotInDatabase = $cleanedTransactionsUuids->diff($databaseUuids);
                            $uuidsInDatabaseButNotInCleanedTransactions = $databaseUuids->diff($cleanedTransactionsUuids);

                            // $cleanedTransactions->whereIn('uuid', $uuidsInCleanedTransactionsButNotInDatabase);
                            $cleanedTransactions->chunk(1000)->each(function ($chunk) {
                                $data = $chunk->map(fn($transaction) => [
                                    'uuid' => $transaction['uuid'],
                                    'entity_id' => $transaction['entity_id'],
                                    'user_id' => $transaction['user_id'],
                                    'model_type' => $transaction['model_type'],
                                    'model_id' => $transaction['model_id'],
                                    'action' => $transaction['action'],
                                    'property' => $transaction['property'],
                                    'value' => json_encode($transaction['value']),
                                    'created_at' => Carbon::parse($transaction['created_at'])->toDateTimeString('millisecond'),
                                ])->toArray();
                                ModelTransaction::insert($data);
                            });

                            ModelTransaction::whereIn('uuid', $uuidsInDatabaseButNotInCleanedTransactions)->delete();
                        }
                    }
                }

                $this->getQuery($entityId)->update([
                    'reported_to_mothership' => true,
                ]);
            }
            catch(MothershipException $exception) {
                if($exception->response->status() < 500) {
                    // ToDo: what to do in this case!?
                    info('Not authorized to sync transactions for entity ' . $entityId);
                }

                throw $exception;
            }
        }
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
        return ModelTransaction::query()
            ->where('entity_id', $entityId);
    }
}
