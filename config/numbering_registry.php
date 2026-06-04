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
        'credit_note' => ['label' => 'Credit Notes', 'type_code' => 'CN'],
        'payment' => ['label' => 'Payments', 'type_code' => 'PAY'],
        'supplier_bill' => ['label' => 'Supplier Bills', 'type_code' => 'SBILL'],
        'supplier_credit_note' => ['label' => 'Supplier Credit Notes', 'type_code' => 'SCN'],
        'supplier_payment' => ['label' => 'Supplier Payments', 'type_code' => 'SPAY'],
        'vendor' => ['label' => 'Vendors', 'type_code' => 'VEND'],
        'purchase_request' => ['label' => 'Purchase Requests', 'type_code' => 'PR'],
        'purchase_order' => ['label' => 'Purchase Orders', 'type_code' => 'PO'],
        'goods_receipt' => ['label' => 'Goods Receipts', 'type_code' => 'GRN'],
        'supplier_quotation' => ['label' => 'Supplier Quotations', 'type_code' => 'SQ'],
        'rfq' => ['label' => 'Request For Quotation', 'type_code' => 'RFQ'],
        'fixed_asset' => ['label' => 'Fixed Assets', 'type_code' => 'AST'],
        'journal' => ['label' => 'Journal Entries', 'type_code' => 'JE'],
    ],

];
