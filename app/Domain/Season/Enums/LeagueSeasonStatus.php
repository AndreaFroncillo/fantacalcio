<?php

namespace App\Domain\Season\Enums;

enum LeagueSeasonStatus: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case COMPLETED = 'completed';
}
