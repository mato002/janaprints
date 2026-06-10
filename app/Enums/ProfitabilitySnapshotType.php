<?php

namespace App\Enums;

enum ProfitabilitySnapshotType: string
{
    case Job = 'job';
    case Quotation = 'quotation';
    case Customer = 'customer';
    case Machine = 'machine';
    case Material = 'material';
    case Product = 'product';
    case Branch = 'branch';
    case Period = 'period';

    public function label(): string
    {
        return match ($this) {
            self::Job => __('Job'),
            self::Quotation => __('Quotation'),
            self::Customer => __('Customer'),
            self::Machine => __('Machine'),
            self::Material => __('Material'),
            self::Product => __('Product'),
            self::Branch => __('Branch'),
            self::Period => __('Period'),
        };
    }
}
