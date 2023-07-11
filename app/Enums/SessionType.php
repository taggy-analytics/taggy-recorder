<?php

namespace App\Enums;

enum SessionType: string
{
    case MATCH = 'match';
    case TRAINING = 'training';
    case OPPONENT_SCOUTING = 'opponent-scouting';
}
