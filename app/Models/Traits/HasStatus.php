<?php

namespace App\Models\Traits;

trait HasStatus
{
    public static function withStatus($status)
    {
        return self::where('status', $status)->get();
    }

    public function setStatus($status)
    {
        $this->update([
            'status' => $status,
        ]);
    }
}
