<?php

namespace App\Enums;

enum CycleCountFrequency: string
{
    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case Annual = 'annual';

    public function nextDate(\DateTimeInterface $from): string
    {
        $date = \Carbon\Carbon::parse($from);

        return match ($this) {
            self::Weekly => $date->addWeek()->toDateString(),
            self::Monthly => $date->addMonth()->toDateString(),
            self::Quarterly => $date->addMonths(3)->toDateString(),
            self::Annual => $date->addYear()->toDateString(),
        };
    }
}
