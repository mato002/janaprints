<?php

namespace App\Enums;

enum PrintProductTemplateCategory: string
{
    case Stationery = 'stationery';
    case Marketing = 'marketing';
    case LargeFormat = 'large_format';
    case Packaging = 'packaging';
    case CorporateBranding = 'corporate_branding';
    case Books = 'books';
    case Promotional = 'promotional';
    case SecurityPrinting = 'security_printing';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::Stationery => __('Stationery'),
            self::Marketing => __('Marketing'),
            self::LargeFormat => __('Large Format'),
            self::Packaging => __('Packaging'),
            self::CorporateBranding => __('Corporate Branding'),
            self::Books => __('Books'),
            self::Promotional => __('Promotional'),
            self::SecurityPrinting => __('Security Printing'),
            self::Custom => __('Custom'),
        };
    }
}
