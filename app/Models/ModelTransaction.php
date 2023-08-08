<?php

namespace App\Models;

use App\Enums\ModelTransactionAction;
use App\Enums\ModelTransitionError;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ModelTransaction extends Model
{
    public $timestamps = false;

    protected $casts = [
        'action' => ModelTransactionAction::class,
        'value' => 'json',
        'created_at' => 'timestamp',
        'error' => ModelTransitionError::class,
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('orderByCreatedAt', function (Builder $builder) {
            $builder->orderByDesc('created_at');
        });
    }

    public function model()
    {
        return $this->morphTo();
    }
}
