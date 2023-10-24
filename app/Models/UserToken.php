<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserToken extends Model
{
    protected $casts = [
        'token' => 'encrypted',
        'last_successfully_used_at' => 'datetime',
        'last_rejected_at' => 'datetime',
    ];

    public static function perEntity()
    {
        return self::lastSuccessfullyUsed()
            ->groupBy('entity_id');
    }

    public static function lastSuccessfullyUsed()
    {
        return self::query()
            ->where('last_successfully_used_at', '>', now()->subDays(30))
            ->where('endpoint', str_replace('/api/v1', '', config('services.mothership.endpoint')))
            ->orWhereNull('last_successfully_used_at')
            ->orderByDesc('last_successfully_used_at')
            ->orderByDesc('updated_at')
            ->get()
            ->whereNull('last_rejected_at');
    }
}
