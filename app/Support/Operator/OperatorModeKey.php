<?php

namespace App\Support\Operator;

enum OperatorModeKey: string
{
    case Production = 'production';
    case Designer = 'designer';
    case Sales = 'sales';
    case Storekeeper = 'storekeeper';
}
