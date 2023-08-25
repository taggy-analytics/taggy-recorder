<?php

namespace App\Events;

class TransactionsRecalculated extends EntityBroadcastEvent
{
    public function broadcastAs()
    {
        return 'TransactionsRecalculated';
    }
}
