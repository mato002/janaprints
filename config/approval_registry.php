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

        'purchase_request_approval' => [
            'label' => 'Purchase Request Approval',
            'description' => 'Require approval for purchase requests above amount thresholds.',
            'metric' => 'amount',
            'default_permission' => 'procurement.requests.approve',
            'example_tiers' => [10000, 50000, 100000],
        ],

        'rfq_approval' => [
            'label' => 'RFQ Approval',
            'description' => 'Require approval before RFQs are issued to suppliers.',
            'metric' => 'amount',
            'default_permission' => 'procurement.rfq.edit',
            'example_tiers' => [25000, 75000, 150000],
        ],

        'goods_receipt_approval' => [
            'label' => 'Goods Receipt Approval',
            'description' => 'Require approval for goods receipts above amount thresholds.',
            'metric' => 'amount',
            'default_permission' => 'procurement.orders.receive',
            'example_tiers' => [25000, 75000, 150000],
        ],

        'vendor_invoice_approval' => [
            'label' => 'Vendor Invoice Approval',
            'description' => 'Require approval for supplier bills above amount thresholds.',
            'metric' => 'amount',
            'default_permission' => 'payables.bills.approve',
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

        'payroll_approval' => [
            'label' => 'Payroll Approval',
            'description' => 'Require approval for payroll runs above net pay thresholds.',
            'metric' => 'amount',
            'default_permission' => 'hr.payroll.approve',
            'example_tiers' => [100000, 250000, 500000],
        ],

    ],

];
