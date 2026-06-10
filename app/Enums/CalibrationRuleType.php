<?php

namespace App\Enums;

enum CalibrationRuleType: string
{
    case InkYield = 'ink_yield';
    case InkCost = 'ink_cost';
    case MachineRate = 'machine_rate';
    case LabourRate = 'labour_rate';
    case ElectricityRate = 'electricity_rate';
    case OverheadRate = 'overhead_rate';
    case WastageFactor = 'wastage_factor';
    case MarginRule = 'margin_rule';
    case ConfidenceRule = 'confidence_rule';

    public function label(): string
    {
        return match ($this) {
            self::InkYield => __('Ink yield'),
            self::InkCost => __('Ink cost'),
            self::MachineRate => __('Machine rate'),
            self::LabourRate => __('Labour rate'),
            self::ElectricityRate => __('Electricity rate'),
            self::OverheadRate => __('Overhead rate'),
            self::WastageFactor => __('Wastage factor'),
            self::MarginRule => __('Margin rule'),
            self::ConfidenceRule => __('Confidence rule'),
        };
    }

    public function formulaPrefix(): string
    {
        return match ($this) {
            self::InkYield, self::InkCost => 'PI3',
            self::MachineRate, self::LabourRate, self::ElectricityRate, self::OverheadRate => 'PI4',
            self::WastageFactor, self::MarginRule, self::ConfidenceRule => 'PI5',
        };
    }
}
