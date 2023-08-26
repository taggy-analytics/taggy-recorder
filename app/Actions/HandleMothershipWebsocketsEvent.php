<?php

namespace App\Actions;

use App\Enums\WebsocketEventType;
use App\Events\TransactionsAdded;
use App\Events\TransactionsRecalculated;
use App\Models\Transaction;
use App\Support\Recorder;

class HandleMothershipWebsocketsEvent
{
    public function execute(WebsocketEventType $eventType, $entityId, $data)
    {
        ray($data);
        if($eventType === WebsocketEventType::TRANSACTIONS_ADDED) {
            $newTransactions = collect($data['transactions'])
                ->whereNotIn('id', $this->getUuids($entityId))
                ->hydrateTransactions()
                ->toArray();

            ray($newTransactions);

            if(count($newTransactions) > 0) {
                Transaction::insert($newTransactions);

                broadcast(new TransactionsAdded($entityId, Recorder::make()->getSystemId(), $newTransactions));
            }
        }
    }

    private function getUuids($entity)
    {
        return Transaction::query()
            ->where('entity_id', $entity)
            ->pluck('id');
    }
}
