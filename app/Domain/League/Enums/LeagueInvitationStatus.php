<?php

namespace App\Domain\League\Enums;

enum LeagueInvitationStatus: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case REVOKED = 'revoked';
}
