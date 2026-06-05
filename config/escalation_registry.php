<?php

return [

    'workflows' => [
        'purchase_order' => [
            'label' => 'Purchase Order',
            'module' => 'procurement',
            'approval_rule_type' => 'procurement_approval',
            'document_type' => 'purchase_order',
        ],
        'inventory_adjustment' => [
            'label' => 'Inventory Adjustment',
            'module' => 'inventory',
            'approval_rule_type' => 'stock_adjustment_approval',
            'document_type' => 'stock_adjustment',
        ],
        'quotation' => [
            'label' => 'Quotation',
            'module' => 'sales',
            'approval_rule_type' => 'quotation_approval',
            'document_type' => 'quotation',
        ],
        'payment' => [
            'label' => 'Payment',
            'module' => 'finance',
            'approval_rule_type' => 'payment_approval',
            'document_type' => 'payment',
        ],
    ],

    'waiting_period_presets' => [
        4 => '4 Hours',
        8 => '8 Hours',
        24 => '24 Hours',
        48 => '48 Hours',
        72 => '72 Hours',
        168 => '1 Week',
    ],

];
