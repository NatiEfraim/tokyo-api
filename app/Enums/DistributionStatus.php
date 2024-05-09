<?php

namespace App\Enums;

enum DistributionStatus: int
{
    case PENDING = 0;
    case APPROVED = 1;
    case CANCELD = 2;
    case COLLECTED = 3;
}
