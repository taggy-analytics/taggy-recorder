<?php

namespace App\Models;

use App\Support\Mothership;
use Illuminate\Database\Eloquent\Model;

class UserToken extends Model
{
    protected $casts = [
        'token' => 'encrypted',
        'last_successfully_used_at' => 'datetime',
        'last_rejected_at' => 'datetime',
    ];

    public function revoke()
    {
        $this->update([
            'last_rejected_at' => now(),
        ]);
    }

    public function isRevoked()
    {
        return filled($this->last_rejected_at);
    }

    public static function perEntity()
    {
        return self::lastSuccessfullyUsed()
            ->groupBy('entity_id');
    }

    public static function lastSuccessfullyUsed()
    {
        return self::query()
            ->whereNot('entity_id', 0)
            ->where('last_successfully_used_at', '>', now()->subDays(30))
            ->where('endpoint', Mothership::getEndpoint())
            ->orWhereNull('last_successfully_used_at')
            ->orderByDesc('last_successfully_used_at')
            ->orderByDesc('updated_at')
            ->get()
            ->whereNull('last_rejected_at');
    }

    public static function forEndpointAndEntity($endpoint, $entityId)
    {
        return self::query()
            ->where('endpoint', $endpoint)
            ->where('entity_id', $entityId)
            ->latest('last_successfully_used_at')
            ->whereNull('last_rejected_at')
            ->first();
    }
}
