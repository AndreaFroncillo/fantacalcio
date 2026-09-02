<?php

namespace App\Domain\Football\Enums;

enum PlayerSeasonStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
}
