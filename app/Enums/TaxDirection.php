<?php

namespace App\Enums;

enum TaxDirection: string
{
    case Output = 'output';
    case Input = 'input';

    public function label(): string
    {
        return match ($this) {
            self::Output => __('Output tax'),
            self::Input => __('Input tax'),
        };
    }
}
