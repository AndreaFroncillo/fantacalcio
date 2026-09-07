<?php

namespace App\Domain\Market\Enums;

enum MarketCapabilityEventType: string
{
    case ENABLED = 'enabled';
    case DISABLED = 'disabled';
}
