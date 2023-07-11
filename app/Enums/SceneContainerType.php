<?php

namespace App\Enums;

enum SceneContainerType: string
{
    case SESSION = 'session';
    case PLAYER = 'player';
    case TRAINING = 'training';
    case COLLECTION = 'collection';
    case ALL = 'all';
    case OPPONENT_SCOUTING = 'opponent-scouting';
}
