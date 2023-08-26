<?php

namespace App\Enums;

enum WebsocketEventType : string
{
    case TRANSACTIONS_ADDED = 'TransactionsAdded';
    case DISCONNECTED = 'Disconnected';
    case SUBSCRIPTION_FAILED = 'SubscriptionFailed';
    case SUBSCRIPTION_SUCCEEDED = 'SubscriptionSucceeded';
}
