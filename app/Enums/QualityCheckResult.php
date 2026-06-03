<?php

namespace App\Enums;

enum QualityCheckResult: string
{
    case Passed = 'passed';
    case Failed = 'failed';
    case ReworkRequired = 'rework_required';
}
