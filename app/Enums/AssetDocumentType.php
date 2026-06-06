<?php

namespace App\Enums;

enum AssetDocumentType: string
{
    case Photo = 'photo';
    case Manual = 'manual';
    case Warranty = 'warranty';
    case Purchase = 'purchase';
    case ServiceReport = 'service_report';
    case HandoverForm = 'handover_form';
    case TransferForm = 'transfer_form';
    case Insurance = 'insurance';
    case Certificate = 'certificate';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Photo => __('Photo'),
            self::Manual => __('Manual'),
            self::Warranty => __('Warranty Document'),
            self::Purchase => __('Purchase Document'),
            self::ServiceReport => __('Service Report'),
            self::HandoverForm => __('Handover Form'),
            self::TransferForm => __('Transfer Form'),
            self::Insurance => __('Insurance Document'),
            self::Certificate => __('Certificate'),
            self::Other => __('Other'),
        };
    }
}
