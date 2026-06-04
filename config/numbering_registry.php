<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Governed document types (Phase S1C)
    |--------------------------------------------------------------------------
    */

    'document_types' => [
        'customer' => ['label' => 'Customers', 'type_code' => 'CUST'],
        'lead' => ['label' => 'Leads', 'type_code' => 'LEAD'],
        'quotation' => ['label' => 'Quotations', 'type_code' => 'QUOTE'],
        'artwork_request' => ['label' => 'Artwork Requests', 'type_code' => 'ART'],
        'sales_order' => ['label' => 'Sales Orders', 'type_code' => 'SO'],
        'job_card' => ['label' => 'Job Cards', 'type_code' => 'JOB'],
        'stock_receipt' => ['label' => 'Stock Receipts', 'type_code' => 'RCPT'],
        'stock_issue' => ['label' => 'Stock Issues', 'type_code' => 'ISSUE'],
        'invoice' => ['label' => 'Invoices', 'type_code' => 'INV'],
        'payment' => ['label' => 'Payments', 'type_code' => 'PAY'],
        'vendor' => ['label' => 'Vendors', 'type_code' => 'VEND'],
        'purchase_request' => ['label' => 'Purchase Requests', 'type_code' => 'PR'],
        'purchase_order' => ['label' => 'Purchase Orders', 'type_code' => 'PO'],
        'goods_receipt' => ['label' => 'Goods Receipts', 'type_code' => 'GRN'],
        'supplier_quotation' => ['label' => 'Supplier Quotations', 'type_code' => 'SQ'],
    ],

];
