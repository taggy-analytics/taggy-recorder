<?php

namespace App\Enums;

enum TransitionError: string
{
    case CANNOT_UPDATE_MODEL_THAT_DOESNT_EXIST = 'cannot-update-model-that-doesnt-exist';
    case CANNOT_UPDATE_MODEL_THAT_WAS_DELETED = 'cannot-update-model-that-was-deleted';
    case CANNOT_CREATE_MODEL_THAT_ALREADY_EXISTS = 'cannot-create-model-that-already-exists';
    case CANNOT_DELETE_MODEL_THAT_DOESNT_EXIST = 'cannot-delete-model-that-doesnt-exist';
    case CANNOT_DELETE_MODEL_THAT_ALREADY_WAS_DELETED = 'cannot-delete-model-that-already-was-deleted';
    case CANNOT_ATTACH_TO_MODEL_THAT_ALREADY_WAS_DELETED = 'cannot-attach-to-model-that-already-was-deleted';
    case CANNOT_DETACH_FROM_MODEL_THAT_ALREADY_WAS_DELETED = 'cannot-detach-from-model-that-already-was-deleted';
    case CANNOT_ATTACH_TO_MODEL_THAT_DOESNT_EXIST = 'cannot-attach-to-model-that-doesnt-exist';
    case CANNOT_DETACH_FROM_MODEL_THAT_DOESNT_EXIST = 'cannot-detach-from-model-that-doesnt-exist';
}
