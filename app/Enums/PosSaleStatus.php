<?php

namespace App\Enums;

enum PosSaleStatus: string
{
    case Draft = 'draft';
    case Held = 'held';
    case Paid = 'paid';
    case PartiallyRefunded = 'partially_refunded';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';
}
