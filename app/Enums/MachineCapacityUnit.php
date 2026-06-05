<?php

namespace App\Enums;

enum MachineCapacityUnit: string
{
    case Sheets = 'sheets';
    case Jobs = 'jobs';
    case Meters = 'meters';
    case Prints = 'prints';
    case Books = 'books';

    public function label(): string
    {
        return match ($this) {
            self::Sheets => __('Sheets'),
            self::Jobs => __('Jobs'),
            self::Meters => __('Meters'),
            self::Prints => __('Prints'),
            self::Books => __('Books'),
        };
    }
}
