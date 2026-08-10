<?php

/**
 * Master Data Center category registry.
 * Defines lookup domains, labels, and dependency rules for safe deactivation.
 */

return [

    'modules' => [

        'commercial' => [
            'label' => 'Sales',
            'categories' => [
                'customer_types' => [
                    'label' => 'Customer Types',
                    'dependencies' => [
                        ['table' => 'customers', 'column' => 'customer_type', 'match' => 'code', 'label' => 'Customers'],
                    ],
                ],
                'lead_sources' => [
                    'label' => 'Lead Sources',
                    'dependencies' => [
                        ['table' => 'leads', 'column' => 'lead_source_id', 'match' => 'lead_source_code', 'label' => 'Leads'],
                    ],
                ],
                'industry_types' => [
                    'label' => 'Industry Types',
                    'dependencies' => [],
                ],
                'payment_terms' => [
                    'label' => 'Payment Terms',
                    'dependencies' => [
                        ['table' => 'customers', 'column' => 'payment_terms', 'match' => 'name', 'label' => 'Customers'],
                        ['table' => 'vendors', 'column' => 'payment_terms', 'match' => 'name', 'label' => 'Vendors'],
                    ],
                ],
            ],
        ],

        'production' => [
            'label' => 'Production',
            'categories' => [
                'job_types' => ['label' => 'Job Types', 'dependencies' => []],
                'production_stages' => ['label' => 'Production Stages', 'dependencies' => []],
                'machine_types' => ['label' => 'Machine Types', 'dependencies' => []],
                'artwork_statuses' => ['label' => 'Artwork Statuses', 'dependencies' => []],
                'qc_statuses' => ['label' => 'QC Statuses', 'dependencies' => []],
            ],
        ],

        'supply_chain' => [
            'label' => 'Inventory',
            'categories' => [
                'vendor_categories' => ['label' => 'Vendor Categories', 'dependencies' => []],
                'purchase_types' => ['label' => 'Purchase Types', 'dependencies' => []],
                'delivery_methods' => ['label' => 'Delivery Methods', 'dependencies' => []],
                'warehouse_types' => ['label' => 'Warehouse Types', 'dependencies' => []],
            ],
        ],

        'inventory' => [
            'label' => 'Inventory',
            'categories' => [
                'item_categories' => ['label' => 'Item Categories', 'dependencies' => []],
                'item_brands' => ['label' => 'Item Brands', 'dependencies' => []],
                'units_of_measure' => [
                    'label' => 'Units Of Measure',
                    'dependencies' => [
                        ['table' => 'inventory_items', 'column' => 'unit_of_measure_id', 'match' => 'uom_code', 'label' => 'Inventory Items'],
                    ],
                ],
                'stock_statuses' => ['label' => 'Stock Statuses', 'dependencies' => []],
            ],
        ],

        'accounting' => [
            'label' => 'Accounting',
            'categories' => [
                'tax_codes' => ['label' => 'Tax Codes', 'dependencies' => []],
                'tax_categories' => ['label' => 'Tax Categories', 'dependencies' => []],
                'currency_codes' => ['label' => 'Currency Codes', 'dependencies' => []],
                'payment_methods' => ['label' => 'Payment Methods', 'dependencies' => []],
            ],
        ],

        'hr' => [
            'label' => 'HR',
            'categories' => [
                'employee_types' => ['label' => 'Employee Types', 'dependencies' => []],
                'contract_types' => ['label' => 'Contract Types', 'dependencies' => []],
                'leave_types' => ['label' => 'Leave Types', 'dependencies' => []],
                'shift_types' => ['label' => 'Shift Types', 'dependencies' => []],
            ],
        ],

        'communications' => [
            'label' => 'Communications',
            'categories' => [
                'communication_types' => ['label' => 'Communication Types', 'dependencies' => []],
                'campaign_types' => ['label' => 'Campaign Types', 'dependencies' => []],
                'message_categories' => ['label' => 'Message Categories', 'dependencies' => []],
            ],
        ],

    ],

];
