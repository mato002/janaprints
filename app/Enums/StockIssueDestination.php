<?php

namespace App\Enums;

enum StockIssueDestination: string
{
    case Production = 'production';
    case InternalUse = 'internal_use';
    case Damage = 'damage';
    case Transfer = 'transfer';
}
