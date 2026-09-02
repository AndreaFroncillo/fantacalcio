<?php

namespace App\Domain\Football\Enums;

enum FootballPlayerStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
}
