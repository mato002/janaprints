<?php

namespace App\Enums;

enum StockReceiptSource: string
{
    case Purchase = 'purchase';
    case Return = 'return';
    case Adjustment = 'adjustment';
}
