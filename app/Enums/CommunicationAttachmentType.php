<?php

namespace App\Enums;

enum CommunicationAttachmentType: string
{
    case Pdf = 'pdf';
    case Quotation = 'quotation';
    case QuotationPdf = 'quotation_pdf';
    case Invoice = 'invoice';
    case InvoicePdf = 'invoice_pdf';
    case Statement = 'statement';
    case StatementPdf = 'statement_pdf';
    case Artwork = 'artwork';
    case ArtworkPdf = 'artwork_pdf';
    case JobCard = 'job_card';
    case JobCardPdf = 'job_card_pdf';
    case PayslipPdf = 'payslip_pdf';
    case Report = 'report';
    case Image = 'image';
    case Document = 'document';

    public function label(): string
    {
        return match ($this) {
            self::Pdf => __('PDF'),
            self::Quotation, self::QuotationPdf => __('Quotation'),
            self::Invoice, self::InvoicePdf => __('Invoice'),
            self::Statement, self::StatementPdf => __('Statement'),
            self::Artwork, self::ArtworkPdf => __('Artwork'),
            self::JobCard, self::JobCardPdf => __('Job card'),
            self::PayslipPdf => __('Payslip PDF'),
            self::Report => __('Report'),
            self::Image => __('Image'),
            self::Document => __('Document'),
        };
    }
}
