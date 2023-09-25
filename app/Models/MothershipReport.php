<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MothershipReport extends Model
{
    protected $casts = [
        'user_token' => 'encrypted',
        'reported_at' => 'datetime',
        'processed_at' => 'datetime',
        'ready_to_send' => 'boolean',
    ];

    public function model()
    {
        return $this->morphTo();
    }

    public function userToken()
    {
        return $this->belongsTo(UserToken::class);
    }

    public static function unreported()
    {
        return self::query()
            ->whereNull('processed_at')
            ->where('ready_to_send', true)
            ->get();
    }
}
