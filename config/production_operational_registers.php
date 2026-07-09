<?php

return [
    'title' => 'Operational Registers',
    'description' => 'Executive operational registers generated from live ERP data — read-only intelligence.',

    'registers' => [
        'daily_sales' => [
            'label' => 'Daily Sales Register',
            'permission' => 'reports.view|intelligence.commercial.view',
        ],
        'digital' => [
            'label' => 'Digital Department Register',
            'department' => 'digital',
            'permission' => 'reports.view|intelligence.production.view|production.queue.view',
        ],
        'offset' => [
            'label' => 'Offset Department Register',
            'department' => 'offset',
            'permission' => 'reports.view|intelligence.production.view|production.queue.view',
        ],
        'outsource' => [
            'label' => 'Outsourced Jobs Register',
            'department' => 'outsource',
            'permission' => 'reports.view|intelligence.production.view|production.queue.view',
        ],
        'large_format' => [
            'label' => 'Large Format Register',
            'department' => 'large_format',
            'permission' => 'reports.view|intelligence.production.view|production.queue.view',
        ],
        'finishing' => [
            'label' => 'Finishing Register',
            'department' => 'finishing',
            'permission' => 'reports.view|intelligence.production.view|production.queue.view',
        ],
        'production_summary' => [
            'label' => 'Production Summary Register',
            'permission' => 'reports.view|intelligence.production.view',
        ],
        'machine_utilisation' => [
            'label' => 'Machine Utilisation Register',
            'permission' => 'reports.view|intelligence.production.view',
        ],
        'operator_productivity' => [
            'label' => 'Operator Productivity Register',
            'permission' => 'reports.view|intelligence.production.view',
        ],
        'department_performance' => [
            'label' => 'Department Performance Register',
            'permission' => 'reports.view|intelligence.production.view',
        ],
        'customer_summary' => [
            'label' => 'Customer Production Summary',
            'permission' => 'reports.view|intelligence.production.view',
        ],
        'sales_register' => [
            'label' => 'Sales Register',
            'permission' => 'reports.view|intelligence.commercial.view',
        ],
    ],

    'presets' => [
        'today' => 'Today',
        'yesterday' => 'Yesterday',
        'week' => 'This week',
        'month' => 'This month',
        'quarter' => 'This quarter',
        'year' => 'This year',
    ],
];
