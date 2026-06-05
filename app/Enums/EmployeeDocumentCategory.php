<?php

namespace App\Enums;

enum EmployeeDocumentCategory: string
{
    case Contract = 'contract';
    case IdCopy = 'id_copy';
    case KraPin = 'kra_pin';
    case ShifRecord = 'shif_record';
    case NssfRecord = 'nssf_record';
    case Certificate = 'certificate';
    case Cv = 'cv';
    case WarningLetter = 'warning_letter';
    case PerformanceReview = 'performance_review';
    case ExitDocument = 'exit_document';

    public function label(): string
    {
        return match ($this) {
            self::Contract => __('Contract'),
            self::IdCopy => __('ID Copy'),
            self::KraPin => __('KRA PIN'),
            self::ShifRecord => __('SHIF Record'),
            self::NssfRecord => __('NSSF Record'),
            self::Certificate => __('Certificate'),
            self::Cv => __('CV'),
            self::WarningLetter => __('Warning Letter'),
            self::PerformanceReview => __('Performance Review'),
            self::ExitDocument => __('Exit Document'),
        };
    }

    public function supportsExpiry(): bool
    {
        return match ($this) {
            self::Contract, self::Certificate, self::IdCopy, self::ShifRecord, self::NssfRecord => true,
            default => false,
        };
    }
}
