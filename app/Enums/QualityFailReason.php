<?php

namespace App\Enums;

enum QualityFailReason: string
{
    case QuantityMismatch = 'quantity_mismatch';
    case ArtworkError = 'artwork_error';
    case PrintDefect = 'print_defect';
    case NumberingError = 'numbering_error';
    case FinishingDefect = 'finishing_defect';
    case PackagingDefect = 'packaging_defect';
    case SerialRangeError = 'serial_range_error';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::QuantityMismatch => __('Quantity mismatch'),
            self::ArtworkError => __('Artwork error'),
            self::PrintDefect => __('Print defect'),
            self::NumberingError => __('Numbering error'),
            self::FinishingDefect => __('Finishing defect'),
            self::PackagingDefect => __('Packaging defect'),
            self::SerialRangeError => __('Serial range error'),
            self::Other => __('Other'),
        };
    }
}
