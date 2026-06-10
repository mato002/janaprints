<?php

return [

    'rule_types' => [

        'quotation_approval' => [
            'label' => 'Quotation Approval',
            'description' => 'Require approval when quotation totals exceed configured amount thresholds.',
            'metric' => 'amount',
            'default_permission' => 'quotations.approve',
            'example_tiers' => [50000, 100000, 500000],
        ],

        'discount_approval' => [
            'label' => 'Discount Approval',
            'description' => 'Require approval when discounts exceed configured percentage thresholds.',
            'metric' => 'percent',
            'default_permission' => 'quotations.approve',
            'example_tiers' => [5, 10, 20],
        ],

        'stock_adjustment_approval' => [
            'label' => 'Inventory Adjustment Approval',
            'description' => 'Require approval for stock adjustments above amount thresholds.',
            'metric' => 'amount',
            'default_permission' => 'inventory.adjust',
            'example_tiers' => [1000, 5000, 10000],
        ],

        'procurement_approval' => [
            'label' => 'Purchase Approval',
            'description' => 'Require approval for purchase orders above amount thresholds.',
            'metric' => 'amount',
            'default_permission' => null,
            'example_tiers' => [50000, 100000, 250000],
        ],

        'payment_approval' => [
            'label' => 'Payment Approval',
            'description' => 'Require approval for payments above amount thresholds.',
            'metric' => 'amount',
            'default_permission' => null,
            'example_tiers' => [100000, 250000, 500000],
        ],

        'calibration_rule_approval' => [
            'label' => 'Cost Calibration Approval',
            'description' => 'Require approval before activating Printing Intelligence calibration rule changes.',
            'metric' => 'percent',
            'default_permission' => 'printing.calibration.approve',
            'example_tiers' => [5, 10, 15],
        ],

    ],

];
