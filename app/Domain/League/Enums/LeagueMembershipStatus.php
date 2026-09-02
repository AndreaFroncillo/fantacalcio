<?php

namespace App\Domain\League\Enums;

enum LeagueMembershipStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
}
