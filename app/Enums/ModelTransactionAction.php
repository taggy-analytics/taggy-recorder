<?php

namespace App\Enums;

enum ModelTransactionAction : string
{
    case CREATE = 'create';
    case UPDATE = 'update';
    case DELETE = 'delete';
    case ATTACH = 'attach';
    case DETACH = 'detach';

    public static function getValues()
    {
        return array_column(self::cases(), 'value');
    }
}
