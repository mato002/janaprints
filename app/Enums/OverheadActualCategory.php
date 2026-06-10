<?php

namespace App\Enums;

enum OverheadActualCategory: string
{
    case Electricity = 'electricity';
    case Rent = 'rent';
    case MachineDepreciation = 'machine_depreciation';
    case MachineMaintenance = 'machine_maintenance';
    case LabourSupport = 'labour_support';
    case Internet = 'internet';
    case Water = 'water';
    case Security = 'security';
    case AdminOverhead = 'admin_overhead';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Electricity => __('Electricity'),
            self::Rent => __('Rent'),
            self::MachineDepreciation => __('Machine depreciation'),
            self::MachineMaintenance => __('Machine maintenance'),
            self::LabourSupport => __('Labour support'),
            self::Internet => __('Internet'),
            self::Water => __('Water'),
            self::Security => __('Security'),
            self::AdminOverhead => __('Admin overhead'),
            self::Other => __('Other'),
        };
    }
}
