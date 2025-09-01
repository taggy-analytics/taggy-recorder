<?php

namespace App\Models\Traits;

use Illuminate\Support\Str;

trait HasUuid
{
    public static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid();
            }
        });
    }

    public static function byUuid($uuid)
    {
        return self::firstWhere('uuid', $uuid);
    }
}
