<?php

namespace App\Enums;

enum ProductionMaterialFlowType: string
{
    case Wasted = 'wasted';
    case Returned = 'returned';

    public function label(): string
    {
        return match ($this) {
            self::Wasted => __('Wasted'),
            self::Returned => __('Returned'),
        };
    }

    public function isOutbound(): bool
    {
        return $this === self::Wasted;
    }
}
