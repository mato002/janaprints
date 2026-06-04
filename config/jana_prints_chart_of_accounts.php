<?php

/**
 * Jana Prints production chart of accounts (Phase F1A).
 * Nodes are processed in order; parent_code must refer to an earlier group or account.
 */
return [
    'version' => 'f1a',

    'nodes' => [
        // —— Assets ——
        ['type' => 'asset', 'kind' => 'group', 'code' => '1000', 'name' => 'Assets', 'parent_code' => null, 'sort' => 10],
        ['type' => 'asset', 'kind' => 'group', 'code' => '1100', 'name' => 'Cash', 'parent_code' => '1000', 'sort' => 11],
        ['type' => 'asset', 'kind' => 'account', 'code' => '1110', 'name' => 'Petty Cash', 'parent_code' => '1100', 'postable' => true, 'sort' => 12],
        ['type' => 'asset', 'kind' => 'account', 'code' => '1120', 'name' => 'Cash Till', 'parent_code' => '1100', 'postable' => true, 'sort' => 13],
        ['type' => 'asset', 'kind' => 'group', 'code' => '1200', 'name' => 'Bank', 'parent_code' => '1000', 'sort' => 20],
        ['type' => 'asset', 'kind' => 'account', 'code' => '1210', 'name' => 'Equity Bank', 'parent_code' => '1200', 'postable' => true, 'sort' => 21],
        ['type' => 'asset', 'kind' => 'account', 'code' => '1220', 'name' => 'KCB Bank', 'parent_code' => '1200', 'postable' => true, 'sort' => 22],
        ['type' => 'asset', 'kind' => 'account', 'code' => '1230', 'name' => 'NCBA Bank', 'parent_code' => '1200', 'postable' => true, 'sort' => 23],
        ['type' => 'asset', 'kind' => 'account', 'code' => '1300', 'name' => 'Accounts Receivable', 'parent_code' => '1000', 'postable' => true, 'sort' => 30],
        ['type' => 'asset', 'kind' => 'account', 'code' => '1400', 'name' => 'Inventory', 'parent_code' => '1000', 'postable' => false, 'sort' => 40],
        ['type' => 'asset', 'kind' => 'account', 'code' => '1410', 'name' => 'Raw Materials', 'parent_code' => '1400', 'postable' => true, 'sort' => 41],
        ['type' => 'asset', 'kind' => 'account', 'code' => '1420', 'name' => 'Work In Progress', 'parent_code' => '1400', 'postable' => true, 'sort' => 42],
        ['type' => 'asset', 'kind' => 'account', 'code' => '1430', 'name' => 'Finished Goods', 'parent_code' => '1400', 'postable' => true, 'sort' => 43],
        ['type' => 'asset', 'kind' => 'group', 'code' => '1500', 'name' => 'Fixed Assets', 'parent_code' => '1000', 'sort' => 50],
        ['type' => 'asset', 'kind' => 'account', 'code' => '1510', 'name' => 'Computers', 'parent_code' => '1500', 'postable' => true, 'sort' => 51],
        ['type' => 'asset', 'kind' => 'account', 'code' => '1520', 'name' => 'Printers', 'parent_code' => '1500', 'postable' => true, 'sort' => 52],
        ['type' => 'asset', 'kind' => 'account', 'code' => '1530', 'name' => 'Machinery', 'parent_code' => '1500', 'postable' => true, 'sort' => 53],
        ['type' => 'asset', 'kind' => 'account', 'code' => '1540', 'name' => 'Vehicles', 'parent_code' => '1500', 'postable' => true, 'sort' => 54],
        ['type' => 'asset', 'kind' => 'account', 'code' => '1550', 'name' => 'Accumulated Depreciation', 'parent_code' => '1500', 'postable' => true, 'sort' => 55],
        ['type' => 'asset', 'kind' => 'account', 'code' => '1600', 'name' => 'Prepayments', 'parent_code' => '1000', 'postable' => true, 'sort' => 60],

        // —— Liabilities ——
        ['type' => 'liability', 'kind' => 'group', 'code' => '2000', 'name' => 'Liabilities', 'parent_code' => null, 'sort' => 10],
        ['type' => 'liability', 'kind' => 'account', 'code' => '2100', 'name' => 'Accounts Payable', 'parent_code' => '2000', 'postable' => true, 'sort' => 20],
        ['type' => 'liability', 'kind' => 'account', 'code' => '2200', 'name' => 'Tax Payable', 'parent_code' => '2000', 'postable' => false, 'sort' => 30],
        ['type' => 'liability', 'kind' => 'account', 'code' => '2210', 'name' => 'VAT Payable', 'parent_code' => '2200', 'postable' => true, 'sort' => 31],
        ['type' => 'liability', 'kind' => 'account', 'code' => '2220', 'name' => 'PAYE Payable', 'parent_code' => '2200', 'postable' => true, 'sort' => 32],
        ['type' => 'liability', 'kind' => 'account', 'code' => '2300', 'name' => 'Accrued Expenses', 'parent_code' => '2000', 'postable' => true, 'sort' => 40],
        ['type' => 'liability', 'kind' => 'account', 'code' => '2400', 'name' => 'Customer Deposits', 'parent_code' => '2000', 'postable' => true, 'sort' => 50],

        // —— Equity ——
        ['type' => 'equity', 'kind' => 'group', 'code' => '3000', 'name' => 'Equity', 'parent_code' => null, 'sort' => 10],
        ['type' => 'equity', 'kind' => 'account', 'code' => '3100', 'name' => 'Share Capital', 'parent_code' => '3000', 'postable' => true, 'sort' => 20],
        ['type' => 'equity', 'kind' => 'account', 'code' => '3200', 'name' => 'Retained Earnings', 'parent_code' => '3000', 'postable' => true, 'sort' => 30],
        ['type' => 'equity', 'kind' => 'account', 'code' => '3300', 'name' => 'Current Year Earnings', 'parent_code' => '3000', 'postable' => true, 'sort' => 40],

        // —— Revenue ——
        ['type' => 'revenue', 'kind' => 'group', 'code' => '4000', 'name' => 'Revenue', 'parent_code' => null, 'sort' => 10],
        ['type' => 'revenue', 'kind' => 'account', 'code' => '4100', 'name' => 'Printing Revenue', 'parent_code' => '4000', 'postable' => false, 'sort' => 20],
        ['type' => 'revenue', 'kind' => 'account', 'code' => '4110', 'name' => 'Banner Printing', 'parent_code' => '4100', 'postable' => true, 'sort' => 21],
        ['type' => 'revenue', 'kind' => 'account', 'code' => '4120', 'name' => 'Business Cards', 'parent_code' => '4100', 'postable' => true, 'sort' => 22],
        ['type' => 'revenue', 'kind' => 'account', 'code' => '4130', 'name' => 'Flyers', 'parent_code' => '4100', 'postable' => true, 'sort' => 23],
        ['type' => 'revenue', 'kind' => 'account', 'code' => '4140', 'name' => 'Posters', 'parent_code' => '4100', 'postable' => true, 'sort' => 24],
        ['type' => 'revenue', 'kind' => 'account', 'code' => '4150', 'name' => 'Large Format', 'parent_code' => '4100', 'postable' => true, 'sort' => 25],
        ['type' => 'revenue', 'kind' => 'account', 'code' => '4200', 'name' => 'Design Revenue', 'parent_code' => '4000', 'postable' => true, 'sort' => 30],
        ['type' => 'revenue', 'kind' => 'account', 'code' => '4300', 'name' => 'Delivery Revenue', 'parent_code' => '4000', 'postable' => true, 'sort' => 40],

        // —— Cost of sales ——
        ['type' => 'cost_of_sales', 'kind' => 'group', 'code' => '5000', 'name' => 'Cost Of Sales', 'parent_code' => null, 'sort' => 10],
        ['type' => 'cost_of_sales', 'kind' => 'account', 'code' => '5100', 'name' => 'Paper Consumption', 'parent_code' => '5000', 'postable' => true, 'sort' => 20],
        ['type' => 'cost_of_sales', 'kind' => 'account', 'code' => '5200', 'name' => 'Ink Consumption', 'parent_code' => '5000', 'postable' => true, 'sort' => 30],
        ['type' => 'cost_of_sales', 'kind' => 'account', 'code' => '5300', 'name' => 'Lamination Materials', 'parent_code' => '5000', 'postable' => true, 'sort' => 40],
        ['type' => 'cost_of_sales', 'kind' => 'account', 'code' => '5400', 'name' => 'Outsourced Printing', 'parent_code' => '5000', 'postable' => true, 'sort' => 50],
        ['type' => 'cost_of_sales', 'kind' => 'account', 'code' => '5500', 'name' => 'Direct Labour', 'parent_code' => '5000', 'postable' => true, 'sort' => 60],

        // —— Operating expenses ——
        ['type' => 'expense', 'kind' => 'group', 'code' => '6000', 'name' => 'Operating Expenses', 'parent_code' => null, 'sort' => 10],
        ['type' => 'expense', 'kind' => 'account', 'code' => '6100', 'name' => 'Salaries', 'parent_code' => '6000', 'postable' => true, 'sort' => 20],
        ['type' => 'expense', 'kind' => 'account', 'code' => '6200', 'name' => 'Rent', 'parent_code' => '6000', 'postable' => true, 'sort' => 30],
        ['type' => 'expense', 'kind' => 'account', 'code' => '6300', 'name' => 'Electricity', 'parent_code' => '6000', 'postable' => true, 'sort' => 40],
        ['type' => 'expense', 'kind' => 'account', 'code' => '6400', 'name' => 'Water', 'parent_code' => '6000', 'postable' => true, 'sort' => 50],
        ['type' => 'expense', 'kind' => 'account', 'code' => '6500', 'name' => 'Internet', 'parent_code' => '6000', 'postable' => true, 'sort' => 60],
        ['type' => 'expense', 'kind' => 'account', 'code' => '6600', 'name' => 'Fuel', 'parent_code' => '6000', 'postable' => true, 'sort' => 70],
        ['type' => 'expense', 'kind' => 'account', 'code' => '6700', 'name' => 'Repairs', 'parent_code' => '6000', 'postable' => true, 'sort' => 80],
        ['type' => 'expense', 'kind' => 'account', 'code' => '6710', 'name' => 'Depreciation Expense', 'parent_code' => '6000', 'postable' => true, 'sort' => 85],
        ['type' => 'expense', 'kind' => 'account', 'code' => '6800', 'name' => 'Marketing', 'parent_code' => '6000', 'postable' => true, 'sort' => 90],
        ['type' => 'expense', 'kind' => 'account', 'code' => '6900', 'name' => 'Office Expenses', 'parent_code' => '6000', 'postable' => true, 'sort' => 100],
    ],

    'expected' => [
        'groups' => 9,
        'accounts' => 46,
        'postable_accounts' => 43,
        'header_accounts' => 3,
    ],
];
