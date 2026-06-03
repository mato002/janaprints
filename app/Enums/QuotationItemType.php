<?php

namespace App\Enums;

enum QuotationItemType: string
{
    case Product = 'product';
    case Service = 'service';
    case Material = 'material';
    case Other = 'other';
}
