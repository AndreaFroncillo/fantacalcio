<?php

namespace App\Domain\Season\Enums;

enum SeasonParticipationStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
}
