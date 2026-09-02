<?php

namespace App\Domain\Football\Enums;

enum FootballSeasonStatus: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case COMPLETED = 'completed';
}
