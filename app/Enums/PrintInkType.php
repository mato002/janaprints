<?php

namespace App\Enums;

enum PrintInkType: string
{
    case Cmyk = 'cmyk';
    case Black = 'black';
    case EcoSolvent = 'ecosolvent';
    case Uv = 'uv';
    case Latex = 'latex';
    case Dtf = 'dtf';
    case DyeSub = 'dye_sub';

    public function label(): string
    {
        return match ($this) {
            self::Cmyk => __('CMYK'),
            self::Black => __('Black'),
            self::EcoSolvent => __('EcoSolvent'),
            self::Uv => __('UV'),
            self::Latex => __('Latex'),
            self::Dtf => __('DTF'),
            self::DyeSub => __('Dye Sub'),
        };
    }
}
