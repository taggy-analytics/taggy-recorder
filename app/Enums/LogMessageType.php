<?php

namespace App\Enums;

enum LogMessageType: string
{
    case INSTALLATION_FINISHED = 'installation-finished';
    case RTC_ISSUES = 'rtc-issues';
    case HOSTNAME_NOT_RESOLVABLE = 'hostname-not-resolvable';
    case SYNC_TRANSACTIONS_ALREADY_RUNNING = 'sync-transaction-already-running';
    case REPORTING_TRANSACTIONS_FAILED = 'reporting-transactions-failed';
}
