<?php

namespace App\Domain\Team\Enums;

enum TeamStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
}
