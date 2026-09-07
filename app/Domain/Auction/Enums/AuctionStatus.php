<?php

namespace App\Domain\Auction\Enums;

enum AuctionStatus: string
{
    case SCHEDULED = 'scheduled';
    case LIVE = 'live';
    case PAUSED = 'paused';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}
