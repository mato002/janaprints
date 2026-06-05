<?php

namespace App\Enums;

enum ExitType: string
{
    case Resignation = 'resignation';
    case Termination = 'termination';
    case Retirement = 'retirement';
    case ContractEnd = 'contract_end';
    case Death = 'death';

    public function label(): string
    {
        return match ($this) {
            self::Resignation => __('Resignation'),
            self::Termination => __('Termination'),
            self::Retirement => __('Retirement'),
            self::ContractEnd => __('Contract End'),
            self::Death => __('Death'),
        };
    }
}
