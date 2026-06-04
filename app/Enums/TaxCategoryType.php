<?php

namespace App\Enums;

enum TaxCategoryType: string
{
    case Vat = 'vat';
    case WithholdingTax = 'withholding_tax';
    case ZeroRated = 'zero_rated';
    case Exempt = 'exempt';

    public function label(): string
    {
        return match ($this) {
            self::Vat => __('VAT'),
            self::WithholdingTax => __('Withholding Tax'),
            self::ZeroRated => __('Zero Rated'),
            self::Exempt => __('Exempt'),
        };
    }

    public function effectiveRatePercent(float $configuredRate): float
    {
        return match ($this) {
            self::Vat, self::WithholdingTax => $configuredRate,
            self::ZeroRated, self::Exempt => 0.0,
        };
    }
}
