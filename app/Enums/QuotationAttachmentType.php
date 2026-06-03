<?php

namespace App\Enums;

enum QuotationAttachmentType: string
{
    case Pdf = 'pdf';
    case Artwork = 'artwork';
    case Document = 'document';
}
