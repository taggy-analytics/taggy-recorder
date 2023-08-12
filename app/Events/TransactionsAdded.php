<?php

namespace App\Events;

class TransactionsAdded extends EntityBroadcastEvent
{
    public function __construct(
        protected $entityId,
        public $transactions,
    ){
        parent::__construct($entityId);
    }
}
