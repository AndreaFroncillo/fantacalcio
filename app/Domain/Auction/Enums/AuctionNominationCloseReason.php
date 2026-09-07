<?php

namespace App\Domain\Auction\Enums;

enum AuctionNominationCloseReason: string
{
    case TIMER_EXPIRED = 'timer_expired';
    case PRESIDENT_CONFIRMED = 'president_confirmed';
    case PRESIDENT_REJECTED = 'president_rejected';
}
