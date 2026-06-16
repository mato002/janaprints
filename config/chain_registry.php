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
            'rule_types' => ['procurement_approval'],
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
        'purchase_order' => ['label' => 'Purchase Order', 'module' => 'procurement', 'rule_type' => 'procurement_approval'],
        'payment' => ['label' => 'Payment', 'module' => 'finance', 'rule_type' => 'payment_approval'],
        'payroll_run' => ['label' => 'Payroll Run', 'module' => 'hr', 'rule_type' => 'payroll_approval'],
    ],

];
