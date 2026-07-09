<?php

namespace App\Support\Sales;

use App\Models\Sales\CustomerInvoice;

class CustomerInvoiceCreationResult
{
    public function __construct(
        public CustomerInvoice $invoice,
        public bool $wasExisting = false,
        public ?string $message = null,
    ) {}
}
