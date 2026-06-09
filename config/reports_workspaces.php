<?php

return [
    'hub' => [
        [
            'label' => 'Executive',
            'description' => 'Cross-module executive summary and branch intelligence.',
            'route' => 'admin.workspaces.reports.section',
            'route_params' => ['section' => 'executive'],
            'permission' => 'reports.view',
            'icon' => 'chart-pie',
            'active_routes' => ['admin.workspaces.reports.section:executive', 'admin.reports.executive', 'admin.reports.branch360'],
        ],
        [
            'label' => 'Commercial',
            'description' => 'Commercial reports and customer intelligence.',
            'route' => 'admin.workspaces.reports.section',
            'route_params' => ['section' => 'commercial'],
            'permission' => 'reports.view',
            'icon' => 'shopping-cart',
            'active_routes' => ['admin.workspaces.reports.section:commercial', 'admin.reports.commercial', 'admin.reports.commercial360', 'commercial.reports.*'],
        ],
        [
            'label' => 'Production',
            'description' => 'Production throughput and delay intelligence.',
            'route' => 'admin.workspaces.reports.section',
            'route_params' => ['section' => 'production'],
            'permission' => 'reports.view',
            'icon' => 'cog',
            'active_routes' => ['admin.workspaces.reports.section:production', 'admin.reports.production', 'admin.reports.production360'],
        ],
        [
            'label' => 'Inventory',
            'description' => 'Inventory reports and stock intelligence.',
            'route' => 'admin.workspaces.reports.section',
            'route_params' => ['section' => 'inventory'],
            'permission' => 'reports.inventory.view|reports.view',
            'icon' => 'cube',
            'active_routes' => ['admin.workspaces.reports.section:inventory', 'admin.inventory.reports.*', 'admin.reports.inventory360'],
        ],
        [
            'label' => 'Procurement',
            'description' => 'Procurement reports and supplier intelligence.',
            'route' => 'admin.workspaces.reports.section',
            'route_params' => ['section' => 'procurement'],
            'permission' => 'reports.procurement.view|reports.view',
            'icon' => 'truck',
            'active_routes' => ['admin.workspaces.reports.section:procurement', 'admin.procurement.reports.*', 'admin.reports.procurement360'],
        ],
        [
            'label' => 'Finance',
            'description' => 'Accounting reports and financial intelligence.',
            'route' => 'admin.workspaces.reports.section',
            'route_params' => ['section' => 'finance'],
            'permission' => 'reports.view',
            'icon' => 'currency-dollar',
            'active_routes' => ['admin.workspaces.reports.section:finance', 'admin.reports.accounting', 'admin.reports.financial360'],
        ],
        [
            'label' => 'HR',
            'description' => 'Workforce and payroll analytics.',
            'route' => 'admin.workspaces.reports.section',
            'route_params' => ['section' => 'hr'],
            'permission' => 'reports.view',
            'icon' => 'identification',
            'active_routes' => ['admin.workspaces.reports.section:hr', 'admin.reports.hr'],
        ],
        [
            'label' => 'KPI Center',
            'description' => 'Configurable KPI scorecards.',
            'route' => 'admin.workspaces.reports.section',
            'route_params' => ['section' => 'kpi-center'],
            'permission' => 'kpi.view|reports.view',
            'icon' => 'badge-check',
            'active_routes' => ['admin.workspaces.reports.section:kpi-center', 'admin.reports.kpi'],
        ],
    ],

    'sections' => [
        'executive' => [
            'title' => 'Executive',
            'description' => 'Executive dashboards and branch intelligence.',
            'icon' => 'chart-pie',
            'groups' => [[
                'label' => 'Executive',
                'items' => [
                    ['key' => 'executive-dashboard', 'label' => 'Executive Dashboard', 'description' => 'Cross-module executive summary.', 'route' => 'admin.reports.executive', 'permission' => 'reports.view', 'icon' => 'chart-pie', 'active_routes' => ['admin.reports.executive']],
                    ['key' => 'branch-360', 'label' => 'Branch 360', 'description' => 'Branch comparison and performance profiles.', 'route' => 'admin.reports.branch360', 'permission' => 'intelligence.branch.view|reports.view', 'icon' => 'office-building', 'active_routes' => ['admin.reports.branch360']],
                ],
            ]],
        ],
        'commercial' => [
            'title' => 'Commercial',
            'description' => 'Commercial reports and intelligence.',
            'icon' => 'shopping-cart',
            'groups' => [[
                'label' => 'Commercial',
                'items' => [
                    ['key' => 'commercial-reports', 'label' => 'Commercial Reports', 'description' => 'Hub for departmental commercial reports.', 'route' => 'admin.reports.commercial', 'permission' => 'reports.view', 'icon' => 'document-text', 'active_routes' => ['admin.reports.commercial', 'commercial.reports.*']],
                    ['key' => 'commercial-360', 'label' => 'Commercial 360', 'description' => 'Sales and customer management intelligence.', 'route' => 'admin.reports.commercial360', 'permission' => 'intelligence.commercial.view|reports.view', 'icon' => 'document-text', 'active_routes' => ['admin.reports.commercial360']],
                ],
            ]],
        ],
        'production' => [
            'title' => 'Production',
            'description' => 'Production reports and intelligence.',
            'icon' => 'cog',
            'groups' => [[
                'label' => 'Production',
                'items' => [
                    ['key' => 'production-reports', 'label' => 'Production Reports', 'description' => 'Throughput, downtime, and job metrics.', 'route' => 'admin.reports.production', 'permission' => 'reports.view', 'icon' => 'chart-bar', 'active_routes' => ['admin.reports.production', 'admin.reports.production.print']],
                    ['key' => 'production-360', 'label' => 'Production 360', 'description' => 'Production pipeline and delay intelligence.', 'route' => 'admin.reports.production360', 'permission' => 'intelligence.production.view|reports.view', 'icon' => 'sparkles', 'active_routes' => ['admin.reports.production360']],
                ],
            ]],
        ],
        'inventory' => [
            'title' => 'Inventory',
            'description' => 'Inventory reports and intelligence.',
            'icon' => 'cube',
            'groups' => [[
                'label' => 'Inventory',
                'items' => [
                    ['key' => 'inventory-reports', 'label' => 'Inventory Reports', 'description' => 'Stock movement and valuation reports.', 'route' => 'admin.inventory.reports.index', 'permission' => 'reports.inventory.view', 'icon' => 'cube', 'active_routes' => ['admin.inventory.reports.*']],
                    ['key' => 'inventory-360', 'label' => 'Inventory 360', 'description' => 'Stock health, valuation, and movement intelligence.', 'route' => 'admin.reports.inventory360', 'permission' => 'intelligence.inventory.view|reports.view', 'icon' => 'cube', 'active_routes' => ['admin.reports.inventory360']],
                ],
            ]],
        ],
        'procurement' => [
            'title' => 'Procurement',
            'description' => 'Procurement reports and intelligence.',
            'icon' => 'truck',
            'groups' => [[
                'label' => 'Procurement',
                'items' => [
                    ['key' => 'procurement-reports', 'label' => 'Procurement Reports', 'description' => 'Purchasing and supplier performance.', 'route' => 'admin.procurement.reports.index', 'permission' => 'reports.procurement.view', 'icon' => 'truck', 'active_routes' => ['admin.procurement.reports.*']],
                    ['key' => 'procurement-360', 'label' => 'Procurement 360', 'description' => 'Purchasing and vendor performance intelligence.', 'route' => 'admin.reports.procurement360', 'permission' => 'intelligence.vendor.view|reports.view', 'icon' => 'truck', 'active_routes' => ['admin.reports.procurement360']],
                ],
            ]],
        ],
        'finance' => [
            'title' => 'Finance',
            'description' => 'Financial reports and intelligence.',
            'icon' => 'currency-dollar',
            'groups' => [[
                'label' => 'Finance',
                'items' => [
                    ['key' => 'accounting-reports', 'label' => 'Accounting Reports', 'description' => 'Financial and management reports.', 'route' => 'admin.reports.accounting', 'permission' => 'reports.view', 'icon' => 'currency-dollar', 'active_routes' => ['admin.reports.accounting']],
                    ['key' => 'financial-360', 'label' => 'Financial 360', 'description' => 'Revenue, receivables, and payables intelligence.', 'route' => 'admin.reports.financial360', 'permission' => 'intelligence.financial.view|reports.view', 'icon' => 'currency-dollar', 'active_routes' => ['admin.reports.financial360']],
                ],
            ]],
        ],
        'hr' => [
            'title' => 'HR',
            'description' => 'HR analytics and reporting.',
            'icon' => 'identification',
            'groups' => [[
                'label' => 'HR',
                'items' => [
                    ['key' => 'hr-reports', 'label' => 'HR Reports', 'description' => 'Workforce and payroll analytics.', 'route' => 'admin.reports.hr', 'permission' => 'reports.view', 'icon' => 'identification', 'active_routes' => ['admin.reports.hr']],
                ],
            ]],
        ],
        'kpi-center' => [
            'title' => 'KPI Center',
            'description' => 'KPI scorecards and targets.',
            'icon' => 'badge-check',
            'groups' => [[
                'label' => 'KPI Center',
                'items' => [
                    ['key' => 'kpi-scorecards', 'label' => 'KPI Scorecards', 'description' => 'Configurable KPI scorecards.', 'route' => 'admin.reports.kpi', 'permission' => 'kpi.view|reports.view', 'icon' => 'badge-check', 'active_routes' => ['admin.reports.kpi']],
                ],
            ]],
        ],
    ],
];
