<?php

namespace App\Enums;

enum ProductionType: string
{
    case Digital = 'digital';
    case Offset = 'offset';
    case LargeFormat = 'large_format';
    case Finishing = 'finishing';
    case Packaging = 'packaging';
    case Mixed = 'mixed';
}
