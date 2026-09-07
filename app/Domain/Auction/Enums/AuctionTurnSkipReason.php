<?php

namespace App\Domain\Auction\Enums;

enum AuctionTurnSkipReason: string
{
    case ROLE_COMPLETE = 'role_complete';
    case ROSTER_COMPLETE = 'roster_complete';
    case NO_CREDITS = 'no_credits';
    case PRESIDENT_OVERRIDE = 'president_override';
}
