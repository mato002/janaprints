<?php

/**
 * Reports & Intelligence (RI-0) — read-only placeholder pages.
 * Widgets show no values until module data sources are wired in later phases.
 */

return [

    'executive' => [
        'title' => 'Executive Dashboard',
        'description' => 'Cross-module executive summary for leadership review.',
        'permission' => 'reports.view',
        'widgets' => [
            ['label' => 'Sales Today', 'icon' => 'currency-dollar'],
            ['label' => 'Open Quotes', 'icon' => 'document-text'],
            ['label' => 'Active Jobs', 'icon' => 'cog'],
            ['label' => 'Receivables', 'icon' => 'cash'],
            ['label' => 'Inventory Alerts', 'icon' => 'exclamation'],
        ],
    ],

    'commercial' => [
        'title' => 'Sales Reports',
        'description' => 'Sales, CRM, and quotation analytics.',
        'permission' => 'reports.view',
        'widgets' => [
            ['label' => 'Leads', 'icon' => 'sparkles'],
            ['label' => 'Customers', 'icon' => 'user-circle'],
            ['label' => 'Quotations', 'icon' => 'document-text'],
            ['label' => 'Sales Orders', 'icon' => 'clipboard-list'],
        ],
    ],

    'inventory' => [
        'title' => 'Inventory Reports',
        'description' => 'Stock movement and valuation reports.',
        'permission' => 'reports.view',
        'widgets' => [
            ['label' => 'Inventory Value', 'icon' => 'currency-dollar'],
            ['label' => 'Low Stock', 'icon' => 'exclamation'],
            ['label' => 'Stock Receipts', 'icon' => 'inbox'],
            ['label' => 'Stock Issues', 'icon' => 'cube'],
        ],
    ],

    'procurement' => [
        'title' => 'Procurement Reports',
        'description' => 'Purchasing and supplier performance.',
        'permission' => 'reports.view',
        'widgets' => [
            ['label' => 'Purchase Requests', 'icon' => 'document-text'],
            ['label' => 'Purchase Orders', 'icon' => 'clipboard-list'],
            ['label' => 'Goods Receipts', 'icon' => 'inbox'],
            ['label' => 'Vendors', 'icon' => 'truck'],
        ],
    ],

    'accounting' => [
        'title' => 'Accounting Reports',
        'description' => 'Financial and management reports.',
        'permission' => 'reports.view',
        'widgets' => [
            ['label' => 'Receivables', 'icon' => 'cash'],
            ['label' => 'Payables', 'icon' => 'currency-dollar'],
            ['label' => 'Revenue', 'icon' => 'chart-bar'],
            ['label' => 'Expenses', 'icon' => 'chart-pie'],
        ],
    ],

    'hr' => [
        'title' => 'HR Reports',
        'description' => 'Workforce and payroll analytics.',
        'permission' => 'reports.view',
        'widgets' => [
            ['label' => 'Employees', 'icon' => 'user-circle'],
            ['label' => 'Attendance', 'icon' => 'clock', 'placeholder' => true],
            ['label' => 'Leave', 'icon' => 'calendar', 'placeholder' => true],
            ['label' => 'Payroll', 'icon' => 'currency-dollar', 'placeholder' => true],
        ],
    ],

    'kpi' => [
        'title' => 'KPI Center',
        'description' => 'Configurable KPI scorecards across modules.',
        'permission' => 'kpi.view',
        'widgets' => [
            ['label' => 'Commercial KPIs', 'icon' => 'document-text'],
            ['label' => 'Production KPIs', 'icon' => 'cog'],
            ['label' => 'Inventory KPIs', 'icon' => 'cube'],
            ['label' => 'Finance KPIs', 'icon' => 'currency-dollar'],
            ['label' => 'HR KPIs', 'icon' => 'identification'],
        ],
    ],

];
