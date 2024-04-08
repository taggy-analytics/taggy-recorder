<?php

namespace App\Models\Traits;

trait InsertsChunked
{
    public static function insertChunked($inserts)
    {
        $chunks = array_chunk($inserts, 500);

        foreach ($chunks as $chunk) {
            static::insert($chunk);
        }
    }
}
