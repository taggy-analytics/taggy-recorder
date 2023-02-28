<?php

namespace App\Data;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

class CredentialsStatusData extends Data
{
    public function __construct(
        public ?CarbonImmutable $invalidCredentialsDiscoveredAt = null,
        public ?CarbonImmutable $invalidCredentialsReportedAt = null,
        public ?CarbonImmutable $newCredentialsReceivedAt = null,
        public ?CarbonImmutable $newCredentialsSuccessfulAt = null,
        public ?CarbonImmutable $newCredentialsUnsuccessfulAt = null,
    ) {}
}
