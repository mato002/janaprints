<?php

namespace App\Enums;

enum ArtworkAnalysisSource: string
{
    case Upload = 'upload';
    case Quotation = 'quotation';
    case ProductionJob = 'production_job';
    case QuoteRequest = 'quote_request';
    case Manual = 'manual';

    public function label(): string
    {
        return match ($this) {
            self::Upload => __('Upload'),
            self::Quotation => __('Quotation'),
            self::ProductionJob => __('Production job'),
            self::QuoteRequest => __('Quote request'),
            self::Manual => __('Manual'),
        };
    }
}
