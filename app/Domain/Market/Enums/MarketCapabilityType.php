<?php

namespace App\Domain\Market\Enums;

enum MarketCapabilityType: string
{
    case RELEASES = 'releases';
    case AUCTION = 'auction';
    case TRADES = 'trades';
    case OFFERS = 'offers';
}
