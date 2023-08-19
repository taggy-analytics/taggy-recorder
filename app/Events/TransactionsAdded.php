<?php

namespace App\Events;

use Illuminate\Support\Arr;

class TransactionsAdded extends EntityBroadcastEvent
{
    public function __construct(
        protected $entityId,
        public $origin,
        public $transactions,
    ){
        $this->transactions = Arr::map($this->transactions, fn($transaction) => Arr::except($transaction, 'user_token_id'));
        parent::__construct($entityId, $origin);
    }
}
