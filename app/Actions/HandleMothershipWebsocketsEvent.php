<?php

namespace App\Actions;

use App\Enums\WebsocketEventType;
use App\Events\TransactionsAdded;
use App\Models\Transaction;
use App\Support\Recorder;
use Illuminate\Support\Str;

class HandleMothershipWebsocketsEvent
{
    public function execute(WebsocketEventType $eventType, $entityId, $data)
    {
        $method = 'run' . Str::studly(strtolower($eventType->name));
        ray($method);
        if(method_exists($this, $method)) {
            $this->$method($entityId, $data);
        }
    }

    private function runTransactionsAdded($entityId, $data)
    {
        $newTransactions = collect($data['transactions'])
            ->whereNotIn('id', $this->getUuids($entityId))
            ->hydrateTransactions()
            ->toArray();

        if(count($newTransactions) > 0) {
            Transaction::insert($newTransactions);

            broadcast(new TransactionsAdded($entityId, Recorder::make()->getSystemId(), $newTransactions));
        }
    }

    private function getUuids($entity)
    {
        return Transaction::query()
            ->where('entity_id', $entity)
            ->pluck('id');
    }
}
