<?php

return [

    'modules' => [
        'sales' => [
            'label' => 'Sales',
            'rule_types' => ['quotation_approval', 'discount_approval'],
        ],
        'inventory' => [
            'label' => 'Inventory',
            'rule_types' => ['stock_adjustment_approval'],
        ],
        'procurement' => [
            'label' => 'Procurement',
            'rule_types' => ['purchase_request_approval', 'procurement_approval', 'rfq_approval', 'goods_receipt_approval', 'vendor_invoice_approval'],
        ],
        'finance' => [
            'label' => 'Finance',
            'rule_types' => ['payment_approval'],
        ],
        'hr' => [
            'label' => 'Human Resources',
            'rule_types' => ['payroll_approval'],
        ],
    ],

    'document_types' => [
        'quotation' => ['label' => 'Quotation', 'module' => 'sales', 'rule_type' => 'quotation_approval'],
        'sales_order' => ['label' => 'Sales Order', 'module' => 'sales', 'rule_type' => 'quotation_approval'],
        'discount' => ['label' => 'Discount', 'module' => 'sales', 'rule_type' => 'discount_approval'],
        'stock_adjustment' => ['label' => 'Stock Adjustment', 'module' => 'inventory', 'rule_type' => 'stock_adjustment_approval'],
        'purchase_request' => ['label' => 'Purchase Request', 'module' => 'procurement', 'rule_type' => 'purchase_request_approval'],
        'purchase_order' => ['label' => 'Purchase Order', 'module' => 'procurement', 'rule_type' => 'procurement_approval'],
        'rfq' => ['label' => 'RFQ', 'module' => 'procurement', 'rule_type' => 'rfq_approval'],
        'goods_receipt' => ['label' => 'Goods Receipt', 'module' => 'procurement', 'rule_type' => 'goods_receipt_approval'],
        'vendor_invoice' => ['label' => 'Vendor Invoice', 'module' => 'procurement', 'rule_type' => 'vendor_invoice_approval'],
        'payment' => ['label' => 'Payment', 'module' => 'finance', 'rule_type' => 'payment_approval'],
        'payroll_run' => ['label' => 'Payroll Run', 'module' => 'hr', 'rule_type' => 'payroll_approval'],
    ],

];
