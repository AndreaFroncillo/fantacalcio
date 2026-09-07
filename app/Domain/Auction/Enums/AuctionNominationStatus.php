<?php

namespace App\Domain\Auction\Enums;

enum AuctionNominationStatus: string
{
    case ACTIVE = 'active';
    case COMPLETED = 'completed';
    case REJECTED = 'rejected';
}
