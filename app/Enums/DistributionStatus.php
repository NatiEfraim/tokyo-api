<?php

namespace App\Enums;

enum DistributionStatus: int
{
    case PENDING = 1;
    case APPROVED = 2;
    case CANCELD = 3;
    case COLLECTED = 4;
    case INVALID = -1;
}