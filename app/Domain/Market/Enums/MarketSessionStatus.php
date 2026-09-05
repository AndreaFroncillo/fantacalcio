<?php

namespace App\Domain\Market\Enums;

enum MarketSessionStatus: string
{
    case SCHEDULED = 'scheduled';
    case OPEN = 'open';
    case CLOSED = 'closed';
    case CANCELLED = 'cancelled';
}
