<?php

namespace App\Enums;

enum CommunicationAttachmentType: string
{
    case Pdf = 'pdf';
    case Quotation = 'quotation';
    case Invoice = 'invoice';
    case Statement = 'statement';
    case Artwork = 'artwork';
    case JobCard = 'job_card';
    case PayslipPdf = 'payslip_pdf';
    case Image = 'image';
    case Document = 'document';

    public function label(): string
    {
        return match ($this) {
            self::Pdf => __('PDF'),
            self::Quotation => __('Quotation'),
            self::Invoice => __('Invoice'),
            self::Statement => __('Statement'),
            self::Artwork => __('Artwork'),
            self::JobCard => __('Job card'),
            self::PayslipPdf => __('Payslip PDF'),
            self::Image => __('Image'),
            self::Document => __('Document'),
        };
    }
}
