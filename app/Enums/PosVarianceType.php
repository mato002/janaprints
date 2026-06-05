<?php

namespace App\Enums;

enum PosVarianceType: string
{
    case Over = 'over';
    case Short = 'short';
    case Balanced = 'balanced';

    public static function fromAmount(float $variance): self
    {
        if ($variance > 0) {
            return self::Over;
        }

        if ($variance < 0) {
            return self::Short;
        }

        return self::Balanced;
    }
}
