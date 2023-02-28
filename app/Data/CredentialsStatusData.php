<?php

namespace App\Data;

use Carbon\Carbon;
use Spatie\LaravelData\Data;

class CredentialsStatusData extends Data
{
    public function __construct(
        public ?Carbon $invalidCredentialsDiscoveredAt = null,
        public ?Carbon $invalidCredentialsReportedAt = null,
        public ?Carbon $newCredentialsReceivedAt = null,
        public ?Carbon $newCredentialsSuccessfulAt = null,
        public ?Carbon $newCredentialsUnsuccessfulAt = null,
    ) {}
}
