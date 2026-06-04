<?php

namespace App\Enums;

enum FixedAssetStatus: string
{
    case Active = 'active';
    case UnderRepair = 'under_repair';
    case Disposed = 'disposed';
    case Retired = 'retired';
}
