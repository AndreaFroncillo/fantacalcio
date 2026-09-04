<?php

namespace App\Domain\Credit\Enums;

enum CreditTransactionType: string
{
    case INITIAL_ALLOCATION = 'initial_allocation';
    case PLAYER_ACQUISITION = 'player_acquisition';
    case PLAYER_RELEASE = 'player_release';
    case TRADE = 'trade';
    case ADJUSTMENT = 'adjustment';
}
