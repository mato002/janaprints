<?php

/**
 * Logical account keys used by posting templates.
 * Default GL account codes are resolved per company when no mapping exists.
 */
return [
    'raw_materials' => ['default_code' => '1410', 'label' => 'Raw Materials'],
    'wip' => ['default_code' => '1420', 'label' => 'Work In Progress'],
    'finished_goods' => ['default_code' => '1430', 'label' => 'Finished Goods'],
    'cash' => ['default_code' => '1110', 'label' => 'Petty Cash'],
    'bank' => ['default_code' => '1210', 'label' => 'Equity Bank'],
    'trade_receivables' => ['default_code' => '1300', 'label' => 'Accounts Receivable'],
    'trade_payables' => ['default_code' => '2100', 'label' => 'Accounts Payable'],
    'vat_payable' => ['default_code' => '2210', 'label' => 'VAT Payable'],
    'customer_deposits' => ['default_code' => '2400', 'label' => 'Customer Deposits'],
    'mpesa_clearing' => ['default_code' => '1210', 'label' => 'M-Pesa Clearing'],
    'printing_revenue' => ['default_code' => '4110', 'label' => 'Banner Printing'],
    'material_consumption' => ['default_code' => '5100', 'label' => 'Paper Consumption'],
    'wip_consumption' => ['default_code' => '5200', 'label' => 'Ink Consumption'],
    'operating_expense' => ['default_code' => '6100', 'label' => 'Salaries'],
];
