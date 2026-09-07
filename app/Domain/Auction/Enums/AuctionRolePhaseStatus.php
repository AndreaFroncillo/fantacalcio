<?php

namespace App\Domain\Auction\Enums;

enum AuctionRolePhaseStatus: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case COMPLETED = 'completed';
}
