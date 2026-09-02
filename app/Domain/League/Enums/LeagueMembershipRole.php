<?php

namespace App\Domain\League\Enums;

enum LeagueMembershipRole: string
{
    case PRESIDENT = 'president';
    case MEMBER = 'member';
}
