<?php

namespace App\Enums;

enum VendorType: string
{
    case Supplier = 'supplier';
    case ServiceProvider = 'service_provider';
    case Contractor = 'contractor';
    case Other = 'other';
}
