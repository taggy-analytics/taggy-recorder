<?php

namespace App\Actions\Mothership;

use App\Enums\RecordingFileStatus;
use App\Enums\RecordingStatus;
use App\Models\ModelTransaction;
use App\Models\RecorderLog;
use App\Models\Recording;
use App\Models\RecordingFile;
use App\Models\UserToken;
use App\Support\Mothership;
use App\Support\Recorder;
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
            ->where('reported_to_mothership', false)
            ->get()
            ->groupBy(['entity_id', 'user_token_id']);

        foreach($entityTransactions as $entityId => $userTokenTransactions) {
            $uuids = $this->getQuery($entityId)
                ->pluck('uuid')
                ->toArray();

            $hashs = $this->getSegmentsHash($uuids, self::SEGMENT_SIZE_FACTOR, self::SEGMENT_MIN_SIZE);

            $checkSync = Mothership::make($userTokenTransactions->first()->first()->userToken->token)
                ->getTransactionsStatus($entityId, $hashs, self::HASH_SUBSTRING_LENGTH);

            if(!$checkSync['transactions_in_sync']) {
                foreach($userTokenTransactions as $userTokenId => $transactions) {
                    $transactions = $this->getQuery($entityId)
                        ->when(filled($checkSync['last_transaction_in_sync']),
                            fn(Builder $query) => $query->where('created_at', '>', ModelTransaction::firstWhere('uuid', $checkSync['last_transaction_in_sync'])->created_at))
                        ->get(['uuid', 'entity_id', 'user_id', 'model_type', 'model_id', 'action', 'property', 'value', 'created_at']);

                    $cleanedTransactions = collect(Mothership::make(UserToken::find($userTokenId)->token)
                        ->sendTransactions($entityId, $transactions)['transactions']);

                    $databaseUuids = $this->getQuery($entityId)->pluck('uuid');

                    $cleanedTransactionsUuids = $cleanedTransactions->pluck('uuid');
                    $uuidsInCleanedTransactionsButNotInDatabase = $cleanedTransactionsUuids->diff($databaseUuids);
                    $uuidsInDatabaseButNotInCleanedTransactions = $databaseUuids->diff($cleanedTransactionsUuids);

                    ModelTransaction::insert($cleanedTransactions->whereIn('uuid', $uuidsInCleanedTransactionsButNotInDatabase)->toArray());
                    ModelTransaction::whereIn('uuid', $uuidsInDatabaseButNotInCleanedTransactions)->delete();
                }
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

            ray($hashData);

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
