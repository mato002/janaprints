<?php

return [

    'logo_path' => '/images/jp-documents-logo.png',

    'company' => [
        'name' => env('DOCUMENT_COMPANY_NAME', config('site.local.company_name', 'Jana Prints')),
        'address' => env('DOCUMENT_COMPANY_ADDRESS', config('site.local.address')),
        'phone' => env('DOCUMENT_COMPANY_PHONE', config('site.local.phone')),
        'email' => env('DOCUMENT_COMPANY_EMAIL', config('site.local.email')),
        'website' => env('DOCUMENT_COMPANY_WEBSITE', config('site.url')),
    ],

    'payment' => [
        'mpesa_paybill' => env('DOCUMENT_MPESA_PAYBILL'),
        'mpesa_account' => env('DOCUMENT_MPESA_ACCOUNT', 'JANA PRINTS'),
        'cheque_payable_to' => env('DOCUMENT_CHEQUE_PAYABLE_TO', 'Jana Prints Ltd'),
        'bank_name' => env('DOCUMENT_BANK_NAME'),
        'bank_branch' => env('DOCUMENT_BANK_BRANCH'),
        'bank_account' => env('DOCUMENT_BANK_ACCOUNT_NUMBER', env('DOCUMENT_BANK_ACCOUNT')),
        'bank_account_name' => env('DOCUMENT_BANK_ACCOUNT_NAME', 'Jana Prints Ltd'),
    ],

    'terms' => [
        'quotation' => 'This quotation is valid until the date shown above. Prices exclude delivery unless stated. Acceptance confirms scope and artwork approval.',
        'invoice' => 'Payment is due by the due date shown above. Please quote the invoice number on all remittances.',
        'receipt_acknowledgement' => 'We confirm receipt of the above payment. Thank you for your prompt settlement.',
    ],

];
