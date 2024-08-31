<?php

namespace App\Actions;

use App\Actions\Mothership\SyncTransactionsWithMothership;
use App\Enums\WebsocketEventType;
use App\Events\TransactionsAdded;
use App\Models\Transaction;
use App\Support\Mothership;
use App\Support\Recorder;
use Illuminate\Support\Str;

class HandleMothershipWebsocketsEvent
{
    public function execute(WebsocketEventType $eventType, $entityId, $data)
    {
        $method = 'run' . Str::studly(strtolower($eventType->name));

        if(method_exists($this, $method)) {
            $this->$method($entityId, $data);
        }
    }

    private function runSubscriptionSucceeded()
    {
        app(SyncTransactionsWithMothership::class)
            ->execute();
    }

    private function runTransactionsAdded($entityId, $data)
    {
        $newTransactions = collect($data['transactions'])
            ->whereNotIn('id', $this->getUuids($entityId))
            ->hydrateTransactions(Mothership::getEndpoint())
            ->toArray();

        if(count($newTransactions) > 0) {
            Transaction::insertChunked($newTransactions);

            broadcast(new TransactionsAdded($entityId, $newTransactions, Recorder::make()->getSystemId()));
        }
    }

    private function getUuids($entity)
    {
        return Transaction::query()
            ->where('entity_id', $entity)
            ->pluck('id');
    }
}
