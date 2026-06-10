<?php

namespace App\Enums;

enum InventoryVarianceReasonCategory: string
{
    case MachineCalibration = 'machine_calibration';
    case StorageDamage = 'storage_damage';
    case DriedInk = 'dried_ink';
    case PaperDamage = 'paper_damage';
    case CountingError = 'counting_error';
    case TheftLoss = 'theft_loss';
    case SupplierShortage = 'supplier_shortage';
    case ProductionSpoilage = 'production_spoilage';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::MachineCalibration => __('Machine calibration'),
            self::StorageDamage => __('Storage damage'),
            self::DriedInk => __('Dried ink'),
            self::PaperDamage => __('Paper damage'),
            self::CountingError => __('Counting error'),
            self::TheftLoss => __('Theft / loss'),
            self::SupplierShortage => __('Supplier shortage'),
            self::ProductionSpoilage => __('Production spoilage'),
            self::Other => __('Other'),
        };
    }
}
