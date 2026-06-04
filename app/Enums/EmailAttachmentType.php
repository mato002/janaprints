<?php

namespace App\Enums;

enum EmailAttachmentType: string
{
    case QuotationPdf = 'quotation_pdf';
    case InvoicePdf = 'invoice_pdf';
    case StatementPdf = 'statement_pdf';
    case ArtworkPdf = 'artwork_pdf';
    case JobCardPdf = 'job_card_pdf';
    case Report = 'report';
    case Image = 'image';
    case Document = 'document';

    public function label(): string
    {
        return match ($this) {
            self::QuotationPdf => __('Quotation PDF'),
            self::InvoicePdf => __('Invoice PDF'),
            self::StatementPdf => __('Statement PDF'),
            self::ArtworkPdf => __('Artwork PDF'),
            self::JobCardPdf => __('Job card PDF'),
            self::Report => __('Report'),
            self::Image => __('Image'),
            self::Document => __('Document'),
        };
    }
}
