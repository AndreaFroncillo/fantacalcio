<?php

namespace App\Domain\Football\Enums;

enum PlayerRole: string
{
    case GOALKEEPER = 'P';
    case DEFENDER = 'D';
    case MIDFIELDER = 'C';
    case FORWARD = 'A';
}
