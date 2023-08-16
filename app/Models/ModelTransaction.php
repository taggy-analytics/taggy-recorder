<?php

namespace App\Models;

use App\Enums\ModelTransactionAction;
use App\Enums\ModelTransitionError;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ModelTransaction extends Model
{
    public $timestamps = false;
    protected $dateFormat = 'Y-m-d H:i:s.v';

    protected $casts = [
        'action' => ModelTransactionAction::class,
        'value' => 'json',
        'created_at' => 'datetime',
        'error' => ModelTransitionError::class,
        'reported_to_mothership' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('orderByCreatedAt', function (Builder $builder) {
            $builder->orderBy('created_at');
        });
    }

    public function model()
    {
        return $this->morphTo();
    }

    public function userToken()
    {
        return $this->belongsTo(UserToken::class);
    }
}
