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
            'active_routes' => [
                'admin.workspaces.reports.section:commercial',
                'admin.reports.commercial',
                'admin.reports.commercial360',
                'admin.reports.commercial-intelligence',
                'admin.commercial.reports.*',
                'admin.commercial.pos.reports.*',
            ],
        ],
        [
            'label' => 'Production',
            'description' => 'Production throughput and delay intelligence.',
            'route' => 'admin.workspaces.reports.section',
            'route_params' => ['section' => 'production'],
            'permission' => 'reports.view|production.view|production.costing.view',
            'icon' => 'cog',
            'active_routes' => [
                'admin.workspaces.reports.section:production',
                'admin.reports.production',
                'admin.reports.production360',
                'admin.production.dashboard',
                'admin.production.costing.*',
            ],
        ],
        [
            'label' => 'Inventory',
            'description' => 'Inventory reports and stock intelligence.',
            'route' => 'admin.workspaces.reports.section',
            'route_params' => ['section' => 'inventory'],
            'permission' => 'reports.inventory.view|reports.view|inventory.intelligence.view',
            'icon' => 'cube',
            'active_routes' => [
                'admin.workspaces.reports.section:inventory',
                'admin.inventory.reports.*',
                'admin.inventory.intelligence.*',
                'admin.reports.inventory360',
            ],
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
            'permission' => 'reports.view|hr.kpi.view|kpi.view',
            'icon' => 'identification',
            'active_routes' => ['admin.workspaces.reports.section:hr', 'admin.reports.hr', 'admin.hr.kpi', 'admin.hr.kpi.*'],
        ],
        [
            'label' => 'Assets',
            'description' => 'Asset analytics and lifecycle intelligence.',
            'route' => 'admin.workspaces.reports.section',
            'route_params' => ['section' => 'assets'],
            'permission' => 'assets.analytics.view|reports.view',
            'icon' => 'chip',
            'active_routes' => [
                'admin.workspaces.reports.section:assets',
                'admin.assets.intelligence.*',
                'admin.assets.360.show',
            ],
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
            'presentation' => 'hub',
            'hub_route' => 'admin.reports.commercial',
            'groups' => [[
                'label' => 'Commercial',
                'items' => [
                    ['key' => 'commercial-reports', 'label' => 'Commercial Reports', 'description' => 'Sales, pipeline, customer, artwork, and exports.', 'route' => 'admin.reports.commercial', 'permission' => 'reports.view|commercial.reports.sales.view', 'icon' => 'document-text', 'active_routes' => ['admin.reports.commercial', 'admin.commercial.reports.*']],
                    ['key' => 'commercial-360', 'label' => 'Commercial 360', 'description' => 'Sales and customer management intelligence.', 'route' => 'admin.reports.commercial360', 'permission' => 'intelligence.commercial.view|reports.view', 'icon' => 'document-text', 'active_routes' => ['admin.reports.commercial360']],
                    ['key' => 'commercial-intelligence', 'label' => 'Commercial Intelligence', 'description' => 'Job, customer, product profitability, waste, and outsource analysis.', 'route' => 'admin.reports.commercial-intelligence', 'permission' => 'intelligence.commercial.view|reports.view', 'icon' => 'chart-bar', 'active_routes' => ['admin.reports.commercial-intelligence']],
                    ['key' => 'pos-intelligence', 'label' => 'POS Intelligence', 'description' => 'POS sales, sessions, payments, and returns analytics.', 'route' => 'admin.commercial.pos.reports.index', 'permission' => 'commercial.pos.reports.view', 'icon' => 'cash', 'active_routes' => ['admin.commercial.pos.reports.*']],
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
                    ['key' => 'operations-intelligence', 'label' => 'Operations Intelligence', 'description' => 'Capacity, pipeline, and maintenance intelligence.', 'route' => 'admin.production.dashboard', 'permission' => 'production.view', 'icon' => 'chart-pie', 'active_routes' => ['admin.production.dashboard']],
                    ['key' => 'job-costing', 'label' => 'Job Costing & Profitability', 'description' => 'Job-level profitability and cost analysis.', 'route' => 'admin.production.costing.dashboard', 'permission' => 'production.costing.view', 'icon' => 'currency-dollar', 'active_routes' => ['admin.production.costing.*', 'admin.production.job-cards.costing']],
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
                    ['key' => 'inventory-intelligence', 'label' => 'Inventory Intelligence', 'description' => 'Velocity, stockout risk, dead stock, and warehouse consumption.', 'route' => 'admin.inventory.intelligence.overview', 'permission' => 'inventory.intelligence.view', 'icon' => 'chart-bar', 'active_routes' => ['admin.inventory.intelligence.*']],
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
                    ['key' => 'hr-kpi', 'label' => 'HR KPI', 'description' => 'Workforce KPIs by department, branch, and role.', 'route' => 'admin.hr.kpi', 'permission' => 'hr.kpi.view|kpi.view', 'icon' => 'chart-pie', 'active_routes' => ['admin.hr.kpi', 'admin.hr.kpi.*']],
                ],
            ]],
        ],
        'assets' => [
            'title' => 'Assets',
            'description' => 'Asset analytics and lifecycle intelligence.',
            'icon' => 'chip',
            'groups' => [[
                'label' => 'Assets',
                'items' => [
                    ['key' => 'asset-intelligence', 'label' => 'Asset Intelligence', 'description' => 'Executive, branch, and lifecycle analytics.', 'route' => 'admin.assets.intelligence.dashboard', 'permission' => 'assets.analytics.view', 'icon' => 'chart-pie', 'active_routes' => ['admin.assets.intelligence.*', 'admin.assets.360.show']],
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
