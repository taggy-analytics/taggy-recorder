<?php

namespace App\Enums;

enum WebsocketEventType : string
{
    case TRANSACTIONS_ADDED = 'TransactionsAdded';
    case SERVER_ONLINE = 'ServerOnline';
    case SERVER_OFFLINE = 'ServerOffline';
    case SUBSCRIPTION_FAILED = 'SubscriptionFailed';
}
