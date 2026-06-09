<?php

namespace App\Enums;

enum ProductionWasteType: string
{
    case SetupWaste = 'setup_waste';
    case PrintError = 'print_error';
    case Damage = 'damage';
    case MachineFault = 'machine_fault';
    case QualityReject = 'quality_reject';
    case OperatorError = 'operator_error';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::SetupWaste => __('Setup waste'),
            self::PrintError => __('Print error'),
            self::Damage => __('Damage'),
            self::MachineFault => __('Machine fault'),
            self::QualityReject => __('Quality reject'),
            self::OperatorError => __('Operator error'),
            self::Custom => __('Custom'),
        };
    }
}
