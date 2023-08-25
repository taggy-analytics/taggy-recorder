<?php

namespace App\Models;

use App\Enums\TransactionAction;
use App\Enums\TransitionError;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasUuids;

    public $timestamps = false;
    protected $dateFormat = 'Y-m-d H:i:s.v';

    protected static function booted(): void
    {
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('created_at');
        });
    }

    protected $casts = [
        'action' => TransactionAction::class,
        'value' => 'json',
        'created_at' => 'datetime',
        'error' => TransitionError::class,
    ];

    public function model()
    {
        return $this->morphTo();
    }

    public function userToken()
    {
        return $this->belongsTo(UserToken::class);
    }
}
