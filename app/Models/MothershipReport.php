<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MothershipReport extends Model
{
    protected $casts = [
        'user_token' => 'encrypted',
        'reported_at' => 'datetime',
    ];

    public function model()
    {
        return $this->morphTo();
    }

    public static function unreported()
    {
        return self::whereNull('reported_at')->get();
    }
}
