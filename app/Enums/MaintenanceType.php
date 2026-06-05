<?php

namespace App\Enums;

enum MaintenanceType: string
{
    case Preventive = 'preventive';
    case Corrective = 'corrective';
    case Inspection = 'inspection';
    case Calibration = 'calibration';
    case Emergency = 'emergency';
    case Warranty = 'warranty';

    public function label(): string
    {
        return match ($this) {
            self::Preventive => __('Preventive'),
            self::Corrective => __('Corrective'),
            self::Inspection => __('Inspection'),
            self::Calibration => __('Calibration'),
            self::Emergency => __('Emergency'),
            self::Warranty => __('Warranty'),
        };
    }

    public function blocksProduction(): bool
    {
        return in_array($this, [self::Emergency, self::Corrective], true);
    }
}
