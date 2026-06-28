<?php

namespace App\Enums;

enum JobCostCategory: string
{
    case Material = 'material';
    case Labor = 'labor';
    case Machine = 'machine';
    case Finishing = 'finishing';
    case Outsourced = 'outsourced';
    case Wastage = 'wastage';
    case Overhead = 'overhead';
}
