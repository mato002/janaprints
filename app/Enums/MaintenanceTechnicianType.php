<?php

namespace App\Enums;

enum MaintenanceTechnicianType: string
{
    case Internal = 'internal';
    case External = 'external';

    public function label(): string
    {
        return match ($this) {
            self::Internal => __('Internal Technician'),
            self::External => __('External Vendor Technician'),
        };
    }
}
